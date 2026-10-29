<?php

namespace ClarkWinkelmann\Scout\Search\Ranking;

use ClarkWinkelmann\Scout\ScoutStatic;
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Search\SearchState;
use Illuminate\Support\Arr;

class DefaultRankingStrategy implements RankingStrategyInterface
{
    public function rank(SearchState $search, string $bit): void
    {
        $discussionIds = ScoutStatic::makeBuilder(Discussion::class, $bit)->keys();

        $postBuilder = ScoutStatic::makeBuilder(Post::class, $bit);
        $rawPosts = $postBuilder->raw();

        $postIds = [];
        $discussionIdsFromPosts = [];

        if (is_array($rawPosts) && isset($rawPosts['hits']) && is_array($rawPosts['hits'])) {
            foreach ($rawPosts['hits'] as $hit) {
                $postId = Arr::get($hit, 'id') ?? Arr::get($hit, 'objectID');
                $discussionId = Arr::get($hit, 'discussion_id');

                if ($postId && $discussionId) {
                    $postIds[] = $postId;
                    $discussionIdsFromPosts[$postId] = $discussionId;
                }
            }
        } else {
            $postIds = $postBuilder->keys()->all();
            if (count($postIds) > 0) {
                $discussionIdsFromPosts = Post::query()
                    ->whereIn('id', $postIds)
                    ->pluck('discussion_id', 'id')
                    ->all();
            }
        }

        $postsByDiscussion = [];
        $discussionFirstMatchRank = [];

        foreach ($postIds as $postRank => $postId) {
            $discussionId = Arr::get($discussionIdsFromPosts, $postId);

            if (!$discussionId) {
                continue;
            }

            if (!isset($postsByDiscussion[$discussionId])) {
                $postsByDiscussion[$discussionId] = [];
                $discussionFirstMatchRank[$discussionId] = $postRank;
            }

            $postsByDiscussion[$discussionId][] = $postId;
        }

        foreach ($discussionIds as $discussionRank => $discussionId) {
            if (isset($discussionFirstMatchRank[$discussionId])) {
                $discussionFirstMatchRank[$discussionId] = min($discussionFirstMatchRank[$discussionId], $discussionRank);
            } else {
                $discussionFirstMatchRank[$discussionId] = $discussionRank;
            }
        }

        asort($discussionFirstMatchRank);
        $sortedDiscussionIds = array_keys($discussionFirstMatchRank);

        $search->setDefaultSort(function ($query) use ($sortedDiscussionIds) {
            if (count($sortedDiscussionIds) === 0) {
                return;
            }

            $prefix = $query->getConnection()->getTablePrefix();
            $placeholders = implode(',', array_fill(0, count($sortedDiscussionIds), '?'));
            $query->orderByRaw("FIELD({$prefix}discussions.id, {$placeholders})", $sortedDiscussionIds);
        });

        $search->getQuery()->whereIn('discussions.id', $sortedDiscussionIds);

        Discussion::register relevantPostIds:
        foreach ($postsByDiscussion as $discussionId => $matchingPostIds) {
            // Flarum relevant posts mapping if needed by extensions
        }
    }
}