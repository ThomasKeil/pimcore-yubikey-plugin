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


pimcore.registerNS("yubikey.settings.user.user.settings");
yubikey.settings.user.user.settings = Class.create({

    initialize:function (userPanel) {
        this.userPanel = userPanel;

        this.data = this.userPanel.data;
        this.yubikey = this.data.yubikey;
    },

    getPanel:function () {

        var user = pimcore.globalmanager.get("user");
        this.forceReloadOnSave = false;

        var items = [];

        items.push({
            xtype:"checkbox",
            fieldLabel:t("active"),
            name:"activelocal",
            checked: this.yubikey ? this.yubikey.activelocal == 1 : 0
        });

        this.store = new Ext.data.ArrayStore({
            fields: ["serial", "comment"],
            data: this.yubikey ? this.yubikey.keys : []
        });

        var typesColumns = [
            {
                header: t("Serial"),
                id: "serial",
                width: 200,
                sortable: false,
                dataIndex: 'serial',
                editor: new Ext.form.TextField({})
            },
            {
                header: t("comment"),
                id: "comment",
                width: 200,
                sortable: false,
                dataIndex: 'comment',
                editor: new Ext.form.TextField({})
            }
        ];

        typesColumns.push({
            xtype: 'actioncolumn',
            width: 30,
            items: [{
                tooltip: t('delete'),
                icon: "/pimcore/static/img/icon/cross.png",
                handler: function (grid, rowIndex) {
                    grid.getStore().removeAt(rowIndex);
                    this.updateRows();
                }.bind(this)
            }]
        });

        this.grid = new Ext.grid.EditorGridPanel({
            frame: false,
            autoScroll: true,
            store: this.store,
            columns : typesColumns,
            trackMouseOver: true,
            columnLines: true,
            stripeRows: true,
            autoExpandColumn: "path",
            autoHeight: true,
            style: "margin-bottom:20px;",
            tbar: [
                {
                    xtype: "tbtext",
                    text: "<b>" + t("Keys") + "</b>"
                },
                "-",
                {
                    iconCls: "pimcore_icon_add",
                    text: t("add"),
                    handler: this.onAdd.bind(this)
                }
            ],
            viewConfig: {
                forceFit: true
            }
        });

        items.push(this.grid);

        this.panel = new Ext.form.FormPanel({
            title:t("YubiKey Settings"),
            items: items,
            bodyStyle:"padding:10px;",
            autoScroll:true
        });

        return this.panel;
    },

    onAdd: function (btn, ev) {
        var u = new this.grid.store.recordType({
            serial: "",
            comment: ""
        });
        this.grid.store.insert(0, u);

    },

    getValues:function () {
        var values = this.panel.getForm().getFieldValues();

        var keys = [];
        this.store.commitChanges();

        var records = this.store.getRange();
        for (var i = 0; i < records.length; i++) {
            var currentData = records[i];
            if (currentData) {
                keys.push(currentData.data);
            }
        }
        values["keys"] = keys;

        return values;
    }
});