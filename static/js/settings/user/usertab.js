/**
 * This source file is subject to the new BSD license that is
 * available through the world-wide-web at this URL:
 * http://www.pimcore.org/license
 *
 * @category   Pimcore
 * @copyright  Copyright (c) 2015 Weblizards GmbH (http://www.weblizards.de)
 * @author     Thomas Keil <thomas@weblizards.de>
 * @license    http://www.pimcore.org/license     New BSD License
 */

pimcore.settings.user.usertab = Class.create(pimcore.settings.user.usertab, {
    initPanel: function ($super) {
        $super();

        this.keymapping = new yubikey.settings.user.user.settings(this);

        this.panel.add(this.keymapping.getPanel());
    },

    loadComplete: function ($super, transport) {
        var response = Ext.decode(transport.responseText);
        if (response && response.success) {
            this.data = response;

            Ext.Ajax.request({
                url: "/plugin/YubiKey/user/load",
                success: this.loadYubiKeyComplete.bind(this),
                params: {
                    id: this.id
                }
            });

        }
    },

    loadYubiKeyComplete: function (transport) {
        var response = Ext.decode(transport.responseText);
        if (response && response.success) {
            this.data.yubikey = response.yubikey;
        }
        this.initPanel();
    },

    save: function ($super) {
        $super();

        var data = {
            id: this.id
        };

        try {
            data.keymapping = Ext.encode(this.keymapping.getValues());
        } catch (e) {
            console.log(e);
        }

        Ext.Ajax.request({
            url: "/plugin/YubiKey/user/save",
            method: "post",
            params: data,
            success: function (transport) {
                try{
                    var res = Ext.decode(transport.responseText);
                    if (!res.success) {
                        pimcore.helpers.showNotification(t("error"), t("Error saving YubiKey information"), "error",t(res.message));
                    }
                } catch(e){
                    pimcore.helpers.showNotification(t("error"), t("Error on contacting Server to save YubiKey information"), "error");
                }
            }.bind(this)
        });


    }
});

