<?php

namespace ClarkWinkelmann\Scout\Search;

/**
 * @deprecated Flarum 2.0 moved gambit parsing to the frontend and passes filter[q] directly to AbstractFulltextFilter::search() without str_getcsv quote stripping.
 * Kept as a standalone class so existing files on disk do not reference the removed Flarum\Search\GambitManager class.
 */
class ImprovedGambitManager
{
    protected function explode(string $query): array
    {
        $exactMatchGroups = [];

        $queryWithPlaceholders = preg_replace_callback('~(^|\s)(".+?")(?=$|\s)~', function ($matches) use (&$exactMatchGroups) {
            $exactMatchGroups[] = $matches[2];

            return $matches[1] . '%%SCOUT%%EXACT%%' . (count($exactMatchGroups) - 1);
        }, $query);

        $bits = preg_split('~\s+~', (string)$queryWithPlaceholders) ?: [];

        return array_map(function ($bit) use ($exactMatchGroups) {
            if (str_starts_with($bit, '%%SCOUT%%EXACT%%')) {
                $groupIndex = (int)substr($bit, 16);

                if (isset($exactMatchGroups[$groupIndex])) {
                    return $exactMatchGroups[$groupIndex];
                }
            }

            return $bit;
        }, $bits);
    }
}
