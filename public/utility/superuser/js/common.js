function deleteConfirmation(delete_url, quickRedirectBack = false) {
  Swal.fire({
    title: 'Are you sure?',
    type: 'warning',
    showCancelButton: true,
    allowOutsideClick: false,
    allowEscapeKey: false,
    allowEnterKey: false,
    backdrop: false,
  }).then(result => {
    if (result.value) {
      Swal.fire({
        title: 'Deleting...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        backdrop: false,
        onOpen: () => {
          Swal.showLoading()
        }
      })
      $.ajax({
        url: delete_url,
        type: 'DELETE'
      }).then( response => {
        Swal.fire({
          title: 'Deleted!',
          text: 'Your data has been deleted.',
          type: 'success',
          backdrop: false,
        }).then(() => {
          if (quickRedirectBack) {
            redirect('back()')
          }
          if (objHasProp(response, 'data.redirect_to')) {
            redirect(response.data.redirect_to);
          }
        })
      })
      .catch(error => {
        Swal.fire('Error!',`${error.statusText}`,'error')
      });
    }
  });
}

function restoreConfirmation(restore_url) {
  Swal.fire({
    title: 'Are you sure?',
    type: 'warning',
    showCancelButton: true,
    allowOutsideClick: false,
    allowEscapeKey: false,
    allowEnterKey: false,
    backdrop: false,
  }).then(result => {
    if (result.value) {
      Swal.fire({
        title: 'Restoring...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        backdrop: false,
        onOpen: () => {
          Swal.showLoading()
        }
      })
      $.ajax({
        url: restore_url,
        type: 'GET'
      }).then( response => {
        Swal.fire({
          title: 'Restored!',
          text: 'Your data has been restored.',
          type: 'success',
          backdrop: false,
        }).then(() => {
          if (objHasProp(response, 'data.redirect_to')) {
            redirect(response.data.redirect_to);
          }
        })
      })
      .catch(error => {
        Swal.fire('Error!',`${error.statusText}`,'error')
      });
    }
  });
}

function addLoadSpiner(el) {
  if (el.length > 0) {
    if ($("#img_" + el[0].id).length > 0) {
      $("#img_" + el[0].id).css('display', 'block');
    }               
    else {
      var img = $('<img class="ddloading">');
      img.attr('id', "img_" + el[0].id);
      img.attr('src', base_url + '/superuser_assets/media/loading.gif');
      img.css({ 'display': 'inline-block', 'width': '25px', 'height': '25px', 'position': 'absolute', 'left': '50%', 'margin-top': '5px' });
      img.prependTo(el[0].nextElementSibling);
    }
    el.prop("disabled", true);               
  }
}

function hideLoadSpinner(el) {
  if (el.length > 0) {
    if ($("#img_" + el[0].id).length > 0) {
      setTimeout(function () {
        $("#img_" + el[0].id).css('display', 'none');
        el.prop("disabled", false);
      }, 500);                  
    }
  }
}

function select2_clear(el) {
  el.empty().trigger('change')
  let ph = new Option('', '', false, false);
  el.append(ph).trigger('change');
}
