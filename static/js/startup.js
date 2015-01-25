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


pimcore.registerNS("pimcore.plugin.yubikey");

pimcore.plugin.yubikey = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return "pimcore.plugin.yubikey";
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },
 
    pimcoreReady: function (params,broker){
        var toolbar = pimcore.globalmanager.get("layout_toolbar");


        //var menuItems = new Ext.menu.Menu({cls: "pimcore_navigation_flyout"});
        var user = pimcore.globalmanager.get("user");

        var settings_action= new Ext.Action({
            id: "yubikey_settings_button",
            text: t('YubiKey Settings'),
            iconCls:"yubikey_icon_menu_settings",
            handler: function(){
                try {
                    pimcore.globalmanager.get("yubikey_settings").activate();
                } catch (e) {
                    pimcore.globalmanager.add("yubikey_settings", new pimcore.plugin.yubikey.settings());
                }

            }
        });
        toolbar.settingsMenu.addItem(settings_action);

    }
});

var yubikeyPlugin = new pimcore.plugin.yubikey();

