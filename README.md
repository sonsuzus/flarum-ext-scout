# Scout Search for Flarum 2.0

[](https://opensource.org/licenses/MIT)
[](https://flarum.org)
[](https://www.php.net)

Integrates [Laravel Scout](https://laravel.com/framework/docs/scout) (`^10.0|^11.0`) with [Flarum 2.0](https://docs.flarum.org/extend/search/) discussion and user search.

Just like in Laravel, model changes are automatically synced with the configured search index whenever discussions, posts, or users are created, updated, or deleted in Flarum. You only need to manually import existing records when first enabling the extension or switching drivers.

The external search engine is queried server-side inside Flarum's `DatabaseSearchDriver` full-text filter to constrain and rank the SQL query results, preserving full compatibility with Flarum 2.0's `SearchModal`, native filters (`author:`, `tag:`, `is:unread`, etc.), and visibility scopes.

## Key Features & Flarum 2.0 Enhancements

* **Flarum 2.0 & Laravel 13 Ready:** Built for PHP `^8.3`, `flarum/core: ^2.0`, and `laravel/scout: ^10.0|^11.0` using the new `Extend\SearchDriver` (`AbstractFulltextFilter`) and `Extend.Admin` architectures.
* **Multiple Search Engines:** Built-in support for **Meilisearch** (`meilisearch/meilisearch-php: ^1.0`), **Algolia** (`algolia/algoliasearch-client-php: ^3.2|^4.0`), and optional support for **TNTSearch** (`teamtnt/laravel-scout-tntsearch-driver: ^14.0|^15.0|^16.0`).
* **Configurable Discussion Ranking Strategies:** Choose how discussion title matches and post content matches are prioritized and merged:
  * **Original Scout merge (`default`):** Prioritizes discussions with the highest-ranked matching comment posts.
  * **Title-first (`title_first`):** Prioritizes exact title matches $\rightarrow$ phrase title matches $\rightarrow$ title word matches $\rightarrow$ exact post matches $\rightarrow$ post word matches.
  * **Exact-first (`exact_title_post`):** Prioritizes exact/phrase title matches $\rightarrow$ exact post phrase matches $\rightarrow$ title word matches $\rightarrow$ post word matches.
  * **Title only (`title_only`):** Searches and ranks strictly by discussion titles (exact title $\rightarrow$ phrase title $\rightarrow$ title words).
  * **Posts only (`posts_only`):** Searches and ranks strictly by comment post content (exact phrase posts $\rightarrow$ post words).
* **Queue Support:** Optionally dispatch indexing and removal jobs (`MakeSearchable` / `RemoveFromSearch`) to Flarum's background queue.
* **Search Highlighting Integration:** Compatible with `clarkwinkelmann/flarum-ext-advanced-search-highlight` when using Meilisearch.

## Requirements

* **PHP:** `^8.3`
* **Flarum Core:** `^2.0`
* **Search Engine:** Algolia account, Meilisearch server (`v1.0+`), or SQLite PDO extension (for TNTSearch).

## Installation

Install the extension via Composer:

```bash
composer require dshovchko/flarum-ext-scout -W
```

If you are installing from a custom VCS fork for Flarum 2.0:

```bash
composer config repositories.scout vcs https://github.com/sonsuzus/flarum-ext-scout
composer require dshovchko/flarum-ext-scout:dev-main -W
```

After installation, clear the cache and publish assets if needed:

```bash
php flarum cache:clear
```

## Driver Configuration

Enable **Scout Search** in the Flarum Admin Dashboard and select your preferred driver:

### 1. Meilisearch

1. Install and run a [Meilisearch](https://www.meilisearch.com) instance (v1.0 or newer).
2. In the extension settings, set **Driver** to **Meilisearch**.
3. Configure **Meilisearch Host** (default: `127.0.0.1:7700`) and **Meilisearch Key** (your master or admin API key).

### 2. Algolia

1. Create an [Algolia](https://www.algolia.com) account and application.
2. In the extension settings, set **Driver** to **Algolia**.
3. Enter your **Algolia ID** and **Algolia Secret** (Admin API Key with read and write permissions).
4. Optionally configure connect, read, and write timeouts.

### 3. TNTSearch (Optional)

TNTSearch stores its search index in local SQLite files inside Flarum's `storage/tntsearch/` directory. Because the package is not installed by default, require it first:

```bash
composer require teamtnt/laravel-scout-tntsearch-driver:"^16.0" -W
```

Then select **TNTSearch** as the driver in the Flarum Admin settings. You can optionally tune max documents, fuzziness (typo tolerance), Levenshtein distance, prefix length, and max expansions.

## CLI Commands (Indexing & Flushing)

When you first configure Scout—or whenever you want to rebuild your search indices—run the import command from your Flarum root directory:

* **Import all searchable Flarum models (`Discussion`, `Post`, `User`):**
  ```bash
  php flarum scout:import-all
  ```
* **Import all models in smaller batches (custom chunk size):**
  ```bash
  php flarum scout:import-all --chunk=250
  ```
* **Import a single model class:**
  ```bash
  php flarum scout:import "Flarum\Discussion\Discussion"
  php flarum scout:import "Flarum\Post\Post"
  php flarum scout:import "Flarum\User\User"
  ```
* **Flush (clear) a model's index:**
  ```bash
  php flarum scout:flush "Flarum\Discussion\Discussion"
  php flarum scout:flush "Flarum\Post\Post"
  php flarum scout:flush "Flarum\User\User"
  ```

## Extending (For Extension Developers)

Other Flarum extensions can use the `ClarkWinkelmann\Scout\Extend\Scout` extender in their `extend.php` to customize indexed attributes, filter which records should be indexed, or trigger index updates on custom domain events:

```php
use ClarkWinkelmann\Scout\Extend\Scout;
use Flarum\Discussion\Discussion;
use Acme\Extension\Event\CustomDiscussionEvent;

return [
    (new Scout(Discussion::class))
        ->attributes(function (Discussion $discussion, array $attributes): array {
            return [
                'customField' => $discussion->custom_field,
            ];
        })
        ->searchable(function (Discussion $discussion): ?bool {
            if ($discussion->is_secret) {
                return false;
            }

            return null; // Pass to next callback or default (true)
        })
        ->listenSaved(CustomDiscussionEvent::class, function (CustomDiscussionEvent $event) {
            return $event->discussion;
        }),
];
```

## Frontend Development

To recompile the Admin frontend assets for Flarum 2.0:

```bash
cd js
npm install
npm run build
```

## Links

* [Original Repository (Clark Winkelmann)](https://github.com/clarkwinkelmann/flarum-ext-scout)
* [Maintainer Repository (Dmytro Shovchko)](https://www.google.com/search?q=https://github.com/dshovchko/flarum-ext-scout)
* [Flarum Community Discussion](https://www.google.com/search?q=https://discuss.flarum.org/d/30874)
