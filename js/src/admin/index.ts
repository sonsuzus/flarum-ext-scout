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