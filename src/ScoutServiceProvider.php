<?php

namespace ClarkWinkelmann\Scout;

use ClarkWinkelmann\Scout\Job\MakeSearchable;
use ClarkWinkelmann\Scout\Job\RemoveFromSearch;
use Flarum\Foundation\AbstractServiceProvider;
use Flarum\Frontend\Assets;
use Flarum\Frontend\Compiler\Source\SourceCollector;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Collection;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Scout;
use Meilisearch\Client as MeilisearchClient;

/**
 * Similar to Laravel\Scout\ScoutServiceProvider without the config and command parts
 */
class ScoutServiceProvider extends AbstractServiceProvider
{
    public function register()
    {
        if (class_exists(MeilisearchClient::class)) {
            $this->container->singleton(MeilisearchClient::class, function () {
                /**
                 * @var SettingsRepositoryInterface $settings
                 */
                $settings = $this->container->make(SettingsRepositoryInterface::class);

                return new MeilisearchClient(
                    $settings->get('clarkwinkelmann-scout.meilisearchHost') ?: '127.0.0.1:7700',
                    $settings->get('clarkwinkelmann-scout.meilisearchKey')
                );
            });

            // Backward-compatible container alias in case legacy casing is resolved
            $this->container->alias(MeilisearchClient::class, 'MeiliSearch\Client');
        }

        $this->container->singleton(EngineManager::class, function ($app) {
            return new FlarumEngineManager($app);
        });

        Scout::makeSearchableUsing(MakeSearchableDisable::class);
        Scout::removeFromSearchUsing(MakeSearchableDisable::class);

        $this->container->singleton('scout.searchable', function () {
            return [];
        });

        $this->container->singleton('scout.attributes', function () {
            return [];
        });

        $this->container->resolving('flarum.assets.forum', function (Assets $assets) {
            /**
             * @var SettingsRepositoryInterface $settings
             */
            $settings = $this->container->make(SettingsRepositoryInterface::class);

            $length = (int)$settings->get('clarkwinkelmann-scout.queryMinLength');

            if ($length > 0) {
                $assets->js(function (SourceCollector $sources) use ($length) {
                    $sources->addString(function () use ($length) {
                        return "app.initializers.add('scout-min-length',function(){if(window.flarum&&flarum.reg){var apply=function(m){if(m)m.MIN_SEARCH_LEN=$length;};if(flarum.reg.checkModule){apply(flarum.reg.checkModule('core','forum/components/Search'));apply(flarum.reg.checkModule('core','common/components/AbstractGlobalSearch'));}if(flarum.reg.onLoad){flarum.reg.onLoad('core','forum/components/Search',apply);flarum.reg.onLoad('core','forum/components/SearchModal',apply);flarum.reg.onLoad('core','common/components/SearchModal',apply);}}});";
                    });
                });
            }
        });
    }

    public function boot()
    {
        Collection::macro('searchable', function () {
            /**
             * @var Collection $this
             */
            if ($this->isEmpty()) {
                return;
            }

            $wrappedCollection = $this->map(function ($model) {
                if ($model instanceof ScoutModelWrapper) {
                    return $model;
                }

                return new ScoutModelWrapper($model);
            });

            $first = $wrappedCollection->first();

            $settings = resolve(SettingsRepositoryInterface::class);

            if (!$settings->get('clarkwinkelmann-scout.queue')) {
                return $first->searchableUsing()->update($wrappedCollection);
            }

            resolve(Dispatcher::class)->dispatch(new MakeSearchable($wrappedCollection));
        });

        Collection::macro('unsearchable', function () {
            /**
             * @var Collection $this
             */
            if ($this->isEmpty()) {
                return;
            }

            $wrappedCollection = $this->map(function ($model) {
                if ($model instanceof ScoutModelWrapper) {
                    return $model;
                }

                return new ScoutModelWrapper($model);
            });

            $first = $wrappedCollection->first();

            $settings = resolve(SettingsRepositoryInterface::class);

            if (!$settings->get('clarkwinkelmann-scout.queue')) {
                return $first->searchableUsing()->delete($wrappedCollection);
            }

            resolve(Dispatcher::class)->dispatch(new RemoveFromSearch($wrappedCollection));
        });
    }
}
