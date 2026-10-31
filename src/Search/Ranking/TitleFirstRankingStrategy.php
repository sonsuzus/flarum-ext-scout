<?php

namespace ClarkWinkelmann\Scout\Search\Ranking;

use ClarkWinkelmann\Scout\ScoutStatic;
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Search\Database\DatabaseSearchState;
use Flarum\Search\SearchState;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Expression;

class TitleFirstRankingStrategy implements RankingStrategyInterface
{
    /**
     * @param DatabaseSearchState $search
     */
    public function apply(SearchState $search, string $bit): void
    {
        $exactBit = '"' . str_replace('"', '\\"', $bit) . '"';

        $discussionExactBuilder = ScoutStatic::makeBuilder(Discussion::class, $exactBit);
        $discussionExactIds = $discussionExactBuilder->keys()->all();

        $discussionExactTitleIds = Discussion::whereVisibleTo($search->getActor())
            ->where('title', $bit)
            ->pluck('id')
            ->all();

        $discussionBuilder = ScoutStatic::makeBuilder(Discussion::class, $bit);
        $discussionIds = $discussionBuilder->keys()->all();

        $discussionAllIds = array_values(array_unique(array_merge($discussionExactTitleIds, $discussionExactIds, $discussionIds)));

        $postExactBuilder = ScoutStatic::makeBuilder(Post::class, $exactBit);
        $postExactIds = $postExactBuilder->keys()->all();

        $postBuilder = ScoutStatic::makeBuilder(Post::class, $bit);
        $postIds = $postBuilder->keys()->all();

        $postIdsMerged = array_values(array_unique(array_merge($postExactIds, $postIds)));

        $postIdsCount = count($postIdsMerged);

        // We could replace the "where field" with "where false" everywhere when there are no IDs, but it's easier to
        // keep a FIELD() statement and just hard-code some values to prevent SQL errors
        // we know nothing will be returned anyway, so it doesn't really matter what impact it has on the query
        $postIdsSql = $postIdsCount > 0 ? str_repeat(', ?', $postIdsCount) : ', 0';

        $discussionExactTitleIdsSql = count($discussionExactTitleIds) > 0 ? implode(', ', array_fill(0, count($discussionExactTitleIds), '?')) : '0';
        $discussionExactIdsSql = count($discussionExactIds) > 0 ? implode(', ', array_fill(0, count($discussionExactIds), '?')) : '0';
        $discussionIdsSql = count($discussionIds) > 0 ? implode(', ', array_fill(0, count($discussionIds), '?')) : '0';

        $postExactIdsSql = count($postExactIds) > 0 ? str_repeat(', ?', count($postExactIds)) : ', 0';

        // In Flarum 2.0, DatabaseSearchState::getQuery() returns an Eloquent\Builder instance;
        // unwrap to the underlying Query\Builder so all raw joins, bindings, and closures work identically.
        $query = $search->getQuery();
        if ($query instanceof EloquentBuilder) {
            $query = $query->getQuery();
        }
        $grammar = $query->getGrammar();
        $prefix = $query->getConnection()->getTablePrefix();

        $allMatchingPostsQuery = Post::whereVisibleTo($search->getActor())
            ->select('posts.discussion_id')
            ->selectRaw("FIELD({$prefix}posts.id" . $postIdsSql . ") as priority", $postIdsMerged)
            ->where('posts.type', 'comment')
            ->whereIn('posts.id', $postIdsMerged);

        // Using wrap() instead of wrapTable() in join subquery to skip table prefixes
        // Using Expression in the join column to use the same prefixless alias
        $bestMatchingPostQuery = Post::query()
            ->select('posts.discussion_id')
            ->selectRaw('min(matching_posts.priority) as min_priority')
            ->join(
                new Expression('(' . $allMatchingPostsQuery->toSql() . ') ' . $grammar->wrap('matching_posts')),
                new Expression('matching_posts.discussion_id'),
                '=',
                'posts.discussion_id'
            )
            ->groupBy('posts.discussion_id')
            ->addBinding($allMatchingPostsQuery->getBindings(), 'join');

        $subquery = Post::whereVisibleTo($search->getActor())
            ->select('posts.discussion_id')
            ->selectRaw('id as most_relevant_post_id')
            ->join(
                new Expression('(' . $bestMatchingPostQuery->toSql() . ') ' . $grammar->wrap('best_matching_posts')),
                new Expression('best_matching_posts.discussion_id'),
                '=',
                'posts.discussion_id'
            )
            ->whereIn('posts.id', $postIdsMerged)
            ->whereRaw("FIELD({$prefix}posts.id" . $postIdsSql . ") = best_matching_posts.min_priority", $postIdsMerged)
            ->addBinding($bestMatchingPostQuery->getBindings(), 'join');

        $exactPostsDiscussionsQuery = Post::whereVisibleTo($search->getActor())
            ->select('posts.discussion_id')
            ->where('posts.type', 'comment')
            ->whereIn('posts.id', $postExactIds)
            ->groupBy('posts.discussion_id');

        $query
            ->where(function (EloquentBuilder|QueryBuilder $query) use ($discussionAllIds) {
                $query
                    ->whereNotNull('most_relevant_post_id')
                    ->orWhereIn('discussions.id', $discussionAllIds);
            })
            ->selectRaw('COALESCE(posts_ft.most_relevant_post_id, ' . $grammar->wrapTable('discussions') . '.first_post_id) as most_relevant_post_id')
            ->leftJoin(
                new Expression('(' . $subquery->toSql() . ') ' . $grammar->wrap('posts_ft')),
                new Expression('posts_ft.discussion_id'),
                '=',
                'discussions.id'
            )
            ->leftJoin(
                new Expression('(' . $exactPostsDiscussionsQuery->toSql() . ') ' . $grammar->wrap('exact_posts')),
                new Expression('exact_posts.discussion_id'),
                '=',
                'discussions.id'
            )
            ->groupBy('discussions.id')
            ->addBinding($subquery->getBindings(), 'join')
            ->addBinding($exactPostsDiscussionsQuery->getBindings(), 'join');

        $search->setDefaultSort(function ($query) use (
            $discussionExactTitleIds,
            $discussionExactIds,
            $discussionExactIdsSql,
            $discussionIds,
            $discussionIdsSql,
            $discussionExactTitleIdsSql,
            $postExactIds,
            $postExactIdsSql,
            $postIdsSql,
            $postIdsMerged
        ) {
            $prefix = $query->getConnection()->getTablePrefix();
            $query
                ->orderByRaw(
                    "CASE " .
                    "WHEN {$prefix}discussions.id IN (" . $discussionExactTitleIdsSql . ") THEN 0 " .
                    "WHEN {$prefix}discussions.id IN (" . $discussionExactIdsSql . ") THEN 1 " .
                    "WHEN {$prefix}discussions.id IN (" . $discussionIdsSql . ") THEN 2 " .
                    "WHEN exact_posts.discussion_id IS NOT NULL THEN 3 " .
                    "WHEN posts_ft.most_relevant_post_id IS NOT NULL THEN 4 " .
                    "ELSE 5 END",
                    array_merge($discussionExactTitleIds, $discussionExactIds, $discussionIds)
                )
                ->orderByRaw(
                    "CASE " .
                    "WHEN {$prefix}discussions.id IN (" . $discussionExactTitleIdsSql . ") THEN FIELD({$prefix}discussions.id, " . $discussionExactTitleIdsSql . ") " .
                    "WHEN {$prefix}discussions.id IN (" . $discussionExactIdsSql . ") THEN FIELD({$prefix}discussions.id, " . $discussionExactIdsSql . ") " .
                    "WHEN {$prefix}discussions.id IN (" . $discussionIdsSql . ") THEN FIELD({$prefix}discussions.id, " . $discussionIdsSql . ") " .
                    "WHEN exact_posts.discussion_id IS NOT NULL THEN FIELD(posts_ft.most_relevant_post_id" . $postExactIdsSql . ") " .
                    "WHEN posts_ft.most_relevant_post_id IS NOT NULL THEN FIELD(posts_ft.most_relevant_post_id" . $postIdsSql . ") " .
                    "ELSE 0 END",
                    array_merge(
                        $discussionExactTitleIds,
                        $discussionExactTitleIds,
                        $discussionExactIds,
                        $discussionExactIds,
                        $discussionIds,
                        $discussionIds,
                        $postExactIds,
                        $postIdsMerged
                    )
                );
        });
    }
}
