
//{block name="backend/customer/application"}
//      {$smarty.block.parent}
//      {include file="backend/blauband_email/view/common/email_tab.js"}

var blaubandMailIcon = '/custom/plugins/BlaubandEmail/Resources/views/backend/_public/src/images/mail.png';
var blaubandMailIconStyle = 'width: 20px; cursor: pointer; margin-top: -3px; float:left; margin-right:10px;';

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
      var style = 'width: 130px;overflow: hidden;margin-right: 10px;'

      return Ext.String.format(button+'<a style="'+style+'" href="mailto:[0]" data-qtip="[0]">[0]</a>', value);
    }
  }
  );
//{/block}

//{block name="backend/customer/application"}
//      {$smarty.block.parent}
//      {include file="backend/blauband_email/view/common/email_tab.js"}

var blaubandMailIcon = '/custom/plugins/BlaubandEmail/Resources/views/backend/_public/src/images/mail.png';
var blaubandMailIconStyle = 'width: 20px; cursor: pointer; margin-top: -3px;';

Ext.define( "Shopware.apps.Customer.view.Blauband.main.List",
  {
    // override
    override: "Shopware.apps.Order.view.list.List",

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
      return '<img src="'+blaubandMailIcon+'" style="'+blaubandMailIconStyle+'" onclick="Shopware.ModuleManager.createSimplifiedModule(\'BlaubandEmail\/index/frame/1\/orderId\/'+value+'\', { \'title\': \''+frameTitle+'\'})"/>'
    }
  }
);

//{/block}