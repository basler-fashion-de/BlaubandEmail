


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
    var frame = me.createPostMessageApiEnabledTabComponent('Email', url);

    me.items  =  [frame];

    me.callParent(arguments);
  },

  createPostMessageApiEnabledTabComponent: function (label, url) {
    var me = this,
      swModuleManager = Shopware.ModuleManager,
      instance = swModuleManager.uuidGenerator.generate(),
      content = swModuleManager.createContentFrame(url, instance, true),
      subApp = null,
      windows = Ext.create('Ext.util.MixedCollection'),
      contentWindow

    contentWindow = Ext.create('Ext.Component', {
      title: label,
      component: 'main',
      content: 'content',
      style: 'height: 100%',
      listeners: {
        render: function (component, eOpts) {
          component.el.appendChild(content)
          component.setLoading(true)
        },
      },
    })

    content.dom._window = contentWindow
    windows.add('main', contentWindow)

    swModuleManager.modules.add(instance, {
      name: url,
      instance: instance,
      subApp: subApp,
      windows: windows
    })

    return contentWindow
  }
});


