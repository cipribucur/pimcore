pimcore.registerNS("pimcore.plugin.ChallengeAssetImportBundle");

pimcore.elementservice.AssetImportBundleCsv = function (id) {

    Ext.Ajax.request({
        url: Routing.generate('pimcore_admin_asset_carimport'),
        method: "GET",
        params: {
            id: id
        },
        success: function(response) {
            var result = Ext.decode(response.responseText);
            if(result.success) {
                pimcore.helpers.showNotification(t('Import done'), "success");
            }
            if (result.failedArticles) {
                pimcore.helpers.showNotification(result.failedArticles, "error");
            }
            if(!result.success) {
                pimcore.helpers.showNotification(t('Import failed :('), "error");
            }
        }.bind(this)
    });
};

pimcore.plugin.ChallengeAssetImportBundle = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.ChallengeAssetImportBundle";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    postOpenAsset: function (object, type) {

        object.toolbar.add({
            text: t('do-import'),
            iconCls: 'pimcore_material_icon_upload',
            scale: 'small',
            handler: function () {
                pimcore.elementservice.AssetImportBundleCsv(object.id)
            }.bind(this)
        });
        pimcore.layout.refresh();
    }
});

var ChallengeAssetImportBundlePlugin = new pimcore.plugin.ChallengeAssetImportBundle();
