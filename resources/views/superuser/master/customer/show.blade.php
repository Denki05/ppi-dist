@extends('superuser.app')

@section('content')
    <div class="card text-center">
        <div class="card-header">
          <h4 align="left">#CUSTOMER - {{ $customer->name }}</h4>
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#profile">PROFILE</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#geo_tag">GEO TAG</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#finance">FINANCE</a>
                </li>
                
            </ul>
        </div>
        <div class="tab-content card-body">

            <!-- PROFILE -->
            <div id="profile" class="tab-pane active">
                <div class="container">
                    <div class="row">
                        <div class="col">
                            <div class="form-group row">
                                <label for="register" class="col-sm-4 col-form-label">Registered :</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control-plaintext" id="register" value="{{ $customer->created_at }}">
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group row">
                                <label for="name" class="col-sm-4 col-form-label">Nama :</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control-plaintext" id="name" value="{{ $customer->name }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group row">
                                <label for="email" class="col-sm-4 col-form-label">E-mail :</label>
                                <div class="col-sm-8">
                                    <input type="text" readonly class="form-control-plaintext" id="email" value="{{ $customer->email }}">
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group row">
                                <label for="phone" class="col-sm-4 col-form-label">Telp :</label>
                                <div class="col-sm-8">
                                    <input type="text" readonly class="form-control-plaintext" id="phone" value="{{ $customer->phone }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group row">
                                <label for="owner" class="col-sm-4 col-form-label">Owner :</label>
                                <div class="col-sm-8">
                                    <input type="text" readonly class="form-control-plaintext" id="owner" value="{{ $customer->owner_name }}">
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group row">
                                <label for="address" class="col-sm-4 col-form-label">Alamat :</label>
                                <div class="col-sm-8">
                                    <input type="text" readonly class="form-control-plaintext" id="address" value="{{ $customer->address }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GEO TAG -->
            <div id="geo_tag" class="tab-pane">
              
            </div>

            <!-- FINANCE -->
            <div id="finance" class="tab-pane">

            </div>
        </div>
    </div>
@endsection

@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')

@push('scripts')
@endpush