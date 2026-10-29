<?php

namespace ClarkWinkelmann\Scout\Search\Ranking;

use ClarkWinkelmann\Scout\ScoutStatic;
use Flarum\Post\Post;
use Flarum\Search\SearchState;

class PostsOnlyRankingStrategy implements RankingStrategyInterface
{
    public function rank(SearchState $search, string $bit): void
    {
        $postIds = ScoutStatic::makeBuilder(Post::class, $bit)->keys()->all();

        $discussionIds = [];
        if (count($postIds) > 0) {
            $prefix = $search->getQuery()->getConnection()->getTablePrefix();
            $placeholders = implode(',', array_fill(0, count($postIds), '?'));

            $discussionIds = Post::query()
                ->whereIn('id', $postIds)
                ->orderByRaw("FIELD({$prefix}posts.id, {$placeholders})", $postIds)
                ->pluck('discussion_id')
                ->unique()
                ->values()
                ->all();
        }

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