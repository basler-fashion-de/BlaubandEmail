


Ext.define('Shopware.apps.BlaubandEmail.view.common.EmailTab', {
  extend: 'Ext.container.Container',
  padding: 10,
  title: 'Email',
  customerId: null,
  orderId: null,

  initComponent: function() {
    var me = this;
    var url = '{url controller="BlaubandEmail"}/index/customerId/'+me.customerId+'/orderId/'+me.orderId;
    var instance = Shopware.ModuleManager.uuidGenerator.generate();

    var frame = Shopware.ModuleManager.createContentFrame(
      url,
      instance,
      true
    );

    me.items  =  [{
      xtype: 'label',
      html: frame.dom.outerHTML
    }];

    me.callParent(arguments);
  }
});


