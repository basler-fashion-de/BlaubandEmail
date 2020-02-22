function hideErrorPanel() {
    $(plugin_selector + ' .alerts .ui-state-error').hide();
    $(plugin_selector + ' .alerts .ui-state-error .content').text('');
}

function hideInfoPanel() {
    $(plugin_selector + ' .alerts .ui-state-highlight').hide();
    $(plugin_selector + ' .alerts .ui-state-highlight .content').text('');
}

function showErrorPanel(text) {
    if (text !== '') {
        $(plugin_selector + ' .alerts .ui-state-error .content').text(text);
        $(plugin_selector + ' .alerts .ui-state-error').show();

        if (hideErrorAfter) {
            setTimeout(function () {
                hideErrorPanel()
            }, hideErrorAfter);
        }
    }
}

function showInfoPanel(text) {
    if (text !== '') {
        $(plugin_selector + ' .alerts .ui-state-highlight .content').text(text);
        $(plugin_selector + ' .alerts .ui-state-highlight').show();

        if (hideErrorAfter) {
            setTimeout(function () {
                hideInfoPanel()
            }, hideErrorAfter);
        }
    }
}

function openNewIframe(title, controller, action, params, additional) {
    var values = {
        component: 'customSubWindow',
        url: controller + '/' + action + '?' + jQuery.param(params),
        title: title
    };
    jQuery.extend(values, additional);
    postMessageApi.createSubWindow(values);
}

function openOrderWindow(id) {
    postMessageApi.openModule({
        name: 'Shopware.apps.Order',
        params: {
            orderId: id
        }
    });
}

function openCustomerWindow(id) {
    postMessageApi.openModule({
        name: 'Shopware.apps.Customer',
        action: 'detail',
        params: {
            customerId: id
        }
    });
}

function openModal(selector, buttons) {
    $(selector).dialog({
        resizable: false,
        height: 'auto',
        width: '50%',
        modal: true,
        buttons: buttons
    });
    $('button').blur();
}