<?php

namespace ClarkWinkelmann\Scout\Search;

use ClarkWinkelmann\Scout\ScoutStatic;
use Flarum\Search\AbstractFulltextFilter;
use Flarum\Search\Database\DatabaseSearchState;
use Flarum\Search\SearchState;
use Flarum\User\User;

/**
 * @extends AbstractFulltextFilter<DatabaseSearchState>
 */
class UserGambit extends AbstractFulltextFilter
{
    public function search(SearchState $state, string $value): void
    {
        $builder = ScoutStatic::makeBuilder(User::class, $value);

        $ids = $builder->keys()->all();

        $state->getQuery()->whereIn('users.id', $ids);

        $state->setDefaultSort(function ($query) use ($ids) {
            if (!count($ids)) {
                return;
            }

            $prefix = $query->getConnection()->getTablePrefix();
            $query->orderByRaw("FIELD({$prefix}users.id" . str_repeat(', ?', count($ids)) . ')', $ids);
        });
    }

    public function apply(SearchState $search, string $bit): void
    {
        $this->search($search, $bit);
    }
}