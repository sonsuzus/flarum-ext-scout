<?php

namespace ClarkWinkelmann\Scout\Search;

use ClarkWinkelmann\Scout\Search\Ranking\RankingStrategyInterface;
use Flarum\Search\GambitInterface;
use Flarum\Search\SearchState;

class DiscussionGambit implements GambitInterface
{
    public function __construct(
        protected RankingStrategyInterface $rankingStrategy
    ) {
    }

    public function apply(SearchState $search, $bit): bool
    {
        $this->rankingStrategy->rank($search, (string)$bit);

        return true;
    }
}