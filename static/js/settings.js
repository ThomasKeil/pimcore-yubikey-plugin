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

pimcore.registerNS("pimcore.plugin.yubikey.settings");
pimcore.plugin.yubikey.settings = Class.create({


    initialize: function () {
        this.getData();
    },

    getData: function () {
        Ext.Ajax.request({
            url: "/plugin/YubiKey/settings/settings",
            success: function (response) {
                var data = Ext.decode(response.responseText)
                this.data = data.yubikey;
                this.getTabPanel();

            }.bind(this),
            failure: function(response, opts) {
                console.log('server-side failure with status code ' + response.status);
            }
        });
    },

    getValue: function (key) {

        var nk = key.split("\.");
        var current = this.data.values;

        for (var i = 0; i < nk.length; i++) {
            if (current[nk[i]]) {
                current = current[nk[i]];
            } else {
                current = null;
                break;
            }
        }

        if (typeof current != "object" && typeof current != "array" && typeof current != "function") {
            return current;
        }

        return "";
    },

    activate: function () {
        var tabPanel = Ext.getCmp("pimcore_panel_tabs");
        tabPanel.activate("yubikey_settings");
    },

    getTabPanel: function () {
        if (!this.panel) {
            this.panel = new Ext.Panel({
                id: "yubikey_settings",
                title: t("YubiKey Settings"),
                iconCls: "yubikey_icon_settings",
                border: false,
                layout: "fit",
                closable: true
            });

            var tabPanel = Ext.getCmp("pimcore_panel_tabs");
            tabPanel.add(this.panel);
            tabPanel.activate("yubikey_settings");


            this.panel.on("destroy", function () {
                pimcore.globalmanager.remove("yubikey_settings");
            }.bind(this));


            this.privatekey = new Ext.form.TextArea({
                fieldLabel: t("Private Key"),
                name: "local_privatekey",
                value: this.data.local.privatekey,
                width: 500,
                height: 100
            });

            this.publickey = new Ext.form.TextArea({
                fieldLabel: t("Public Key"),
                name: "local_publickey",
                value: this.data.local.publickey,
                width: 500,
                height: 100
            });


            this.layout = new Ext.FormPanel({
                layout: "pimcoreform",
                title: "YubiKey",
                bodyStyle: "padding: 10px;",
                autoScroll: true,
                items: [
                    {
                        xtype: 'fieldset',
                        title: t('Lokale Authentifizierung'),
                        collapsible: true,
                        collapsed: false,
                        autoHeight: true,
                        labelWidth: 250,
                        items: [
                            {
                                xtype: "checkbox",
                                fieldLabel: t("Lokale Authentifizierung benutzen"),
                                name: "local_uselocal",
                                checked: this.data.local.uselocal
                            }
                        ]
                    },
                    {
                        xtype:'fieldset',
                        title: t('Lokale Schlüssel'),
                        collapsible: true,
                        collapsed: false,
                        autoHeight:true,
                        labelWidth: 250,
                        items: [
                            this.privatekey,
                            this.publickey,
                            {
                                xtype: "button",
                                text: t("Neues Schlüsselpaar erzeugen"),
                                iconCls: "yubikey_icon_createkey",
                                handler: function () {

                                    Ext.Ajax.request({
                                        url: "/plugin/YubiKey/key/create",
                                        method: "get",
                                        success: function (response) {
                                            try {
                                                var res = Ext.decode(response.responseText);
                                                if (res.success) {
                                                    this.privatekey.setValue(res.keys.private);
                                                    this.publickey.setValue(res.keys.public);

                                                } else {
                                                    pimcore.helpers.showNotification(t("error"), t("yubikeyremoteauthenticator_key_error"),
                                                        "error", t(res.message));
                                                }
                                            } catch(e) {
                                                pimcore.helpers.showNotification(t("error"), t("yubikeyremoteauthenticator_key_error"), "error");
                                            }
                                        }.bind(this)
                                    });

                                }.bind(this, this.privatekey, this.publickey)
                            }
                        ]
                    },
                    {
                        xtype:'fieldset',
                        title: t('Zentrale Authentifizierung'),
                        collapsible: true,
                        collapsed: false,
                        autoHeight:true,
                        labelWidth: 250,
                        items: [
                            {
                                xtype: "checkbox",
                                fieldLabel: t("Zentrale Authentifizierung benutzen"),
                                name: "remote_useremote",
                                checked: this.data.remote.useremote
                            },
                            {
                                xtype: "textfield",
                                fieldLabel: t("identifier"),
                                name: "remote_identifier",
                                value: this.data.remote.identifier,
                                width: 400
                            },
                            {
                                xtype: "textfield",
                                fieldLabel: t("remote_server"),
                                name: "remote_server",
                                value: this.data.remote.server,
                                width: 400
                            },
                            {
                                xtype: "textarea",
                                fieldLabel: t("Public Key"),
                                name: "remote_publickey",
                                value: this.data.remote.publickey,
                                width: 500,
                                height: 100
                            }
                        ]
                    }

                ],
                tbar: [
                    {
                        text: "Save",
                        handler: this.save.bind(this),
                        iconCls: "pimcore_icon_apply"
                    }
                ],
                bbar: [
                    "<span>Developed by <a href='http://www.weblizards.de/pimcore-development/yubikey?pk_campaign=PluginSettings' target='_blank'>Weblizards - Custom Internet Solutions</a></span>",
                ]
            });

            this.panel.add(this.layout);
            tabPanel.activate("yubikey_settings");
            pimcore.layout.refresh();
        }

        return this.panel;
    },

    save: function () {
        var values = this.layout.getForm().getFieldValues();

        Ext.Ajax.request({
            url: "/plugin/YubiKey/settings/save",
            method: "post",
            params: {
                data: Ext.encode(values)
            },
            success: function (response) {
                try {
                    var res = Ext.decode(response.responseText);
                    if (res.success) {
                        pimcore.helpers.showNotification(t("success"), t("yubikey_settings_save_success"), "success");
                    } else {
                        pimcore.helpers.showNotification(t("error"), t("yubikey_settings_save_error"),
                            "error", t(res.message));
                    }
                } catch(e) {
                    pimcore.helpers.showNotification(t("error"), t("yubikeySettingsController.php_settings_save_error"), "error");
                }
            }
        });
    }

});