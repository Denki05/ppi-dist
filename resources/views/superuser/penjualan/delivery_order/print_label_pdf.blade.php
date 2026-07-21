<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
      @page { margin: 6mm; }
      body { font-family: Arial, sans-serif; font-size: 13px; }
      .box { border: 2px solid #000; padding: 10px; }
      .do-code { font-size: 20px; font-weight: bold; text-align: center; margin-bottom: 8px; }
      .row { margin-bottom: 6px; }
      .label { font-size: 10px; text-transform: uppercase; color: #555; }
      .value { font-size: 14px; font-weight: bold; }
    </style>
  </head>
  <body>
    <div class="box">
      <div class="do-code">{{ $do_code }}</div>
      <div class="row">
        <div class="label">Kepada</div>
        <div class="value">{{ $customer }}</div>
      </div>
      <div class="row">
        <div class="label">Alamat</div>
        <div class="value">{{ $address }}</div>
      </div>
      <div class="row">
        <div class="label">Telp</div>
        <div class="value">{{ $phone }}</div>
      </div>
      <div class="row">
        <div class="label">Gudang Asal</div>
        <div class="value">{{ $warehouse }}</div>
      </div>
    </div>
  </body>
</html>