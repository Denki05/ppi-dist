@push('scripts')
<script>
var rajaapi_token = function() {
  let tmp = null
  $.ajax({
      'async': false,
      'type': "get",
      'global': false,
      'dataType': 'json',
      'url': 'https://x.rajaapi.com/poe',
      'success': function(data) {
        tmp = data.token
      }
  })
  return tmp
}()

$(document).ready(function () {
  $.ajax({
    url: 'https://x.rajaapi.com/MeP7c5ne' + window.rajaapi_token + '/m/wilayah/provinsi',
    type: 'GET',
    dataType: 'json',
    beforeSend: function () {
      addLoadSpiner($('#provinsi'))
    },
    complete: function () {
      hideLoadSpinner($('#provinsi'))
    },
    success: function(json) {
      if (json.code == 200) {
        for (i = 0; i < Object.keys(json.data).length; i++) {
          let newOption = new Option(json.data[i].name, json.data[i].id, false, false);
          $('#provinsi').append(newOption).trigger('change');
        }

        let data_val = $('#provinsi').data('value')
        if (data_val > 0) {
          $('#provinsi').val(data_val);
          $('#provinsi').trigger('select2:select');
        }
      }
    }
  });

  $("#provinsi").on('select2:select', function() {
    var provinsi = $("#provinsi").val();

    let text_provinsi = (objHasProp($('#provinsi').select2('data')[0], 'text')) ? $('#provinsi').select2('data')[0].text : '';
    $('input[name=text_provinsi]').val(text_provinsi);
    $('input[name=text_kota]').val('');
    $('input[name=text_kecamatan]').val('');
    $('input[name=text_kelurahan]').val('');

    $.ajax({
      url: 'https://x.rajaapi.com/MeP7c5ne' + window.rajaapi_token + '/m/wilayah/kabupaten',
      data: "idpropinsi=" + provinsi,
      type: 'GET',
      cache: false,
      dataType: 'json',
      beforeSend: function () {
        addLoadSpiner($('#kota'))
      },
      complete: function () {
        hideLoadSpinner($('#kota'))
      },
      success: function(json) {
        $('#kota').empty().trigger('change');
        $('#kecamatan').empty().trigger('change');
        $('#kelurahan').empty().trigger('change');
        
        if (json.code == 200) {
          let ph = new Option('', '', false, false);
          $('#kota').append(ph).trigger('change');

          for (i = 0; i < Object.keys(json.data).length; i++) {
            let newOption = new Option(json.data[i].name, json.data[i].id, false, false);
            $('#kota').append(newOption).trigger('change');
          }

          let data_val = $('#kota').data('value')
          if (data_val > 0) {
            $('#kota').val(data_val);
            $('#kota').trigger('select2:select');
          }
        }
      }
    });
  });

  $("#kota").on('select2:select', function() {
    var kota = $("#kota").val();

    let text_kota = (objHasProp($('#kota').select2('data')[0], 'text')) ? $('#kota').select2('data')[0].text : '';
    $('input[name=text_kota]').val(text_kota);
    $('input[name=text_kecamatan]').val('');
    $('input[name=text_kelurahan]').val('');

    $.ajax({
      url: 'https://x.rajaapi.com/MeP7c5ne' + window.rajaapi_token + '/m/wilayah/kecamatan',
      data: "idkabupaten=" + kota + "&idpropinsi=" + provinsi,
      type: 'GET',
      cache: false,
      dataType: 'json',
      beforeSend: function () {
        addLoadSpiner($('#kecamatan'))
      },
      complete: function () {
        hideLoadSpinner($('#kecamatan'))
      },
      success: function(json) {
        $('#kecamatan').empty().trigger('change');
        $('#kelurahan').empty().trigger('change');

        if (json.code == 200) {
          let ph = new Option('', '', false, false);
          $('#kecamatan').append(ph).trigger('change');

          for (i = 0; i < Object.keys(json.data).length; i++) {
            let newOption = new Option(json.data[i].name, json.data[i].id, false, false);
            $('#kecamatan').append(newOption).trigger('change');
          }

          let data_val = $('#kecamatan').data('value')
          if (data_val > 0) {
            $('#kecamatan').val(data_val);
            $('#kecamatan').trigger('select2:select');
          }
        }
      }
    });
  });

  $("#kecamatan").on('select2:select', function() {
    var kecamatan = $("#kecamatan").val();

    let text_kecamatan = (objHasProp($('#kecamatan').select2('data')[0], 'text')) ? $('#kecamatan').select2('data')[0].text : '';
    $('input[name=text_kecamatan]').val(text_kecamatan);
    $('input[name=text_kelurahan]').val('');

    $.ajax({
      url: 'https://x.rajaapi.com/MeP7c5ne' + window.rajaapi_token + '/m/wilayah/kelurahan',
      data: "idkabupaten=" + kota + "&idpropinsi=" + provinsi + "&idkecamatan=" + kecamatan,
      type: 'GET',
      dataType: 'json',
      cache: false,
      beforeSend: function () {
        addLoadSpiner($('#kelurahan'))
      },
      complete: function () {
        hideLoadSpinner($('#kelurahan'))
      },
      success: function(json) {
        $('#kelurahan').empty().trigger('change');

        if (json.code == 200) {
          let ph = new Option('', '', false, false);
          $('#kelurahan').append(ph).trigger('change');

          for (i = 0; i < Object.keys(json.data).length; i++) {
            let newOption = new Option(json.data[i].name, json.data[i].id, false, false);
            $('#kelurahan').append(newOption).trigger('change');
          }

          let data_val = $('#kelurahan').data('value')
          if (data_val > 0) {
            $('#kelurahan').val(data_val);
            $('#kelurahan').trigger('select2:select');
          }
        }
      }
    });
  });

  $("#kelurahan").on('select2:select', function() {
    let text_kelurahan = (objHasProp($('#kelurahan').select2('data')[0], 'text')) ? $('#kelurahan').select2('data')[0].text : '';
    $('input[name=text_kelurahan]').val(text_kelurahan);
  })
})
</script>
@endpush