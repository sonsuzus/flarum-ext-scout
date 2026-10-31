<?php

namespace ClarkWinkelmann\Scout\Search;

use ClarkWinkelmann\Scout\Search\Ranking\DefaultRankingStrategy;
use ClarkWinkelmann\Scout\Search\Ranking\ExactTitlePostRankingStrategy;
use ClarkWinkelmann\Scout\Search\Ranking\PostsOnlyRankingStrategy;
use ClarkWinkelmann\Scout\Search\Ranking\RankingStrategyInterface;
use ClarkWinkelmann\Scout\Search\Ranking\TitleFirstRankingStrategy;
use ClarkWinkelmann\Scout\Search\Ranking\TitleOnlyRankingStrategy;
use Flarum\Search\AbstractFulltextFilter;
use Flarum\Search\Database\DatabaseSearchState;
use Flarum\Search\SearchState;
use Flarum\Settings\SettingsRepositoryInterface;

/**
 * @extends AbstractFulltextFilter<DatabaseSearchState>
 */
class DiscussionGambit extends AbstractFulltextFilter
{
    protected SettingsRepositoryInterface $settings;

    /**
     * @var array<string, RankingStrategyInterface>
     */
    protected array $strategies = [];

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function search(SearchState $state, string $value): void
    {
        $strategyKey = $this->settings->get('clarkwinkelmann-scout.rankingStrategy') ?: 'default';

        $strategy = $this->resolveStrategy($strategyKey);
        $strategy->apply($state, $value);
    }

    public function apply(SearchState $search, string $bit): void
    {
        $this->search($search, $bit);
    }

    protected function resolveStrategy(string $strategyKey): RankingStrategyInterface
    {
        if (isset($this->strategies[$strategyKey])) {
            return $this->strategies[$strategyKey];
        }

        $strategy = match ($strategyKey) {
            'title_first' => new TitleFirstRankingStrategy(),
            'exact_title_post' => new ExactTitlePostRankingStrategy(),
            'title_only' => new TitleOnlyRankingStrategy(),
            'posts_only' => new PostsOnlyRankingStrategy(),
            default => new DefaultRankingStrategy(),
        };

        $this->strategies[$strategyKey] = $strategy;

        return $strategy;
    }
}
