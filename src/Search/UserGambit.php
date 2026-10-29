<?php

namespace ClarkWinkelmann\Scout\Search;

use ClarkWinkelmann\Scout\ScoutStatic;
use Flarum\Search\GambitInterface;
use Flarum\Search\SearchState;
use Flarum\User\User;

class UserGambit implements GambitInterface
{
    public function apply(SearchState $search, $bit): bool
    {
        $builder = ScoutStatic::makeBuilder(User::class, (string)$bit);

        $ids = $builder->keys();

        $query = $search->getQuery();
        $prefix = $query->getConnection()->getTablePrefix();

        $query->whereIn('users.id', $ids);

        if ($ids->isNotEmpty()) {
            $placeholders = implode(',', array_fill(0, $ids->count(), '?'));
            $query->getQuery()->orders = null;
            $query->orderByRaw("FIELD({$prefix}users.id, {$placeholders})", $ids->all());
        }

        return true;
    }
}