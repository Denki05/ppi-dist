$(function () {
  ajaxSetup();
});

function ajaxSetup() {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
}

if ($('form').length) {
  setInterval(function () {
    $.ajax({
      url: '/token',
      type: 'POST',
    }).fail(function (result) {
      $('meta[name="csrf-token"]').attr('content', result.responseJSON.data.csrf_token);

      ajaxSetup();
    });
  }, 1000 * 60 * 15); //every 15 mins
}

function defaultFor(arg, val) {
  return typeof arg !== 'undefined' ? arg : val;
}

function objHasProp(obj, key) {
  return key.split(".").every(function (x) {
    if (typeof obj == "object" && obj !== null && x in obj) {
      obj = obj[x];
      return true;
    }
    
    return false;
  });
}

function responded(notification = null) {
  /**
   * notification = {
   *  'alert => 'block|notify',
   *  'type' => 'primary|secondary|success|info|warning|error',
   *  'content => string|array
   * }
   */

  if (notification == null) {
    return;
  }

  switch (notification.alert) {
    case 'block':
      let notif_content = Array.isArray(notification.content) ? notification.content.join("<br>") : notification.content;
      let html =
        '<div class="alert ' + notification.type + ' alert-dismissable mb-5" role="alert">' +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
        '<span aria-hidden="true">×</span>' +
        '</button>' +
        '<h3 class="alert-heading font-size-h4 font-w400">'+ notification.header +'</h3>' +
        '<p class="mb-0" id="alert-content">' + notif_content + '</p>'
        // '<p class="mb-0" id="alert-content">' + notification.content + '</p>'
      '</div>';

      // if ($('div#alert-block .alert').length) {
      //   $('div#alert-block .alert').remove();
      // }

      let alert_block = $('div#alert-block')

      if (alert_block.length < 1) {
        log('div#alert-block is missing')
      } else {
        $('div#alert-block').append(html).hide().show('normal');
      }

      break;

    case 'notify':
      Codebase.helpers('notify', {
        align: 'right',
        from: 'top',
        type: notification.type,
        icon: 'fa fa-info mr-5',
        message: notification.content
      });
      break;
  }
}

function redirect(url = null, timer = 0) {
  if (url == null) {
    return;
  }

  if (url.includes('datatable')) {
    $(url).DataTable().ajax.reload();
    return;
  }

  setTimeout(function () {
    if (url == 'reload()') {
      window.location.reload();
    } else if (url == 'back()') {
      history.back()
    } else {
      window.location.href = url;
    }
  }, timer)
}

function log(str) {
  console.log('%c ' + str + ' ', 'background: #222; color: #fff',);
}

function ajaxGet(url) {
  var res

  $.ajax({
    url: url,
    // contentType: false,
    async: false,
    // cache: false,
    // processData: false,
    type: 'GET',
  }).done(function (response) {
    res = response
  }).fail(function (request, status, error) {
    res = request
  });

  return res
}

function nospaces(t){
  if(t.value.match(/\s/g)){
    t.value=t.value.replace(/\s/g,'');
  }
}