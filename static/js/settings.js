/**
 * Created by thomas on 11.08.14.
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
                console.log(this.data);
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
                            {
                                xtype: "textarea",
                                fieldLabel: t("Private Key"),
                                name: "local_privatekey",
                                value: this.data.local.privatekey,
                                width: 500,
                                height: 100
                            },
                            {
                                xtype: "textarea",
                                fieldLabel: t("Public Key"),
                                name: "local_publickey",
                                value: this.data.local.publickey,
                                width: 500,
                                height: 100
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
                                fieldLabel: t("remote_server"),
                                name: "remote_server",
                                value: this.data.remote.server,
                                width: 400
                            },
                            {
                                xtype: "textfield",
                                fieldLabel: t("port"),
                                name: "remote_port",
                                value: this.data.remote.port,
                                width: 400
                            },
                            {
                                xtype: "checkbox",
                                fieldLabel: t("SSL verwenden"),
                                name: "remote_usessl",
                                checked: this.data.remote.usessl
                            },
                            {
                                xtype: "textfield",
                                fieldLabel: t("apikey"),
                                name: "remote_apikey",
                                value: this.data.remote.apikey,
                                width: 400
                            }
                        ]
                    }

                ],
                buttons: [
                    {
                        text: "Save",
                        handler: this.save.bind(this),
                        iconCls: "pimcore_icon_apply"
                    }
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