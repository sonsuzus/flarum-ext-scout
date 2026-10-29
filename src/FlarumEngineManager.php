<?php

namespace ClarkWinkelmann\Scout;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\Support\UserAgent;
use Exception;
use Flarum\Foundation\Paths;
use Flarum\Settings\SettingsRepositoryInterface;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\AlgoliaEngine;
use Laravel\Scout\Engines\MeilisearchEngine;
use Laravel\Scout\Engines\NullEngine;
use Meilisearch\Client as MeilisearchClient;
use TeamTNT\Scout\Engines\TNTSearchEngine;
use TeamTNT\TNTSearch\TNTSearch;

class FlarumEngineManager extends EngineManager
{
    public function createAlgoliaDriver(): AlgoliaEngine
    {
        /** @var SettingsRepositoryInterface $settings */
        $settings = $this->container->make(SettingsRepositoryInterface::class);

        if (class_exists(UserAgent::class)) {
            UserAgent::addCustomUserAgent('Laravel Scout', '10.0.0');
        }

        $config = SearchConfig::create(
            (string)$settings->get('clarkwinkelmann-scout.algoliaId'),
            (string)$settings->get('clarkwinkelmann-scout.algoliaSecret')
        );

        if ($connectTimeout = $settings->get('clarkwinkelmann-scout.algoliaConnectTimeout')) {
            $config->setConnectTimeout((int)$connectTimeout);
        }

        if ($readTimeout = $settings->get('clarkwinkelmann-scout.algoliaReadTimeout')) {
            $config->setReadTimeout((int)$readTimeout);
        }

        if ($writeTimeout = $settings->get('clarkwinkelmann-scout.algoliaWriteTimeout')) {
            $config->setWriteTimeout((int)$writeTimeout);
        }

        return new AlgoliaEngine(SearchClient::createWithConfig($config));
    }

    public function createMeilisearchDriver(): MeilisearchEngine
    {
        /** @var SettingsRepositoryInterface $settings */
        $settings = $this->container->make(SettingsRepositoryInterface::class);

        $host = $settings->get('clarkwinkelmann-scout.meilisearchHost');
        $key = $settings->get('clarkwinkelmann-scout.meilisearchKey');

        return new MeilisearchEngine(
            new MeilisearchClient(
                $host ?: 'http://127.0.0.1:7700',
                $key ?: null
            )
        );
    }

    public function createMeiliSearchDriver(): MeilisearchEngine
    {
        return $this->createMeilisearchDriver();
    }

    public function createTntsearchDriver(): TNTSearchEngine
    {
        if (!class_exists(TNTSearch::class)) {
            throw new Exception('Please install the TNTSearch driver package: teamtnt/laravel-scout-tntsearch-driver');
        }

        /** @var SettingsRepositoryInterface $settings */
        $settings = $this->container->make(SettingsRepositoryInterface::class);
        /** @var Paths $paths */
        $paths = $this->container->make(Paths::class);

        $tnt = new TNTSearch();

        $storagePath = $paths->storage . '/tntsearch';
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0775, true);
        }

        $config = array_merge($this->container->make('flarum.db')->getConfig(), [
            'storage' => $storagePath,
            'fuzziness' => (bool)$settings->get('clarkwinkelmann-scout.tntsearchFuzziness'),
            'fuzzy' => [
                'prefix_length' => (int)($settings->get('clarkwinkelmann-scout.tntsearchFuzzyPrefixLength') ?: 2),
                'max_expansions' => (int)($settings->get('clarkwinkelmann-scout.tntsearchFuzzyMaxExpansions') ?: 50),
                'distance' => (int)($settings->get('clarkwinkelmann-scout.tntsearchFuzzyDistance') ?: 2),
            ],
            'asYouType' => false,
            'searchBoolean' => false,
        ]);

        $tnt->loadConfig($config);
        $tnt->setDatabaseHandle($this->container->make('flarum.db')->getPdo());

        if ($maxDocs = $settings->get('clarkwinkelmann-scout.tntsearchMaxDocs')) {
            $tnt->maxDocs = (int)$maxDocs;
        }

        return new TNTSearchEngine($tnt);
    }

    public function getDefaultDriver(): string
    {
        /** @var SettingsRepositoryInterface $settings */
        $settings = $this->container->make(SettingsRepositoryInterface::class);

        return $settings->get('clarkwinkelmann-scout.driver') ?: 'null';
    }
}