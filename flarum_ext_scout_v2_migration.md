# `dshovchko/flarum-ext-scout` — Flarum 2.0 Tam Geçiş Kodları

Toplam **33 dosyadan 20 tanesinde** Flarum 2.0, PHP 8.3+ ve Laravel 12/13 (Laravel Scout 10/11) uyumluluğu için değişiklik gerekiyor. Geri kalan 13 dosya (`js/admin.ts`, `js/tsconfig.json`, `js/webpack.config.js`, `resources/locale/en.yml`, `src/Console/*`, `src/Job/MakeSearchable.php`, `src/Listener/DeletingDiscussion.php`, `src/FlarumSearchableScope.php`, `src/helpers.php`, `src/MakeSearchableDisable.php`) olduğu gibi korunabilir.

## Değişen Dosyaların Özet Tablosu

| Dosya Yolu | Flarum 2.0 / PHP 8.3 Değişiklik Nedeni |
| :--- | :--- |
| `composer.json` | `php: ^8.3`, `flarum/core: ^2.0`, `laravel/scout: ^10.0\|^11.0`, `meilisearch/meilisearch-php: ^1.0` ve `teamtnt/laravel-scout-tntsearch-driver: ^14.0\|^15.0\|^16.0` sürümlerine yükseltildi. |
| `js/package.json` | `flarum-tsconfig: ^2.0.0` ve `flarum-webpack-config: ^3.0.0` sürümlerine güncellendi. |
| `js/src/admin/index.ts` | Kaldırılan `app.extensionData` yerine Flarum 2.0 `Extend.Admin` deklaratif yapısı (`export const extend`) kullanıldı. |
| `extend.php` | Kaldırılan `Extend\SimpleFlarumSearch` yerine `Extend\SearchDriver(DatabaseSearchDriver::class)->setFulltext(...)` ve `Extend\Settings` varsayılanları eklendi. |
| `src/Extend/Scout.php` | PHP 8.3/8.4 uyumlu `?Extension $extension = null` ve `: void` dönüş tipi eklendi; callback sarma döngüsü düzeltildi. |
| `src/Job/RemoveFromSearch.php` | Scout 10/11'de kaldırılan `getUnqualifiedScoutKeyName()` çağrısı `$model->getScoutKeyName()` olarak güncellendi. |
| `src/Job/SerializesAndRestoresWrappedModelIdentifiers.php` | Laravel 11-13 `getSerializedPropertyValue($value, $withRelations = true)` imzası ve `parent::getSerializedPropertyValue` çağrısı güncellendi. |
| `src/Search/Ranking/RankingStrategyInterface.php` | Flarum 2.0 `SearchState` tip bildirimleri korundu. |
| `src/Search/Ranking/DefaultRankingStrategy.php` | Flarum 2.0'da `DatabaseSearchState::getQuery()` artık `Eloquent\Builder` döndürdüğü için `where` closure tip hatası (`EloquentBuilder\|QueryBuilder`), `getGrammar()` ve `Expression` join ifadeleri güncellendi. |
| `src/Search/Ranking/ExactTitlePostRankingStrategy.php` | `Eloquent\Builder` uyumluluğu, `Expression` join yapıları ve tablo sütun nitelemeleri (`discussions.id`, `posts.id`) güncellendi. |
| `src/Search/Ranking/PostsOnlyRankingStrategy.php` | `Eloquent\Builder` uyumluluğu ve `Expression` join yapıları güncellendi. |
| `src/Search/Ranking/TitleFirstRankingStrategy.php` | `Eloquent\Builder` uyumluluğu, `Expression` join yapıları ve tablo sütun nitelemeleri güncellendi. |
| `src/Search/Ranking/TitleOnlyRankingStrategy.php` | `Eloquent\Builder` uyumluluğu ve `discussions.id` sütun nitelemesi güncellendi. |
| `src/Search/DiscussionGambit.php` | Kaldırılan `GambitInterface` yerine `AbstractFulltextFilter` sınıfından türetildi ve `search(SearchState $state, string $value): void` metodu eklendi. |
| `src/Search/UserGambit.php` | Kaldırılan `GambitInterface` yerine `AbstractFulltextFilter` sınıfından türetildi; `orderByRaw` için `$builder->keys()->all()` dizi dönüşümü yapıldı. |
| `src/Search/ImprovedGambitManager.php` | Kaldırılan `Flarum\Search\GambitManager` bağımlılığı kaldırıldı (Flarum 2.0 backend'de `str_getcsv` kullanmadığı için tırnaklar doğal olarak korunur). |
| `src/FlarumEngineManager.php` | Algolia ve TNTSearch ayarlarında tip güvenliği (`is_numeric` ve `(int)` dönüşümleri) sağlandı. |
| `src/ScoutModelWrapper.php` | Scout 10/11 kuralına göre `getScoutKeyName()` tablo öneksiz anahtar döndürecek şekilde (`getKeyName()`) ve `queryScoutModelsByIds` ise `qualifyColumn()` kullanacak şekilde güncellendi. |
| `src/ScoutServiceProvider.php` | `Meilisearch\Client` isim alanı güncellendi, kaldırılan `flarum.core.compat` yerine `flarum.reg` kullanıldı ve kaldırılan `flarum.simple_search.fulltext_gambits` döngüsü temizlendi. |
| `src/ScoutStatic.php` | `MeilisearchEngine` isim alanı, PHP 8.3 `?callable $callback = null` imzası ve `makeAllSearchable` sıralama anahtarı güncellendi. |

## Değiştirilmesi Gereken Tüm Dosyalar (Tam Sayfa Kodlar)

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\composer.json`

```json
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\composer.json ===
{
    "name": "dshovchko/flarum-ext-scout",
    "description": "Algolia, Meilisearch and TNTSearch search for Flarum",
    "keywords": [
        "flarum"
    ],
    "type": "flarum-extension",
    "license": "MIT",
    "require": {
        "php": "^8.3",
        "flarum/core": "^2.0",
        "laravel/scout": "^10.0|^11.0",
        "algolia/algoliasearch-client-php": "^3.2|^4.0",
        "http-interop/http-factory-guzzle": "^1.0",
        "meilisearch/meilisearch-php": "^1.0"
    },
    "require-dev": {
        "teamtnt/laravel-scout-tntsearch-driver": "^14.0|^15.0|^16.0"
    },
    "suggest": {
        "clarkwinkelmann/flarum-ext-advanced-search-highlight": "*"
    },
    "authors": [
        {
            "name": "Clark Winkelmann",
            "email": "clark.winkelmann@gmail.com",
            "role": "Developer"
        },
        {
            "name": "Dmytro Shovchko",
            "email": "d.shovchko@gmail.com",
            "role": "Maintainer"
        }
    ],
    "support": {
        "source": "https://github.com/dshovchko/flarum-ext-scout",
        "forum": "https://discuss.flarum.org/d/30874"
    },
    "autoload": {
        "psr-4": {
            "ClarkWinkelmann\\Scout\\": "src/"
        },
        "files": [
            "src/helpers.php"
        ]
    },
    "extra": {
        "flarum-extension": {
            "title": "Scout Search",
            "category": "feature",
            "icon": {
                "name": "fas fa-search",
                "backgroundColor": "#f47f68",
                "color": "#fff"
            },
            "optional-dependencies": [
                "clarkwinkelmann/flarum-ext-advanced-search-highlight"
            ]
        }
    }
}
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\js\package.json`

```json
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\js\package.json ===
{
    "name": "@clarkwinkelmann/scout",
    "private": true,
    "dependencies": {
        "flarum-tsconfig": "^2.0.0",
        "flarum-webpack-config": "^3.0.0",
        "webpack": "^5.65",
        "webpack-cli": "^5.0"
    },
    "scripts": {
        "dev": "webpack --mode development --watch",
        "build": "webpack --mode production"
    }
}
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\js\src\admin\index.ts`

```typescript
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\js\src\admin\index.ts ===
import app from 'flarum/admin/app';
import Extend from 'flarum/common/extenders';

export const extend = [
    new Extend.Admin()
        .setting(() => ({
            type: 'select',
            setting: 'clarkwinkelmann-scout.driver',
            options: {
                null: app.translator.trans('clarkwinkelmann-scout.admin.setting.driverOption.null', {}, true),
                algolia: app.translator.trans('clarkwinkelmann-scout.admin.setting.driverOption.algolia', {}, true),
                meilisearch: app.translator.trans('clarkwinkelmann-scout.admin.setting.driverOption.meilisearch', {}, true),
                tntsearch: app.translator.trans('clarkwinkelmann-scout.admin.setting.driverOption.tntsearch', {}, true),
            },
            default: 'null',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.driver', {}, true),
        }))
        .setting(() => ({
            type: 'text',
            setting: 'clarkwinkelmann-scout.prefix',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.prefix', {}, true),
        }))
        .setting(() => ({
            type: 'switch',
            setting: 'clarkwinkelmann-scout.queue',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.queue', {}, true),
            help: app.translator.trans('clarkwinkelmann-scout.admin.setting.queueHelp', {}, true),
        }))
        .setting(() => ({
            type: 'number',
            setting: 'clarkwinkelmann-scout.limit',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.limit', {}, true),
            placeholder: app.translator.trans('clarkwinkelmann-scout.admin.setting.limitPlaceholder', {}, true),
            help: app.translator.trans('clarkwinkelmann-scout.admin.setting.limitHelp', {}, true),
        }))
        .setting(() => ({
            type: 'select',
            setting: 'clarkwinkelmann-scout.rankingStrategy',
            options: {
                default: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategyOption.default', {}, true),
                title_first: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategyOption.titleFirst', {}, true),
                exact_title_post: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategyOption.exactTitlePost', {}, true),
                title_only: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategyOption.titleOnly', {}, true),
                posts_only: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategyOption.postsOnly', {}, true),
            },
            default: 'default',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategy', {}, true),
            help: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategyHelp', {}, true),
        }))
        .setting(() => ({
            type: 'number',
            setting: 'clarkwinkelmann-scout.queryMinLength',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.queryMinLength', {}, true),
            placeholder: app.translator.trans('clarkwinkelmann-scout.admin.setting.queryMinLengthPlaceholder', {}, true),
            help: app.translator.trans('clarkwinkelmann-scout.admin.setting.queryMinLengthHelp', {}, true),
        }))
        .setting(() => ({
            type: 'text',
            setting: 'clarkwinkelmann-scout.algoliaId',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.algoliaId', {}, true),
        }))
        .setting(() => ({
            type: 'text',
            setting: 'clarkwinkelmann-scout.algoliaSecret',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.algoliaSecret', {}, true),
        }))
        .setting(() => ({
            type: 'text',
            setting: 'clarkwinkelmann-scout.algoliaConnectTimeout',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.algoliaConnectTimeout', {}, true),
        }))
        .setting(() => ({
            type: 'text',
            setting: 'clarkwinkelmann-scout.algoliaReadTimeout',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.algoliaReadTimeout', {}, true),
        }))
        .setting(() => ({
            type: 'text',
            setting: 'clarkwinkelmann-scout.algoliaWriteTimeout',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.algoliaWriteTimeout', {}, true),
        }))
        .setting(() => ({
            type: 'text',
            setting: 'clarkwinkelmann-scout.meilisearchHost',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.meilisearchHost', {}, true),
            placeholder: '127.0.0.1:7700',
        }))
        .setting(() => ({
            type: 'text',
            setting: 'clarkwinkelmann-scout.meilisearchKey',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.meilisearchKey', {}, true),
        }))
        .setting(() => ({
            type: 'number',
            setting: 'clarkwinkelmann-scout.tntsearchMaxDocs',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.tntsearchMaxDocs', {}, true),
            placeholder: '500',
        }))
        .setting(() => ({
            type: 'switch',
            setting: 'clarkwinkelmann-scout.tntsearchFuzziness',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.tntsearchFuzziness', {}, true),
        }))
        .setting(() => ({
            type: 'number',
            setting: 'clarkwinkelmann-scout.tntsearchFuzzyDistance',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.tntsearchFuzzyDistance', {}, true),
            placeholder: '2',
        }))
        .setting(() => ({
            type: 'number',
            setting: 'clarkwinkelmann-scout.tntsearchFuzzyPrefixLength',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.tntsearchFuzzyPrefixLength', {}, true),
            placeholder: '50',
        }))
        .setting(() => ({
            type: 'text',
            setting: 'clarkwinkelmann-scout.tntsearchFuzzyMaxExpansions',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.tntsearchFuzzyMaxExpansions', {}, true),
            placeholder: '2',
        })),
];

app.initializers.add('dshovchko-scout', () => {
    // Settings are registered declaratively via Extend.Admin above for Flarum 2.0 Admin Search indexing.
});
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\extend.php`

```php
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\extend.php ===
<?php

namespace ClarkWinkelmann\Scout;

use ClarkWinkelmann\Scout\Extend\Scout as ScoutExtend;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\Event as DiscussionEvent;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Extend;
use Flarum\Post\CommentPost;
use Flarum\Post\Event as PostEvent;
use Flarum\Post\Post;
use Flarum\Search\Database\DatabaseSearchDriver;
use Flarum\User\Event as UserEvent;
use Flarum\User\Search\UserSearcher;
use Flarum\User\User;
use FoF\UserBio\Event\BioChanged;
use Laravel\Scout\Console as ScoutConsole;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    new Extend\Locales(__DIR__ . '/resources/locale'),

    (new Extend\ServiceProvider())
        ->register(ScoutServiceProvider::class),

    (new Extend\Settings())
        ->default('clarkwinkelmann-scout.driver', 'null')
        ->default('clarkwinkelmann-scout.rankingStrategy', 'default')
        ->default('clarkwinkelmann-scout.queue', false)
        ->default('clarkwinkelmann-scout.tntsearchFuzziness', false),

    (new Extend\SearchDriver(DatabaseSearchDriver::class))
        ->setFulltext(DiscussionSearcher::class, Search\DiscussionGambit::class)
        ->setFulltext(UserSearcher::class, Search\UserGambit::class),

    (new Extend\Console())
        ->command(Console\FlushCommand::class)
        ->command(Console\ImportAllCommand::class)
        ->command(Console\ImportCommand::class)
        ->command(ScoutConsole\IndexCommand::class)
        ->command(ScoutConsole\DeleteIndexCommand::class),

    (new ScoutExtend(Discussion::class))
        ->listenSaved(DiscussionEvent\Started::class, function (DiscussionEvent\Started $event) {
            return $event->discussion;
        })
        ->listenSaved(DiscussionEvent\Renamed::class, function (DiscussionEvent\Renamed $event) {
            return $event->discussion;
        })
        // Hidden/Restored events might be needed if we save it as meta in a future version
        /*->listenSaved(DiscussionEvent\Hidden::class, function (DiscussionEvent\Hidden $event) {
            return $event->discussion;
        })
        ->listenSaved(DiscussionEvent\Restored::class, function (DiscussionEvent\Restored $event) {
            return $event->discussion;
        })*/
        ->listenDeleted(DiscussionEvent\Deleted::class, function (DiscussionEvent\Deleted $event) {
            return $event->discussion;
        })
        ->attributes(function (Discussion $discussion): array {
            return [
                'id' => $discussion->id, // TNTSearch requires the ID to be part of the searchable data
                'title' => $discussion->title,
            ];
        }),

    (new ScoutExtend(Post::class))
        ->listenSaved(PostEvent\Posted::class, function (PostEvent\Posted $event) {
            return $event->post;
        })
        ->listenSaved(PostEvent\Revised::class, function (PostEvent\Revised $event) {
            return $event->post;
        })
        // Hidden/Restored events might be needed if we save it as meta in a future version
        /*->listenSaved(PostEvent\Hidden::class, function (PostEvent\Hidden $event) {
            return $event->post;
        })
        ->listenSaved(PostEvent\Restored::class, function (PostEvent\Restored $event) {
            return $event->post;
        })*/
        ->listenDeleted(PostEvent\Deleted::class, function (PostEvent\Deleted $event) {
            return $event->post;
        })
        ->searchable(function (Post $post) {
            if ($post->type !== 'comment') {
                return false;
            }

            return null;
        })
        ->attributes(function (Post $post): array {
            return [
                'id' => $post->id,
            ];
        }),

    // We use a separate extender call specifically for CommentPost
    // This is both a good way to organise the code and removes the need to check for instanceof before rendering the content
    // Natively we only index comments, but an extension could make more posts searchable so this code is nicely isolated in anticipation for that
    (new ScoutExtend(CommentPost::class))
        ->attributes(function (CommentPost $post): array {
            return [
                // We use the rendered version and not unparsed version as the unparsed version might expose original text that's hidden by extensions in the output
                // strip_tags is used to strip HTML tags and their properties from the index but not provide any additional security
                'content' => strip_tags($post->formatContent()),
            ];
        }),

    (new ScoutExtend(User::class))
        ->listenSaved(UserEvent\Registered::class, function (UserEvent\Registered $event) {
            return $event->user;
        })
        ->listenDeleted(UserEvent\Deleted::class, function (UserEvent\Deleted $event) {
            return $event->user;
        })
        ->listenSaved(BioChanged::class, function (BioChanged $event) {
            return $event->user;
        })
        ->attributes(function (User $user): array {
            return [
                'id' => $user->id,
                'displayName' => $user->display_name,
                'username' => $user->username,
                // It doesn't matter if fof/user-bio is installed or not, it'll just be null if not
                'bio' => $user->bio,
            ];
        }),

    (new Extend\Event())
        ->listen(DiscussionEvent\Deleting::class, Listener\DeletingDiscussion::class),
];
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Extend\Scout.php`

```php
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Extend\Scout.php ===
<?php

namespace ClarkWinkelmann\Scout\Extend;

use ClarkWinkelmann\Scout\FlarumSearchableScope;
use ClarkWinkelmann\Scout\ScoutModelWrapper;
use Flarum\Extend\ExtenderInterface;
use Flarum\Extension\Extension;
use Flarum\Extension\ExtensionManager;
use Flarum\Foundation\ContainerUtil;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;

class Scout implements ExtenderInterface
{
    protected string $modelClass;
    protected array $attributes = [];
    protected array $searchable = [];
    protected array $listenSaved = [];
    protected array $listenDeleted = [];

    /**
     * @param string $modelClass The ::class attribute of the Eloquent model you are modifying.
     *                           This model should extend from \Flarum\Database\AbstractModel.
     */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    /**
     * Add to or modify the searchable attributes array of this model.
     *
     * @param callable|string $callback
     * @return self
     */
    public function attributes($callback): self
    {
        $this->attributes[] = $callback;

        return $this;
    }

    /**
     * Modify whether a given model should be searchable.
     *
     * @param callable|string $callback
     * @return self
     */
    public function searchable($callback): self
    {
        $this->searchable[] = $callback;

        return $this;
    }

    /**
     * Register an event listener that should cause Scout to update the indexed data.
     *
     * @param string $eventClass The class to listen for on the event dispatcher
     * @param callable|string $callback
     * @return self
     */
    public function listenSaved(string $eventClass, $callback): self
    {
        $this->listenSaved[] = [
            $eventClass,
            $callback,
        ];

        return $this;
    }

    /**
     * Register an event listener that should cause Scout to remove the indexed data.
     *
     * @param string $eventClass The class to listen for on the event dispatcher
     * @param callable|string $callback
     * @return self
     */
    public function listenDeleted(string $eventClass, $callback): self
    {
        $this->listenDeleted[] = [
            $eventClass,
            $callback,
        ];

        return $this;
    }

    public function extend(Container $container, ?Extension $extension = null): void
    {
        if (!class_exists($this->modelClass)) {
            return;
        }

        /**
         * @var ExtensionManager $manager
         */
        $manager = $container->make(ExtensionManager::class);

        // By design extensions will always call the extender if Scout is installed without checking if it's enabled
        // We could continue setting the container bindings which would just never be used
        // But the model scope and event listeners must be skipped if the extension is disabled
        if (!$manager->isEnabled('dshovchko-scout')) {
            return;
        }

        // It looks like we can safely repeat this call, a new scope instance of the same scope class will just
        // override the existing scope of the same class
        $this->modelClass::addGlobalScope(new FlarumSearchableScope());

        if (count($this->attributes)) {
            $container->extend('scout.attributes', function (array $attributes) use ($container) {
                foreach ($this->attributes as $callback) {
                    $attributes[$this->modelClass][] = ContainerUtil::wrapCallback($callback, $container);
                }

                return $attributes;
            });
        }

        if (count($this->searchable)) {
            $container->extend('scout.searchable', function (array $searchable) use ($container) {
                foreach ($this->searchable as $callback) {
                    $searchable[$this->modelClass][] = ContainerUtil::wrapCallback($callback, $container);
                }

                return $searchable;
            });
        }

        if (count($this->listenSaved) || count($this->listenDeleted)) {
            // Same event registration logic as in Flarum's Extend\Event extender
            $events = $container->make(Dispatcher::class);
            $app = $container->make('flarum');

            $app->booted(function () use ($events, $container) {
                foreach ($this->listenSaved as $listener) {
                    $events->listen($listener[0], function ($event) use ($listener, $container) {
                        $model = ContainerUtil::wrapCallback($listener[1], $container)($event);

                        if ($model) {
                            (new ScoutModelWrapper($model))->scoutObserverSaved();
                        }
                    });
                }

                foreach ($this->listenDeleted as $listener) {
                    $events->listen($listener[0], function ($event) use ($listener, $container) {
                        $model = ContainerUtil::wrapCallback($listener[1], $container)($event);

                        if ($model) {
                            (new ScoutModelWrapper($model))->scoutObserverDeleted();
                        }
                    });
                }
            });
        }
    }
}
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Job\RemoveFromSearch.php`

```php
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Job\RemoveFromSearch.php ===
<?php

namespace ClarkWinkelmann\Scout\Job;

use ClarkWinkelmann\Scout\ScoutModelWrapper;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class RemoveFromSearch extends \Laravel\Scout\Jobs\RemoveFromSearch
{
    use SerializesAndRestoresWrappedModelIdentifiers;

    protected function restoreCollection($value)
    {
        if (!$value->class || count($value->id) === 0) {
            return new EloquentCollection;
        }

        return new EloquentCollection(
            collect($value->id)->map(function ($id) use ($value) {
                $model = new ScoutModelWrapper(new $value->class);

                // In Laravel Scout 10+/11+, getScoutKeyName() already returns the unqualified key name
                // and getUnqualifiedScoutKeyName() has been removed.
                $keyName = $model->getScoutKeyName();

                $model->getRealModel()->forceFill([$keyName => $id]);

                return $model;
            })
        );
    }
}
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Job\SerializesAndRestoresWrappedModelIdentifiers.php`

```php
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Job\SerializesAndRestoresWrappedModelIdentifiers.php ===
<?php

namespace ClarkWinkelmann\Scout\Job;

use ClarkWinkelmann\Scout\ScoutModelWrapper;
use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Contracts\Queue\QueueableCollection;

/**
 * Overrides the right methods to allow passing a collection of wrapped models to a queued job
 */
trait SerializesAndRestoresWrappedModelIdentifiers
{
    protected function getSerializedPropertyValue($value, $withRelations = true)
    {
        if ($value instanceof QueueableCollection) {
            $first = $value->first();

            if ($first instanceof ScoutModelWrapper) {
                return new ModelIdentifier(
                    // This is the reason we need to extend this whole method, because the class is otherwise not accessible
                    // We could also extend the RemoveableScoutCollection that would only help for one of the Scout jobs
                    get_class($first->getRealModel()),
                    // This can be left as-is, we modified the method called in ScoutModelWrapper
                    $value->getQueueableIds(),
                    $withRelations ? $value->getQueueableRelations() : [],
                    $value->getQueueableConnection()
                );
            }
        }

        return parent::getSerializedPropertyValue($value, $withRelations);
    }

    protected function restoreCollection($value)
    {
        return parent::restoreCollection($value)->map(function ($model) {
            // The class stored is the real class, now we wrap it again so we don't have to modify every method in this job
            return new ScoutModelWrapper($model);
        });
    }
}
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Search\Ranking\RankingStrategyInterface.php`

```php
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Search\Ranking\RankingStrategyInterface.php ===
<?php

namespace ClarkWinkelmann\Scout\Search\Ranking;

use Flarum\Search\SearchState;

interface RankingStrategyInterface
{
    public function apply(SearchState $search, string $bit): void;
}
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Search\Ranking\DefaultRankingStrategy.php`

```php
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Search\Ranking\DefaultRankingStrategy.php ===
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
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Search\Ranking\ExactTitlePostRankingStrategy.php`

```php
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Search\Ranking\ExactTitlePostRankingStrategy.php ===
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

class ExactTitlePostRankingStrategy implements RankingStrategyInterface
{
    /**
     * @param DatabaseSearchState $search
     */
    public function apply(SearchState $search, string $bit): void
    {
        $normalizedBit = trim(preg_replace('~\s+~u', ' ', $bit));
        $exactBit = '"' . str_replace('"', '\\"', $normalizedBit) . '"';

        $discussionExactTitleIds = Discussion::whereVisibleTo($search->getActor())
            ->where('title', $normalizedBit)
            ->pluck('id')
            ->all();

        $discussionExactPhraseIds = ScoutStatic::makeBuilder(Discussion::class, $exactBit)->keys()->all();
        $discussionExactTitleIds = array_values(array_unique(array_merge($discussionExactTitleIds, $discussionExactPhraseIds)));

        $discussionBuilder = ScoutStatic::makeBuilder(Discussion::class, $bit);
        $discussionIds = $discussionBuilder->keys()->all();

        $discussionAllIds = array_values(array_unique(array_merge($discussionExactTitleIds, $discussionIds)));

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
                    ->whereNotNull('posts_ft.most_relevant_post_id')
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
            $discussionExactTitleIdsSql,
            $discussionIds,
            $discussionIdsSql,
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
                    "WHEN exact_posts.discussion_id IS NOT NULL THEN 1 " .
                    "WHEN {$prefix}discussions.id IN (" . $discussionIdsSql . ") THEN 2 " .
                    "WHEN posts_ft.most_relevant_post_id IS NOT NULL THEN 3 " .
                    "ELSE 4 END",
                    array_merge($discussionExactTitleIds, $discussionIds)
                )
                ->orderByRaw(
                    "CASE " .
                    "WHEN {$prefix}discussions.id IN (" . $discussionExactTitleIdsSql . ") THEN FIELD({$prefix}discussions.id, " . $discussionExactTitleIdsSql . ") " .
                    "WHEN exact_posts.discussion_id IS NOT NULL THEN FIELD(posts_ft.most_relevant_post_id" . $postExactIdsSql . ") " .
                    "WHEN {$prefix}discussions.id IN (" . $discussionIdsSql . ") THEN FIELD({$prefix}discussions.id, " . $discussionIdsSql . ") " .
                    "WHEN posts_ft.most_relevant_post_id IS NOT NULL THEN FIELD(posts_ft.most_relevant_post_id" . $postIdsSql . ") " .
                    "ELSE 0 END",
                    array_merge(
                        $discussionExactTitleIds,
                        $discussionExactTitleIds,
                        $postExactIds,
                        $discussionIds,
                        $discussionIds,
                        $postIdsMerged
                    )
                );
        });
    }
}
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Search\Ranking\PostsOnlyRankingStrategy.php`

```php
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Search\Ranking\PostsOnlyRankingStrategy.php ===
<?php

namespace ClarkWinkelmann\Scout\Search\Ranking;

use ClarkWinkelmann\Scout\ScoutStatic;
use Flarum\Post\Post;
use Flarum\Search\Database\DatabaseSearchState;
use Flarum\Search\SearchState;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Expression;

class PostsOnlyRankingStrategy implements RankingStrategyInterface
{
    /**
     * @param DatabaseSearchState $search
     */
    public function apply(SearchState $search, string $bit): void
    {
        $exactBit = '"' . str_replace('"', '\\"', $bit) . '"';

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
            ->where(function (EloquentBuilder|QueryBuilder $query) {
                $query->whereNotNull('posts_ft.most_relevant_post_id');
            })
            ->groupBy('discussions.id')
            ->addBinding($subquery->getBindings(), 'join')
            ->addBinding($exactPostsDiscussionsQuery->getBindings(), 'join');

        $search->setDefaultSort(function ($query) use (
            $postExactIds,
            $postExactIdsSql,
            $postIdsSql,
            $postIdsMerged
        ) {
            $query
                ->orderByRaw(
                    'CASE ' .
                    'WHEN exact_posts.discussion_id IS NOT NULL THEN 0 ' .
                    'WHEN posts_ft.most_relevant_post_id IS NOT NULL THEN 1 ' .
                    'ELSE 2 END',
                    []
                )
                ->orderByRaw(
                    'CASE ' .
                    'WHEN exact_posts.discussion_id IS NOT NULL THEN FIELD(posts_ft.most_relevant_post_id' . $postExactIdsSql . ') ' .
                    'WHEN posts_ft.most_relevant_post_id IS NOT NULL THEN FIELD(posts_ft.most_relevant_post_id' . $postIdsSql . ') ' .
                    'ELSE 0 END',
                    array_merge($postExactIds, $postIdsMerged)
                );
        });
    }
}
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Search\Ranking\TitleFirstRankingStrategy.php`

```php
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Search\Ranking\TitleFirstRankingStrategy.php ===
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
                    ->whereNotNull('posts_ft.most_relevant_post_id')
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
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Search\Ranking\TitleOnlyRankingStrategy.php`

```php
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Search\Ranking\TitleOnlyRankingStrategy.php ===
<?php

namespace ClarkWinkelmann\Scout\Search\Ranking;

use ClarkWinkelmann\Scout\ScoutStatic;
use Flarum\Discussion\Discussion;
use Flarum\Search\Database\DatabaseSearchState;
use Flarum\Search\SearchState;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class TitleOnlyRankingStrategy implements RankingStrategyInterface
{
    /**
     * @param DatabaseSearchState $search
     */
    public function apply(SearchState $search, string $bit): void
    {
        $exactBit = '"' . str_replace('"', '\\"', $bit) . '"';

        $discussionExactTitleIds = Discussion::whereVisibleTo($search->getActor())
            ->where('title', $bit)
            ->pluck('id')
            ->all();

        $discussionExactBuilder = ScoutStatic::makeBuilder(Discussion::class, $exactBit);
        $discussionExactIds = $discussionExactBuilder->keys()->all();

        $discussionBuilder = ScoutStatic::makeBuilder(Discussion::class, $bit);
        $discussionIds = $discussionBuilder->keys()->all();

        $discussionAllIds = array_values(array_unique(array_merge($discussionExactTitleIds, $discussionExactIds, $discussionIds)));

        $discussionExactTitleIdsSql = count($discussionExactTitleIds) > 0 ? implode(', ', array_fill(0, count($discussionExactTitleIds), '?')) : '0';
        $discussionExactIdsSql = count($discussionExactIds) > 0 ? implode(', ', array_fill(0, count($discussionExactIds), '?')) : '0';
        $discussionIdsSql = count($discussionIds) > 0 ? implode(', ', array_fill(0, count($discussionIds), '?')) : '0';

        // In Flarum 2.0, DatabaseSearchState::getQuery() returns an Eloquent\Builder instance
        $query = $search->getQuery();
        if ($query instanceof EloquentBuilder) {
            $query = $query->getQuery();
        }
        $grammar = $query->getGrammar();

        $query
            ->where(function (EloquentBuilder|QueryBuilder $query) use ($discussionAllIds) {
                $query->whereIn('discussions.id', $discussionAllIds);
            })
            ->selectRaw($grammar->wrapTable('discussions') . '.first_post_id as most_relevant_post_id')
            ->groupBy('discussions.id');

        $search->setDefaultSort(function ($query) use (
            $discussionExactTitleIds,
            $discussionExactIds,
            $discussionExactTitleIdsSql,
            $discussionExactIdsSql,
            $discussionIds,
            $discussionIdsSql
        ) {
            $prefix = $query->getConnection()->getTablePrefix();
            $query
                ->orderByRaw(
                    "CASE " .
                    "WHEN {$prefix}discussions.id IN (" . $discussionExactTitleIdsSql . ") THEN 0 " .
                    "WHEN {$prefix}discussions.id IN (" . $discussionExactIdsSql . ") THEN 1 " .
                    "WHEN {$prefix}discussions.id IN (" . $discussionIdsSql . ") THEN 2 " .
                    "ELSE 3 END",
                    array_merge($discussionExactTitleIds, $discussionExactIds, $discussionIds)
                )
                ->orderByRaw(
                    "CASE " .
                    "WHEN {$prefix}discussions.id IN (" . $discussionExactTitleIdsSql . ") THEN FIELD({$prefix}discussions.id, " . $discussionExactTitleIdsSql . ") " .
                    "WHEN {$prefix}discussions.id IN (" . $discussionExactIdsSql . ") THEN FIELD({$prefix}discussions.id, " . $discussionExactIdsSql . ") " .
                    "WHEN {$prefix}discussions.id IN (" . $discussionIdsSql . ") THEN FIELD({$prefix}discussions.id, " . $discussionIdsSql . ") " .
                    "ELSE 0 END",
                    array_merge(
                        $discussionExactTitleIds,
                        $discussionExactTitleIds,
                        $discussionExactIds,
                        $discussionExactIds,
                        $discussionIds,
                        $discussionIds
                    )
                );
        });
    }
}
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Search\DiscussionGambit.php`

```php
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Search\DiscussionGambit.php ===
<?php

namespace ClarkWinkelmann\Scout\Search;

use ClarkWinkelmann\Scout\Search\Ranking\DefaultRankingStrategy;
use ClarkWinkelmann\Scout\Search\Ranking\ExactTitlePostRankingStrategy;
use ClarkWinkelmann\Scout\Search\Ranking\PostsOnlyRankingStrategy;
use ClarkWinkelmann\Scout\Search\Ranking\RankingStrategyInterface;
use ClarkWinkelmann\Scout\Search\Ranking\TitleFirstRankingStrategy;
use ClarkWinkelmann\Scout\Search\Ranking\TitleOnlyRankingStrategy;
use Flarum\Search\AbstractFulltextFilter;
use Flarum\Search\Database\DatabaseSearchState;
use Flarum\Search\SearchState;
use Flarum\Settings\SettingsRepositoryInterface;

/**
 * @extends AbstractFulltextFilter<DatabaseSearchState>
 */
class DiscussionGambit extends AbstractFulltextFilter
{
    protected SettingsRepositoryInterface $settings;

    /**
     * @var array<string, RankingStrategyInterface>
     */
    protected array $strategies = [];

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function search(SearchState $state, string $value): void
    {
        $strategyKey = $this->settings->get('clarkwinkelmann-scout.rankingStrategy') ?: 'default';

        $strategy = $this->resolveStrategy($strategyKey);
        $strategy->apply($state, $value);
    }

    public function apply(SearchState $search, string $bit): void
    {
        $this->search($search, $bit);
    }

    protected function resolveStrategy(string $strategyKey): RankingStrategyInterface
    {
        if (isset($this->strategies[$strategyKey])) {
            return $this->strategies[$strategyKey];
        }

        $strategy = match ($strategyKey) {
            'title_first' => new TitleFirstRankingStrategy(),
            'exact_title_post' => new ExactTitlePostRankingStrategy(),
            'title_only' => new TitleOnlyRankingStrategy(),
            'posts_only' => new PostsOnlyRankingStrategy(),
            default => new DefaultRankingStrategy(),
        };

        $this->strategies[$strategyKey] = $strategy;

        return $strategy;
    }
}
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Search\UserGambit.php`

```php
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Search\UserGambit.php ===
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
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Search\ImprovedGambitManager.php`

```php
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\Search\ImprovedGambitManager.php ===
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
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\FlarumEngineManager.php`

```php
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\FlarumEngineManager.php ===
<?php

namespace ClarkWinkelmann\Scout;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\SearchClient as Algolia;
use Algolia\AlgoliaSearch\Support\UserAgent;
use Exception;
use Flarum\Foundation\Paths;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Database\ConnectionInterface;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\AlgoliaEngine;
use TeamTNT\Scout\Engines\TNTSearchEngine;
use TeamTNT\TNTSearch\TNTSearch;

class FlarumEngineManager extends EngineManager
{
    /**
     * Same as original createAlgoliaDriver() but uses Flarum settings as source
     */
    public function createAlgoliaDriver(): AlgoliaEngine
    {
        $this->ensureAlgoliaClientIsInstalled();

        if (class_exists(UserAgent::class)) {
            UserAgent::addCustomUserAgent('Laravel Scout', '11.0.0');
        }

        /**
         * @var SettingsRepositoryInterface $settings
         */
        $settings = resolve(SettingsRepositoryInterface::class);

        $config = SearchConfig::create(
            (string)$settings->get('clarkwinkelmann-scout.algoliaId'),
            (string)$settings->get('clarkwinkelmann-scout.algoliaSecret')
        )->setDefaultHeaders(
            $this->defaultAlgoliaHeaders()
        );

        if (is_numeric($connectTimeout = $settings->get('clarkwinkelmann-scout.algoliaConnectTimeout'))) {
            $config->setConnectTimeout((int)$connectTimeout);
        }

        if (is_numeric($readTimeout = $settings->get('clarkwinkelmann-scout.algoliaReadTimeout'))) {
            $config->setReadTimeout((int)$readTimeout);
        }

        if (is_numeric($writeTimeout = $settings->get('clarkwinkelmann-scout.algoliaWriteTimeout'))) {
            $config->setWriteTimeout((int)$writeTimeout);
        }

        return new AlgoliaEngine(Algolia::createWithConfig($config));
    }

    public function createTntsearchDriver(): TNTSearchEngine
    {
        if (!class_exists(TNTSearch::class) || !class_exists(TNTSearchEngine::class)) {
            throw new Exception('Please install the TNTSearch scout package: teamtnt/laravel-scout-tntsearch-driver.');
        }

        $storage = resolve(Paths::class)->storage . '/tntsearch';

        // TNTSearch won't create the folder automatically, so we need to do it
        if (!file_exists($storage)) {
            mkdir($storage, 0775, true);
        }

        /**
         * @var SettingsRepositoryInterface $settings
         */
        $settings = resolve(SettingsRepositoryInterface::class);

        $tnt = new TNTSearch();
        $tnt->loadConfig([
            'storage' => $storage,
            'searchBoolean' => true,
        ]);
        $tnt->setDatabaseHandle(resolve(ConnectionInterface::class)->getPdo());
        $tnt->maxDocs = (int)($settings->get('clarkwinkelmann-scout.tntsearchMaxDocs') ?: 500);
        $tnt->fuzziness = (bool)$settings->get('clarkwinkelmann-scout.tntsearchFuzziness');
        $tnt->fuzzy_distance = (int)($settings->get('clarkwinkelmann-scout.tntsearchFuzzyDistance') ?: 2);
        $tnt->fuzzy_prefix_length = (int)($settings->get('clarkwinkelmann-scout.tntsearchFuzzyPrefixLength') ?: 50);
        $tnt->fuzzy_max_expansions = (int)($settings->get('clarkwinkelmann-scout.tntsearchFuzzyMaxExpansions') ?: 2);
        $tnt->asYouType = true;

        return new TNTSearchEngine($tnt);
    }

    /**
     * Same as original getDefaultDriver() but uses Flarum settings as source
     * @return mixed|string
     */
    public function getDefaultDriver()
    {
        /**
         * @var SettingsRepositoryInterface $settings
         */
        $settings = resolve(SettingsRepositoryInterface::class);

        $driver = $settings->get('clarkwinkelmann-scout.driver');

        if (is_null($driver) || $driver === '') {
            return 'null';
        }

        return $driver;
    }
}
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\ScoutModelWrapper.php`

```php
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\ScoutModelWrapper.php ===
<?php

namespace ClarkWinkelmann\Scout;

use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;

class ScoutModelWrapper extends Model
{
    use Searchable;

    protected $table = 'scout_should_not_be_used';

    protected ?Model $realModel = null;

    public function __construct(?Model $realModel = null)
    {
        $this->realModel = $realModel;

        parent::__construct([]);
    }

    public static function resolveObserveAttributes(): array
    {
        return [];
    }

    public function getRealModel(): Model
    {
        return $this->realModel;
    }

    /**
     * We could do without this method, but there are instances where we copy-pasted the original Scout code which calls
     * newQuery() directly on the scout model, so we'll proxy it to the real underlying model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery()
    {
        return $this->realModel->newQuery();
    }

    /**
     * Combined with the code in SerializesAndRestoresWrappedModelIdentifiers this allows serializing our special wrapped model
     * With only a minimal number of classes overrides (this method is called in Laravel's collection which would be trickier to override)
     * @return mixed
     */
    public function getQueueableId()
    {
        return $this->realModel->getQueueableId();
    }

    public static function bootSearchable()
    {
        // Override original to remove scope (we call it on the real model),
        // observer (we use events and we are not saving the wrapper model anyway)
        // and collection macros (we do it in the service provider)
    }

    public function registerSearchableMacros()
    {
        throw new \Exception('Flarum implementation does not use Searchable::registerSearchableMacros');
    }

    public function queueMakeSearchable($models)
    {
        throw new \Exception('Flarum implementation does not use Searchable::queueMakeSearchable');
    }

    public function queueRemoveFromSearch($models)
    {
        throw new \Exception('Flarum implementation does not use Searchable::queueRemoveFromSearch');
    }

    public function shouldBeSearchable(): bool
    {
        $callbacks = resolve('scout.searchable');

        foreach (array_reverse(array_merge([get_class($this->realModel)], class_parents($this->realModel))) as $class) {
            if (Arr::exists($callbacks, $class)) {
                foreach ($callbacks[$class] as $callback) {
                    $returnValue = $callback($this->realModel);

                    if (is_bool($returnValue)) {
                        return $returnValue;
                    }
                }
            }
        }

        return true;
    }

    public function searchIndexShouldBeUpdated(): bool
    {
        return true;
    }

    public static function search($query = '', $callback = null)
    {
        throw new \Exception('Static functions not available in Scout for Flarum');
    }

    public static function makeAllSearchable($chunk = null)
    {
        throw new \Exception('Static functions not available in Scout for Flarum. Use ScoutStatic::makeAllSearchable');
    }

    public static function removeAllFromSearch()
    {
        throw new \Exception('Static functions not available in Scout for Flarum. Use ScoutStatic::removeAllFromSearch');
    }

    public function queryScoutModelsByIds(Builder $builder, array $ids)
    {
        $query = $this->realModel->newQuery();

        if ($builder->queryCallback) {
            call_user_func($builder->queryCallback, $query);
        }

        $whereIn = in_array($this->realModel->getKeyType(), ['int', 'integer']) ?
            'whereIntegerInRaw' :
            'whereIn';

        return $query->{$whereIn}(
            $this->realModel->qualifyColumn($this->getScoutKeyName()),
            $ids
        );
    }

    public static function enableSearchSyncing()
    {
        throw new \Exception('Static functions not available in Scout for Flarum');
    }

    public static function disableSearchSyncing()
    {
        throw new \Exception('Static functions not available in Scout for Flarum');
    }

    public static function withoutSyncingToSearch($callback)
    {
        throw new \Exception('Static functions not available in Scout for Flarum');
    }

    public function searchableAs(): string
    {
        /**
         * @var SettingsRepositoryInterface $settings
         */
        $settings = resolve(SettingsRepositoryInterface::class);

        return $settings->get('clarkwinkelmann-scout.prefix') . $this->realModel->getTable();
    }

    public function toSearchableArray(): array
    {
        $callbacks = resolve('scout.attributes');

        $attributes = [];

        foreach (array_reverse(array_merge([get_class($this->realModel)], class_parents($this->realModel))) as $class) {
            if (Arr::exists($callbacks, $class)) {
                foreach ($callbacks[$class] as $callback) {
                    $attributes = array_merge(
                        $attributes,
                        $callback($this->realModel, $attributes)
                    );
                }
            }
        }

        return $attributes;
    }

    public function syncWithSearchUsing()
    {
        return config('scout.queue.connection') ?: config('queue.default');
    }

    public function syncWithSearchUsingQueue()
    {
        return config('scout.queue.queue');
    }

    public function pushSoftDeleteMetadata()
    {
        throw new \Exception('Native Laravel soft delete meta not implemented in Scout for Flarum');
    }

    public function getScoutKey()
    {
        return $this->realModel->getKey();
    }

    /**
     * In Laravel Scout 10+/11+, getScoutKeyName() must return the unqualified key name.
     */
    public function getScoutKeyName()
    {
        return $this->realModel->getKeyName();
    }

    protected static function usesSoftDelete()
    {
        throw new \Exception('Native Laravel soft delete meta not implemented in Scout for Flarum');
    }

    /**
     * Replaces the ModelObserver's callback, to be called by event listeners in Flarum instead
     */
    public function scoutObserverSaved()
    {
        if (!$this->searchIndexShouldBeUpdated()) {
            return;
        }

        if (!$this->shouldBeSearchable()) {
            if ($this->wasSearchableBeforeUpdate()) {
                $this->unsearchable();
            }

            return;
        }

        $this->searchable();
    }

    /**
     * Replaces the ModelObserver's callback, to be called by event listeners in Flarum instead
     */
    public function scoutObserverDeleted()
    {
        if (!$this->wasSearchableBeforeDelete()) {
            return;
        }

        $this->unsearchable();
    }
}
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\ScoutServiceProvider.php`

```php
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\ScoutServiceProvider.php ===
<?php

namespace ClarkWinkelmann\Scout;

use ClarkWinkelmann\Scout\Job\MakeSearchable;
use ClarkWinkelmann\Scout\Job\RemoveFromSearch;
use Flarum\Foundation\AbstractServiceProvider;
use Flarum\Frontend\Assets;
use Flarum\Frontend\Compiler\Source\SourceCollector;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Collection;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Scout;
use Meilisearch\Client as MeilisearchClient;

/**
 * Similar to Laravel\Scout\ScoutServiceProvider without the config and command parts
 */
class ScoutServiceProvider extends AbstractServiceProvider
{
    public function register()
    {
        if (class_exists(MeilisearchClient::class)) {
            $this->container->singleton(MeilisearchClient::class, function () {
                /**
                 * @var SettingsRepositoryInterface $settings
                 */
                $settings = $this->container->make(SettingsRepositoryInterface::class);

                return new MeilisearchClient(
                    $settings->get('clarkwinkelmann-scout.meilisearchHost') ?: '127.0.0.1:7700',
                    $settings->get('clarkwinkelmann-scout.meilisearchKey')
                );
            });

            // Backward-compatible container alias in case legacy casing is resolved
            $this->container->alias(MeilisearchClient::class, 'MeiliSearch\Client');
        }

        $this->container->singleton(EngineManager::class, function ($app) {
            return new FlarumEngineManager($app);
        });

        Scout::makeSearchableUsing(MakeSearchableDisable::class);
        Scout::removeFromSearchUsing(MakeSearchableDisable::class);

        $this->container->singleton('scout.searchable', function () {
            return [];
        });

        $this->container->singleton('scout.attributes', function () {
            return [];
        });

        $this->container->resolving('flarum.assets.forum', function (Assets $assets) {
            /**
             * @var SettingsRepositoryInterface $settings
             */
            $settings = $this->container->make(SettingsRepositoryInterface::class);

            $length = (int)$settings->get('clarkwinkelmann-scout.queryMinLength');

            if ($length > 0) {
                $assets->js(function (SourceCollector $sources) use ($length) {
                    $sources->addString(function () use ($length) {
                        return "app.initializers.add('scout-min-length',function(){if(window.flarum&&flarum.reg){var apply=function(m){if(m)m.MIN_SEARCH_LEN=$length;};if(flarum.reg.checkModule){apply(flarum.reg.checkModule('core','forum/components/Search'));apply(flarum.reg.checkModule('core','common/components/AbstractGlobalSearch'));}if(flarum.reg.onLoad){flarum.reg.onLoad('core','forum/components/Search',apply);flarum.reg.onLoad('core','forum/components/SearchModal',apply);flarum.reg.onLoad('core','common/components/SearchModal',apply);}}});";
                    });
                });
            }
        });
    }

    public function boot()
    {
        Collection::macro('searchable', function () {
            /**
             * @var Collection $this
             */
            if ($this->isEmpty()) {
                return;
            }

            $wrappedCollection = $this->map(function ($model) {
                if ($model instanceof ScoutModelWrapper) {
                    return $model;
                }

                return new ScoutModelWrapper($model);
            });

            $first = $wrappedCollection->first();

            $settings = resolve(SettingsRepositoryInterface::class);

            if (!$settings->get('clarkwinkelmann-scout.queue')) {
                return $first->searchableUsing()->update($wrappedCollection);
            }

            resolve(Dispatcher::class)->dispatch(new MakeSearchable($wrappedCollection));
        });

        Collection::macro('unsearchable', function () {
            /**
             * @var Collection $this
             */
            if ($this->isEmpty()) {
                return;
            }

            $wrappedCollection = $this->map(function ($model) {
                if ($model instanceof ScoutModelWrapper) {
                    return $model;
                }

                return new ScoutModelWrapper($model);
            });

            $first = $wrappedCollection->first();

            $settings = resolve(SettingsRepositoryInterface::class);

            if (!$settings->get('clarkwinkelmann-scout.queue')) {
                return $first->searchableUsing()->delete($wrappedCollection);
            }

            resolve(Dispatcher::class)->dispatch(new RemoveFromSearch($wrappedCollection));
        });
    }
}
```

### `C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\ScoutStatic.php`

```php
=== DOSYA: C:\Users\sonsu\Documents\GitHub\flarum-ext-scout\src\ScoutStatic.php ===
<?php

namespace ClarkWinkelmann\Scout;

use ClarkWinkelmann\AdvancedSearchHighlight\Highlighter;
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Arr;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\MeilisearchEngine;

/**
 * All the static method can't be implemented on UniversalModel since we don't know the target model
 * This class re-implements those methods identically, except they take the model class as first parameter
 */
class ScoutStatic
{
    // This is not really the attributes to highlight, rather it's the attributes highlights will be extracted for
    // The actual list of attributes to highlight is configured in the advanced-search-highlight extension
    public static array $attributesToHighlight = [
        Discussion::class => [
            'title',
        ],
        Post::class => [
            'content',
        ],
    ];

    protected static function useQueue(): bool
    {
        $settings = resolve(SettingsRepositoryInterface::class);
        return (bool)$settings->get('clarkwinkelmann-scout.queue');
    }

    /**
     * Replacement for Searchable::makeAllSearchable
     * @param string $class
     * @param int|null $chunk
     */
    public static function makeAllSearchable(string $class, $chunk = null): void
    {
        $self = new ScoutModelWrapper(new $class);

        $self->newQuery()
            ->when(true, function ($query) use ($self) {
                // We would need to change the visibility of this method to work here
                // Since we know we are not using it, we will comment it out for now
                //$self->makeAllSearchableUsing($query);
            })
            ->orderBy($self->getRealModel()->getKeyName())
            ->searchable($chunk);
    }

    /**
     * Replacement for Searchable::removeAllFromSearch
     * @param string $class
     */
    public static function removeAllFromSearch(string $class): void
    {
        $self = new ScoutModelWrapper(new $class);

        $self->searchableUsing()->flush($self);
    }

    /**
     * Shortcut to obtain a Builder instance with the wrapper already set
     * @param string $class
     * @param string $query
     * @param callable|null $callback
     * @return Builder
     */
    public static function makeBuilder(string $class, string $query, ?callable $callback = null): Builder
    {
        $wrapped = new ScoutModelWrapper(new $class);

        $isMeilisearch = $wrapped->searchableUsing() instanceof MeilisearchEngine;

        if ($isMeilisearch && is_null($callback) && class_exists(Highlighter::class)) {
            $callback = function ($meilisearch, $query, $searchParams) use ($class) {
                $attributes = Arr::get(self::$attributesToHighlight, $class) ?? [];

                $results = $meilisearch->rawSearch($query, $searchParams + [
                    'attributesToHighlight' => $attributes,
                    'showMatchesPosition' => true,
                ]);

                foreach ($results['hits'] ?? [] as $hit) {
                    foreach ($attributes as $attribute) {
                        $positions = Arr::get($hit, '_matchesPosition.' . $attribute);

                        if (is_array($positions)) {
                            foreach ($positions as $position) {
                                // Meilisearch start index is in bytes while the length is in characters
                                // This requires a mix of str and mb_str methods to extract characters in the right places
                                $textStartingAtPosition = substr($hit[$attribute], $position['start']);
                                $after = mb_substr($textStartingAtPosition, $position['length'], 1);

                                // 4 bytes back should be enough to find a valid UTF8 character at the end
                                $backtrackFromStartToFindFullCharacter = min($position['start'], 4);

                                Highlighter::addHighlightRule(
                                    mb_substr($textStartingAtPosition, 0, $position['length']),
                                    $position['start'] === 0 ? null : mb_substr(substr($hit[$attribute], $position['start'] - $backtrackFromStartToFindFullCharacter, $backtrackFromStartToFindFullCharacter), -1),
                                    $after === '' ? null : $after
                                );
                            }
                        }
                    }
                }

                return $results;
            };
        }

        $builder = resolve(Builder::class, [
            'model' => $wrapped,
            'query' => $query,
            'callback' => $callback,
        ]);

        $settings = resolve(SettingsRepositoryInterface::class);
        $limit = (int)$settings->get('clarkwinkelmann-scout.limit');

        // This becomes the new default for every usage of the Builder class
        // Developers can still customize it after getting the instance
        if ($limit > 0) {
            $builder->take($limit);
        } elseif ($isMeilisearch) {
            // Meilisearch default limit of 20 is extremely low
            // If you have a large number of models that aren't visible to guests
            // you might end up with not a single result after the visibility scope is applied
            $builder->take(200);
        }

        return $builder;
    }
}
```
