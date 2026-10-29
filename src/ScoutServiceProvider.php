<?php

namespace ClarkWinkelmann\Scout;

use ClarkWinkelmann\Scout\Search\ImprovedGambitManager;
use ClarkWinkelmann\Scout\Search\Ranking\DefaultRankingStrategy;
use ClarkWinkelmann\Scout\Search\Ranking\ExactTitlePostRankingStrategy;
use ClarkWinkelmann\Scout\Search\Ranking\PostsOnlyRankingStrategy;
use ClarkWinkelmann\Scout\Search\Ranking\RankingStrategyInterface;
use ClarkWinkelmann\Scout\Search\Ranking\TitleFirstRankingStrategy;
use ClarkWinkelmann\Scout\Search\Ranking\TitleOnlyRankingStrategy;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Foundation\AbstractServiceProvider;
use Flarum\Search\GambitManager;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Search\UserSearcher;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Scout\EngineManager;

class ScoutServiceProvider extends AbstractServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(EngineManager::class, function (Container $app) {
            return new FlarumEngineManager($app);
        });

        $this->container->singleton('scout.attributes', function (): array {
            return [];
        });

        $this->container->singleton('scout.ShouldBeSearchable', function (): array {
            return [];
        });

        $this->container->singleton('scout.listenSaved', function (): array {
            return [];
        });

        $this->container->singleton('scout.listenDeleted', function (): array {
            return [];
        });

        $this->container->singleton(MakeSearchableDisable::class);

        $this->container->bind(RankingStrategyInterface::class, function (Container $container): RankingStrategyInterface {
            /** @var SettingsRepositoryInterface $settings */
            $settings = $container->make(SettingsRepositoryInterface::class);
            $strategy = $settings->get('clarkwinkelmann-scout.rankingStrategy', 'default');

            return match ($strategy) {
                'exact_title_post' => $container->make(ExactTitlePostRankingStrategy::class),
                'posts_only' => $container->make(PostsOnlyRankingStrategy::class),
                'title_first' => $container->make(TitleFirstRankingStrategy::class),
                'title_only' => $container->make(TitleOnlyRankingStrategy::class),
                default => $container->make(DefaultRankingStrategy::class),
            };
        });

        foreach ([DiscussionSearcher::class, UserSearcher::class] as $searcherClass) {
            $this->container->when($searcherClass)
                ->needs(GambitManager::class)
                ->give(function (Container $container) use ($searcherClass) {
                    $gambitManager = new ImprovedGambitManager();

                    $fullTextGambit = match ($searcherClass) {
                        DiscussionSearcher::class => Search\DiscussionGambit::class,
                        UserSearcher::class => Search\UserGambit::class,
                    };

                    $gambitManager->setFullTextGambit($container->make($fullTextGambit));

                    foreach (resolve('flarum.simple_search.gambits')[$searcherClass] ?? [] as $gambit) {
                        $gambitManager->add($container->make($gambit));
                    }

                    return $gambitManager;
                });
        }
    }

    public function boot(Dispatcher $events): void
    {
        ScoutModelWrapper::registerSearchableMacros();

        foreach ($this->container->make('scout.listenSaved') as $eventClass => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($eventClass, function ($event) use ($listener) {
                    if ($this->container->make(MakeSearchableDisable::class)->disabled) {
                        return;
                    }

                    $models = $listener($event);

                    if (!$models) {
                        return;
                    }

                    if (!($models instanceof Collection)) {
                        $models = new Collection([$models]);
                    }

                    $models = $models->map(function ($model) {
                        return new ScoutModelWrapper($model);
                    })->filter(function (ScoutModelWrapper $model) {
                        return $model->shouldBeSearchable();
                    });

                    if ($models->isEmpty()) {
                        return;
                    }

                    $models->searchable();
                });
            }
        }

        foreach ($this->container->make('scout.listenDeleted') as $eventClass => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($eventClass, function ($event) use ($listener) {
                    if ($this->container->make(MakeSearchableDisable::class)->disabled) {
                        return;
                    }

                    $models = $listener($event);

                    if (!$models) {
                        return;
                    }

                    if (!($models instanceof Collection)) {
                        $models = new Collection([$models]);
                    }

                    $models->unsearchable();
                });
            }
        }
    }
}