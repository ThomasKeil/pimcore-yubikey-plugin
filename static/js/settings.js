/**
 * Created by thomas on 11.08.14.
 */
pimcore.registerNS("yubikey.settings");
yubikey.settings = Class.create({

    initialize: function () {

        this.getData();
    },

    getData: function () {
        Ext.Ajax.request({
            url: "/plugin/YubiKey/settings/settings",
            success: function (response) {
                this.data = Ext.decode(response.responseText);
                this.getTabPanel();

            }.bind(this)
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
                        xtype:'fieldset',
                        title: t('Server'),
                        collapsible: true,
                        collapsed: true,
                        autoHeight:true,
                        labelWidth: 250,
                        items: [
                            {
                                xtype: "textfield",
                                fieldLabel: t("Delivery URL"),
                                name: "deliveryurl",
                                value: this.data.delivery.url,
                                width: 400
                            },
                            {
                                xtype: "checkbox",
                                fieldLabel: t("Sandbox benutzen"),
                                name: "deliveryusesandbox",
                                checked: this.data.delivery.sandbox
                            },
                            {
                                xtype: "textfield",
                                fieldLabel: t("Delivery Sandbox URL"),
                                name: "deliveryurl_sandbox",
                                value: this.data.delivery.url_sandbox,
                                width: 400
                            },
                            {
                                xtype: "checkbox",
                                fieldLabel: t("Log Requests"),
                                name: "deliverylogrequests",
                                checked: this.data.delivery.logrequests
                            }
                        ]
                    },
                    {
                        xtype:'fieldset',
                        title: t('RSA'),
                        collapsible: true,
                        collapsed: true,
                        autoHeight:true,
                        labelWidth: 250,
                        items: [
                            {
                                xtype: "textfield",
                                fieldLabel: t("User"),
                                name: "openitsmsuser",
                                value: this.data.openitsms.user,
                                width: 400
                            },

                            {
                                xtype: "textfield",
                                fieldLabel: t("Passwort"),
                                name: "openitsmspassword",
                                value: this.data.openitsms.password,
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