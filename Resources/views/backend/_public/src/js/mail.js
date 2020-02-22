$(function () {
  $('#mails').tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
});

function fetchInputFormData () {
  var inputs = fetchInput();
  var formData = new FormData();

  for (var k in inputs){
    if (inputs.hasOwnProperty(k)) {
      formData.append(k, inputs[k]);
    }
  }

  return formData;
}

function fetchInput () {
  var params = $('input, textarea, select');
  var result = {};

  $('.mail-attachment').each(function (i, el) {
    var obj = {};
    var key = 'file' + i;
    obj[key] = this.files[0];
    Object.assign(result, obj);
  });

  for (var i = 0; i < params.length; i++) {
    if(params[i].name && params[i].value){
      var obj = {};
      var key = params[i].name;
      obj[key] = params[i].value;
      Object.assign(result, obj);
    }
  }

  return result;
}