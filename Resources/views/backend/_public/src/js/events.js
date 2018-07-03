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
  registerOpenCustomerButton()
  registerSendButton()
  registerExecuteSendButton()
  registerAddAttachment()

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

function registerOpenCustomerButton () {
  $(plugin_selector + ' .open-customer-link').on('click', function () {
    customerId = $(this).data('customer-id')
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
      var url = $(this).data('url')
      var params = $('input, textarea, select').serializeArray()
      var formData = new FormData()

    $('.mail-attachment').each(function (i, el) {
        formData.append('file'+i, this.files[0])
      })

      for (var i = 0; i < params.length; i++) {
        formData.append(params[i].name, params[i].value)
      }

      $.ajax({
        type: 'post',
        url: url,
        contentType: false,
        cache: false,
        processData: false,
        data: formData,
        xhr: function () {
          var jqXHR = null
          if (window.ActiveXObject) {
            jqXHR = new window.ActiveXObject('Microsoft.XMLHTTP')
          }
          else {
            jqXHR = new window.XMLHttpRequest()
          }

          return jqXHR
        },
        complete: function (response) {
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
    }
  )
}

function registerAddAttachment () {
  $('#addAttachment').on('click', function () {
    var $me = $(this)
    var count = $('#mailContentWrapper').find('input[type="file"]').length + 1
      $me.before('<input type="file" name="file_' + count + '" id="file_' + count + '"  class="mail-attachment" accept="image/png, image/gif, image/jpeg, application/pdf"/>')
  })
}