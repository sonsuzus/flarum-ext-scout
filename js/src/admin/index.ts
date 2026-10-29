import app from 'flarum/admin/app';

app.initializers.add('clarkwinkelmann-scout', () => {
  app.registry
    .for('clarkwinkelmann-scout')
    .registerSetting({
      setting: 'clarkwinkelmann-scout.driver',
      label: app.translator.trans('clarkwinkelmann-scout.admin.setting.driver'),
      type: 'select',
      options: {
        null: app.translator.trans('clarkwinkelmann-scout.admin.setting.driverOption.null'),
        algolia: app.translator.trans('clarkwinkelmann-scout.admin.setting.driverOption.algolia'),
        meilisearch: app.translator.trans('clarkwinkelmann-scout.admin.setting.driverOption.meilisearch'),
        tntsearch: app.translator.trans('clarkwinkelmann-scout.admin.setting.driverOption.tntsearch'),
      },
      default: 'null',
    })
    .registerSetting({
      setting: 'clarkwinkelmann-scout.prefix',
      label: app.translator.trans('clarkwinkelmann-scout.admin.setting.prefix'),
      type: 'text',
    })
    .registerSetting({
      setting: 'clarkwinkelmann-scout.queue',
      label: app.translator.trans('clarkwinkelmann-scout.admin.setting.queue'),
      help: app.translator.trans('clarkwinkelmann-scout.admin.setting.queueHelp'),
      type: 'boolean',
    })
    .registerSetting({
      setting: 'clarkwinkelmann-scout.limit',
      label: app.translator.trans('clarkwinkelmann-scout.admin.setting.limit'),
      help: app.translator.trans('clarkwinkelmann-scout.admin.setting.limitHelp'),
      type: 'number',
      placeholder: app.translator.trans('clarkwinkelmann-scout.admin.setting.limitPlaceholder'),
    })
    .registerSetting({
      setting: 'clarkwinkelmann-scout.queryMinLength',
      label: app.translator.trans('clarkwinkelmann-scout.admin.setting.queryMinLength'),
      help: app.translator.trans('clarkwinkelmann-scout.admin.setting.queryMinLengthHelp'),
      type: 'number',
      placeholder: app.translator.trans('clarkwinkelmann-scout.admin.setting.queryMinLengthPlaceholder'),
    })
    .registerSetting({
      setting: 'clarkwinkelmann-scout.rankingStrategy',
      label: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategy'),
      help: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategyHelp'),
      type: 'select',
      options: {
        default: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategyOption.default'),
        exact_title_post: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategyOption.exact_title_post'),
        posts_only: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategyOption.posts_only'),
        title_first: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategyOption.title_first'),
        title_only: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategyOption.title_only'),
      },
      default: 'default',
    })
    .registerSetting({
      setting: 'clarkwinkelmann-scout.algoliaId',
      label: app.translator.trans('clarkwinkelmann-scout.admin.setting.algoliaId'),
      type: 'text',
    })
    .registerSetting({
      setting: 'clarkwinkelmann-scout.algoliaSecret',
      label: app.translator.trans('clarkwinkelmann-scout.admin.setting.algoliaSecret'),
      type: 'password',
    })
    .registerSetting({
      setting: 'clarkwinkelmann-scout.algoliaConnectTimeout',
      label: app.translator.trans('clarkwinkelmann-scout.admin.setting.algoliaConnectTimeout'),
      type: 'number',
    })
    .registerSetting({
      setting: 'clarkwinkelmann-scout.algoliaReadTimeout',
      label: app.translator.trans('clarkwinkelmann-scout.admin.setting.algoliaReadTimeout'),
      type: 'number',
    })
    .registerSetting({
      setting: 'clarkwinkelmann-scout.algoliaWriteTimeout',
      label: app.translator.trans('clarkwinkelmann-scout.admin.setting.algoliaWriteTimeout'),
      type: 'number',
    })
    .registerSetting({
      setting: 'clarkwinkelmann-scout.meilisearchHost',
      label: app.translator.trans('clarkwinkelmann-scout.admin.setting.meilisearchHost'),
      type: 'text',
      placeholder: '127.0.0.1:7700',
    })
    .registerSetting({
      setting: 'clarkwinkelmann-scout.meilisearchKey',
      label: app.translator.trans('clarkwinkelmann-scout.admin.setting.meilisearchKey'),
      type: 'password',
    })
    .registerSetting({
      setting: 'clarkwinkelmann-scout.tntsearchMaxDocs',
      label: app.translator.trans('clarkwinkelmann-scout.admin.setting.tntsearchMaxDocs'),
      type: 'number',
    })
    .registerSetting({
      setting: 'clarkwinkelmann-scout.tntsearchFuzziness',
      label: app.translator.trans('clarkwinkelmann-scout.admin.setting.tntsearchFuzziness'),
      type: 'boolean',
    })
    .registerSetting({
      setting: 'clarkwinkelmann-scout.tntsearchFuzzyDistance',
      label: app.translator.trans('clarkwinkelmann-scout.admin.setting.tntsearchFuzzyDistance'),
      type: 'number',
    })
    .registerSetting({
      setting: 'clarkwinkelmann-scout.tntsearchFuzzyPrefixLength',
      label: app.translator.trans('clarkwinkelmann-scout.admin.setting.tntsearchFuzzyPrefixLength'),
      type: 'number',
    })
    .registerSetting({
      setting: 'clarkwinkelmann-scout.tntsearchFuzzyMaxExpansions',
      label: app.translator.trans('clarkwinkelmann-scout.admin.setting.tntsearchFuzzyMaxExpansions'),
      type: 'number',
    });
});