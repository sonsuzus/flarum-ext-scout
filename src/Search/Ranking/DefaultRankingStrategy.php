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

class DefaultRankingStrategy implements RankingStrategyInterface
{
    /**
     * @param DatabaseSearchState $search
     */
    public function apply(SearchState $search, string $bit): void
    {
        $discussionBuilder = ScoutStatic::makeBuilder(Discussion::class, $bit);
        $discussionIds = $discussionBuilder->keys()->all();

        $postBuilder = ScoutStatic::makeBuilder(Post::class, $bit);
        $postIds = $postBuilder->keys()->all();
        $postIdsCount = count($postIds);

        // We could replace the "where field" with "where false" everywhere when there are no IDs, but it's easier to
        // keep a FIELD() statement and just hard-code some values to prevent SQL errors
        // we know nothing will be returned anyway, so it doesn't really matter what impact it has on the query
        $postIdsSql = $postIdsCount > 0 ? str_repeat(', ?', $postIdsCount) : ', 0';

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
            ->selectRaw("FIELD({$prefix}posts.id" . $postIdsSql . ") as priority", $postIds)
            ->where('posts.type', 'comment')
            ->whereIn('posts.id', $postIds);

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
            ->whereIn('posts.id', $postIds)
            ->whereRaw("FIELD({$prefix}posts.id" . $postIdsSql . ") = best_matching_posts.min_priority", $postIds)
            ->addBinding($bestMatchingPostQuery->getBindings(), 'join');

        $query
            ->where(function (EloquentBuilder|QueryBuilder $query) use ($discussionIds) {
                $query
                    ->whereNotNull('posts_ft.most_relevant_post_id')
                    ->orWhereIn('discussions.id', $discussionIds);
            })
            ->selectRaw('COALESCE(posts_ft.most_relevant_post_id, ' . $grammar->wrapTable('discussions') . '.first_post_id) as most_relevant_post_id')
            ->leftJoin(
                new Expression('(' . $subquery->toSql() . ') ' . $grammar->wrap('posts_ft')),
                new Expression('posts_ft.discussion_id'),
                '=',
                'discussions.id'
            )
            ->groupBy('discussions.id')
            ->addBinding($subquery->getBindings(), 'join');

        $search->setDefaultSort(function ($query) use ($postIdsSql, $postIds) {
            $prefix = $query->getConnection()->getTablePrefix();
            $query->orderByRaw("FIELD({$prefix}discussions.id" . $postIdsSql . ")", $postIds);
        });
    }
}
