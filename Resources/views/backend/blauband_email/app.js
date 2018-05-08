
//{block name="backend/customer/application"}
//      {$smarty.block.parent}
//      {include file="backend/blauband_email/view/common/email_tab.js"}

var blaubandMailIcon = '/custom/plugins/BlaubandEmail/Resources/views/backend/_public/src/images/mail.png';
var blaubandMailIconStyle = 'width: 20px; cursor: pointer; margin-top: -3px;';

Ext.define( "Shopware.apps.Customer.view.Blauband.main.List",
  {
    // override
    override: "Shopware.apps.Customer.view.list.List",

    getColumns:function () {
      var me = this;
      var result = me.callParent(arguments);

      result.splice(result.length-1, 0, {
        header:'{*s namespace="blauband/mail" name="mail"}{/s*}',
        dataIndex:'id',
        renderer:me.mailColumn,
        width:32
      });

      return result;
    },

    mailColumn:function(value){
      var frameTitle = '{s namespace="blauband/mail" name="mail"}{/s}';
      var buttonTitle = '{s namespace="blauband/mail" name="sendMailShort"}{/s}';
      return '<img src="'+blaubandMailIcon+'" style="'+blaubandMailIconStyle+'" onclick="Shopware.ModuleManager.createSimplifiedModule(\'BlaubandEmail\/index/frame/1\/customerId\/'+value+'\', { \'title\': \''+frameTitle+'\'})"/>'
    }
  }
);

Ext.define( "Shopware.apps.Customer.view.Blauband.main.List",
  {
    // override
    override: "Shopware.apps.Customer.view.main.CustomerList",

    mailRenderer: function (value, meta, record) {
      var customerId = record.data.id;
      var frameTitle = '{s namespace="blauband/mail" name="mail"}{/s}';
      var buttonTitle = '{s namespace="blauband/mail" name="sendMailShort"}{/s}';
      var button = '<img src="'+blaubandMailIcon+'" style="'+blaubandMailIconStyle+'" onclick="Shopware.ModuleManager.createSimplifiedModule(\'BlaubandEmail\/index\/frame\/1\/customerId\/'+customerId+'\', { \'title\': \''+frameTitle+'\'})" />'
      var style = 'display: block;width: 130px;float: left;overflow: hidden;margin-right: 10px;'

      return Ext.String.format('<a style="'+style+'" href="mailto:[0]" data-qtip="[0]">[0]</a>'+button, value);
    }
  }
  );
//{/block}

//{block name="backend/order/application"}
//      {$smarty.block.parent}
// {* HTML Code wird an dieser Stelle nicht erlaubt *}
// Ext.define( "Shopware.apps.Customer.view.Blauband.main.List",
//   {
//     // override
//     override: "Shopware.apps.Order.view.detail.Overview",
//
//     createLeftDetailElements: function (value, meta, record) {
//       var me = this;
//       var fields = me.callParent(arguments);
//       fields.push(
//         {
//           name:'blaubandEmail',
//           fieldLabel:'{s namespace="blauband/mail" name="sendMailShort"}{/s}',
//           renderer: me.mailRenderer
//         }
//       );
//
//       return fields;
//     },
//
//     mailRenderer: function (value) {
//       debugger;
//       var customerId = 0;
//       var frameTitle = '{s namespace="blauband/mail" name="mail"}{/s}';
//       var buttonTitle = '{s namespace="blauband/mail" name="sendMailShort"}{/s}';
//       var button = '<button onclick="Shopware.ModuleManager.createSimplifiedModule(\'BlaubandEmail\/index\/frame\/1\/customerId\/'+customerId+'\', { \'title\': \''+frameTitle+'\'})">'+buttonTitle+'</button>'
//       return button;
//
//       // return Ext.create('Ext.Button', {
//       //   text: '{s namespace="blauband/mail" name="sendMail"}{/s}',
//       //   cls: 'primary',
//       //   layout: 'anchor',
//       //   width: 200,
//       //   margin: '20 0',
//       //   handler: function() {
//       //     Shopware.ModuleManager.createSimplifiedModule('BlaubandEmail/index/frame/1/customerId/'+customerId, { 'title': '{s namespace="blauband/mail" name="mail"}{/s}'});
//       //   }
//       // });
//     }
//   }
// );

//{/block}