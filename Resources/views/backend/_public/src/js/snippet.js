$(function () {
  $('#customSnippets').on('selectmenuchange', function () {
    var $me = $(this)
    var dataId = 'customSnippetsData' + $me.val()
    $('.customSnippetsData').hide()
    $('#' + dataId).show()
  })

  $('.snippetRow').on('click', function () {
    insertInTextarea($(this).find('.snippet').text());
  })

  function insertInTextarea (value) {
    var textarea = document.getElementById('mailContent');
    value = value.replace(new RegExp("\\\\n","gm"),"\n");

    if (document.selection) {
      //IE
      textarea.focus()
      var sel = document.selection.createRange()
      sel.text = value
      textarea.focus()
    } else if (textarea.selectionStart || textarea.selectionStart == '0') {
      var startPos = textarea.selectionStart
      var endPos = textarea.selectionEnd
      var scrollTop = textarea.scrollTop
      textarea.value = textarea.value.substring(0, startPos) + value + textarea.value.substring(endPos, textarea.value.length)
      textarea.focus()
      textarea.selectionStart = startPos + value.length
      textarea.selectionEnd = startPos + value.length
      textarea.scrollTop = scrollTop
    } else {
      textarea.value += value
      textarea.focus()
    }
  }
})