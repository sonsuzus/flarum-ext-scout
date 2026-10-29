<?php

namespace ClarkWinkelmann\Scout\Search\Ranking;

use ClarkWinkelmann\Scout\ScoutStatic;
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Search\SearchState;

class TitleFirstRankingStrategy implements RankingStrategyInterface
{
    public function rank(SearchState $search, string $bit): void
    {
        $discussionIds = ScoutStatic::makeBuilder(Discussion::class, $bit)->keys()->all();
        $postIds = ScoutStatic::makeBuilder(Post::class, $bit)->keys()->all();

        $postDiscussionIds = [];
        if (count($postIds) > 0) {
            $prefix = $search->getQuery()->getConnection()->getTablePrefix();
            $placeholders = implode(',', array_fill(0, count($postIds), '?'));

            $postDiscussionIds = Post::query()
                ->whereIn('id', $postIds)
                ->orderByRaw("FIELD({$prefix}posts.id, {$placeholders})", $postIds)
                ->pluck('discussion_id')
                ->all();
        }

        $mergedIds = array_values(array_unique(array_merge($discussionIds, $postDiscussionIds)));

        $search->getQuery()->whereIn('discussions.id', $mergedIds);

        $search->setDefaultSort(function ($query) use ($mergedIds) {
            if (count($mergedIds) === 0) {
                return;
            }

            $prefix = $query->getConnection()->getTablePrefix();
            $placeholders = implode(',', array_fill(0, count($mergedIds), '?'));
            $query->orderByRaw("FIELD({$prefix}discussions.id, {$placeholders})", $mergedIds);
        });
    }
}