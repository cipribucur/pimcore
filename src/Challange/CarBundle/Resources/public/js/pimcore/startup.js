pimcore.registerNS("pimcore.plugin.ChallangeCarBundle");

pimcore.elementservice.importAssetCsv = function (id) {

    Ext.Ajax.request({
        url: Routing.generate('pimcore_admin_asset_carimport'),
        method: "GET",
        params: {
            id: id
        },
        success: function(response) {
            var result = Ext.decode(response.responseText);
            if(result.success) {
                pimcore.helpers.showNotification(t('web2print_cancel_generation'), t('web2print_cancel_generation_error'), "error");
                //alert("ChallangeCarBundle import done!");
            }
        }.bind(this)
    });
};

pimcore.plugin.ChallangeCarBundle = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.ChallangeCarBundle";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {
         //alert("ChallangeCarBundle ready!");
    },

    postOpenAsset: function (object, type) {

        // if (object.data.general.o_className == 'ShopProduct') {

            object.toolbar.add({
                text: t('do-import'),
                iconCls: 'pimcore_material_icon_upload',
                scale: 'small',
                handler: function () {
                    pimcore.elementservice.importAssetCsv(object.id)
                }.bind(this)
            });
            pimcore.layout.refresh();
        // }
    }
});

var ChallangeCarBundlePlugin = new pimcore.plugin.ChallangeCarBundle();
