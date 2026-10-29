<?php

namespace ClarkWinkelmann\Scout\Search\Ranking;

use ClarkWinkelmann\Scout\ScoutStatic;
use Flarum\Discussion\Discussion;
use Flarum\Search\SearchState;

class TitleOnlyRankingStrategy implements RankingStrategyInterface
{
    public function rank(SearchState $search, string $bit): void
    {
        $discussionIds = ScoutStatic::makeBuilder(Discussion::class, $bit)->keys()->all();

        $search->getQuery()->whereIn('discussions.id', $discussionIds);

        $search->setDefaultSort(function ($query) use ($discussionIds) {
            if (count($discussionIds) === 0) {
                return;
            }

            $prefix = $query->getConnection()->getTablePrefix();
            $placeholders = implode(',', array_fill(0, count($discussionIds), '?'));
            $query->orderByRaw("FIELD({$prefix}discussions.id, {$placeholders})", $discussionIds);
        });
    }
}