
//{block name="backend/customer/view/detail/window"}
//{$smarty.block.parent}

Ext.define('Shopware.apps.BlaubandEmail.view.detail.Window', {
  /**
   * Override the customer detail window
   * @string
   */
  override: 'Shopware.apps.Customer.view.detail.Window',

  getTabs: function() {
    var me = this,
      result = me.callParent();

    result.push(Ext.create('Shopware.apps.BlaubandEmail.view.common.EmailTab', {
      orderId: '',
      customerId: me.record.data.id
    }));

    return result;
  }
});

//{/block}
