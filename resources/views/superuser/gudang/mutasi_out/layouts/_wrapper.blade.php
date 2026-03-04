@extends('superuser.app')

@section('content')
<style>
    body { background-color:#1f242a; }

    .crm-wrapper {
        max-width:1000px;
        margin:0 auto;
        height:calc(100vh - 90px);
    }

    .crm-card {
        height:100%;
        border-radius:16px;
    }

    .crm-card .card-body {
        padding:16px;
        overflow-y:auto;
    }

    .status-tabs {
        display:flex;
        gap:6px;
    }

    .status-tab {
        border-radius:10px;
        padding:6px 14px;
        font-size:13px;
        border:1px solid #dee2e6;
        background:#fff;
        cursor:pointer;
    }

    .status-tab.active {
        background:#0d6efd;
        color:#fff;
        border-color:#0d6efd;
    }
</style>

<div class="container-fluid px-2">
    <div class="crm-wrapper">
        <div class="card crm-card">
            <div class="card-body">

                @yield('page-header')
                @yield('page-content')

            </div>
        </div>
    </div>
</div>

@endsection