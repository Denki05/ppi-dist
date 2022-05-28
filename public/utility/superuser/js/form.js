$('form').attr('autocomplete', 'off');

$('form.ajax').submit(function (e) {
  e.preventDefault();

  var form = $(this);

  form.find("button[type='submit']").prop('disabled', true);

  let data = new FormData(this);

  $.ajax({
    url: $(this).data('action'),
    data: data,
    contentType: false,
    cache: false,
    processData: false,
    type: $(this).data('type'),
    beforeSend: function () {
      Codebase.layout('header_loader_on')
    },
    complete: function () {
      Codebase.layout('header_loader_off')
    }
  }).done(function (response) {
    if (objHasProp(response, 'data.notification')) {
      responded(response.data.notification);
    }

    if (objHasProp(response, 'data.redirect_to')) {
      redirect(response.data.redirect_to, 3000);
    } else {
      form.find("button[type='submit']").prop('disabled', false);
    }
  }).fail(function (request, status, error) {
    if (objHasProp(request, 'responseJSON.data.notification')) {
      responded(request.responseJSON.data.notification);
    }

    if (objHasProp(request, 'responseJSON.data.redirect_to')) {
      redirect(request.responseJSON.data.redirect_to, 3000);
    } else {
      form.find("button[type='submit']").prop('disabled', false);
    }

    if (objHasProp(request, 'status') && request.status == 429) {
      Codebase.helpers('notify', {
        align: 'right',
        from: 'top',
        type: 'warning',
        icon: 'fa fa-ban mr-5',
        message: "Too many attempts, try again later"
      });
    }
  });
})