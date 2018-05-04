
//{block name="backend/order/view/detail/window"}
//{$smarty.block.parent}

Ext.define('Shopware.apps.BlaubandEmail.view.order.Window', {
  /**
   * Override the customer detail window
   * @string
   */
  override: 'Shopware.apps.Order.view.detail.Window',

  createTabPanel: function() {
    var me = this,
      result = me.callParent();

    result.add(Ext.create('Shopware.apps.BlaubandEmail.view.common.EmailTab', {
      orderId: me.record.data.id,
      customerId: me.record.data.customerId
    }));
    return result;
  }
});

//{/block}