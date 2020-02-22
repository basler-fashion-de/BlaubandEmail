$(function () {
  registerEvents();
  registerElements();

  showNewsletterPopup()
});

function registerElements () {
  $('button:not(.no-ui)').button();
  $('select:not(.no-ui)').selectmenu();
  $('input[type="text"]:not(.no-ui)').addClass('ui-widget-content');
  $(document).tooltip();
}

function registerEvents () {
  registerSendMailButton();
  registerPrevMailsButton();
  registerNextMailsButton();
  registerBackButton();
  registerOpenOrderButton();
  registerOpenCustomerButton();
  registerSendButton();
  registerExecuteSendButton();
  registerCloseAdButton();
  registerPreviewButton();
  registerPreviewSendButton();

  registerNewsletterPopupEvents();
  registerAddAttachment();
}

function showNewsletterPopup () {
  setTimeout(function () {
    if (typeof showNewsletter !== 'undefined' && showNewsletter === 1)
      openNewIframe(
        newsletterSnippet,
        'BlaubandEmail',
        'newsletter',
        [],
        {
          width: 500,
          height: 300
        }
      )
  }, 1000);
}

function registerSendMailButton () {
  $(plugin_selector + ' #send-mail-button').on('click', function () {
    location.href = $(this).data('url')
  })
}

function registerPrevMailsButton () {
  $(plugin_selector + ' #prev-mails-button').on('click', function () {
    var newOffset = parseInt(offset) - parseInt(limit);
    location.href = location.origin + location.pathname + '?offset=' + newOffset
  })
}

function registerNextMailsButton () {
  $(plugin_selector + ' #next-mails-button').on('click', function () {
    var newOffset = parseInt(offset) + parseInt(limit);
    location.href = location.origin + location.pathname + '?offset=' + newOffset
  })
}

function registerBackButton () {
  $(plugin_selector + ' #back-button').on('click', function () {
    location.href = $(this).data('url')
  })
}

function registerOpenOrderButton () {
  $(plugin_selector + ' .open-order-link').on('click', function () {
    var orderId = $(this).data('order-id');
    openOrderWindow(orderId)
  })
}

function registerOpenCustomerButton () {
  $(plugin_selector + ' .open-customer-link').on('click', function () {
    var customerId = $(this).data('customer-id');
    openCustomerWindow(customerId)
  })
}

function registerSendButton () {
  $(plugin_selector + ' #send-button').on('click', function () {
    $('#send-mail-form').submit()
  })
}

function registerExecuteSendButton () {
  $(plugin_selector + ' #execute-send-button').on('click', function () {
    var url = $(this).data('url');
    var formData = fetchInputFormData();

    $.ajax({
      type: 'post',
      url: url,
      contentType: false,
      cache: false,
      processData: false,
      data: formData,
      xhr: function () {
        var jqXHR = null;
        if (window.ActiveXObject) {
          jqXHR = new window.ActiveXObject('Microsoft.XMLHTTP')
        } else {
          jqXHR = new window.XMLHttpRequest()
        }

        return jqXHR
      },
      complete: function (response) {
        hideInfoPanel();
        hideErrorPanel();

        if (response.success) {
          alert(sendSuccessSnippet);
          $(plugin_selector + ' #back-button').click()
        } else {
          showErrorPanel(response.message)
        }
      }
    })
  })
}

function registerAddAttachment () {
  $('#addAttachment').on('click', function () {
    var $me = $(this);
    var count = $('#mailContentWrapper').find('input[type="file"]').length + 1;
    $me.before('<input type="file" name="file_' + count + '" id="file_' + count + '"  class="mail-attachment" accept="image/png, image/gif, image/jpeg, application/pdf"/>')
  })
}

function registerCloseAdButton () {
  $(plugin_selector + ' .close-ad--button').on('click', function () {
    var url = $(this).data('url');
    var me = this;

    $.ajax({
      type: 'post',
      url: url,
      success: function (response) {
        $(me).parent().remove()
      }
    })
  })
}

function registerPreviewButton () {
  $(plugin_selector + ' #preview-button').on('click', function () {
    var formData = fetchInput();

    openNewIframe('Vorschau', 'BlaubandEmail', 'preview', formData);
  })
}

function registerPreviewSendButton () {
  $(plugin_selector + ' #preview-send-button').on('click', function () {
    parent[parent.length-2].document.getElementById('execute-send-button').click();
    postMessageApi.window.destroy()
  })
}

function registerNewsletterPopupEvents () {
  $(
    plugin_selector + ' #close-newsletter-popup-button, ' +
    plugin_selector + ' #register-newsletter-popup-button'
  ).on('click', function () {
    $.ajax({
      type: 'post',
      url: location.href,
      data: {newsletterShowed: 1},
    })
  });

  $(plugin_selector + ' #close-newsletter-popup-button').on('click', function () {
    postMessageApi.window.destroy()
  })
}