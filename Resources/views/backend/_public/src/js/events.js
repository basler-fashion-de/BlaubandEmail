$(function () {
  registerEvents()
  registerElements()
})

function registerElements () {
  $('button').button()
  $('select').selectmenu()
  $('input[type="text"]').addClass('ui-widget-content')
  $(document).tooltip()
}

function registerEvents () {
  registerSendMailButton()
  registerPrevMailsButton()
  registerNextMailsButton()
  registerBackButton()
  registerOpenOrderButton()
  registerSendButton()
  registerExecuteSendButton()

}

function registerSendMailButton () {
  $(plugin_selector + ' #send-mail-button').on('click', function () {
    location.href = $(this).data('url')
  })
}

function registerPrevMailsButton () {
  $(plugin_selector + ' #prev-mails-button').on('click', function () {
    location.href = location.origin + location.pathname + '?offset=' + (offset - limit)
  })
}

function registerNextMailsButton () {
  $(plugin_selector + ' #next-mails-button').on('click', function () {
    location.href = location.origin + location.pathname + '?offset=' + (offset + limit)
  })
}

function registerBackButton () {
  $(plugin_selector + ' #back-button').on('click', function () {
    location.href = $(this).data('url')
  })
}

function registerOpenOrderButton () {
  $(plugin_selector + ' .open-order-link').on('click', function () {
    orderId = $(this).data('order-id')
    openOrderWindow(orderId)
  })
}

function registerSendButton () {
  $(plugin_selector + ' #send-button').on('click', function () {
    $('#send-mail-form').submit()
  })
}

function registerExecuteSendButton () {
  $(plugin_selector + ' #execute-send-button').on('click', function () {
    var url = $(this).data('url')
    var params = $('input, textarea, select').serialize()

    $.ajax({
      type: 'post',
      url: url,
      data: params,
      success: function (response) {
        hideInfoPanel()
        hideErrorPanel()

        if (response.success) {
          alert(sendSuccessSnippet)
          $(plugin_selector + ' #back-button').click()
        } else {
          showErrorPanel(response.message)
        }
      }
    })
  })
}