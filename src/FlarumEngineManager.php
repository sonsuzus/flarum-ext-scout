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