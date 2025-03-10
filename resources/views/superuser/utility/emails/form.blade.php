@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Menu</span>
  <span class="breadcrumb-item">Emails</span>
  <span class="breadcrumb-item active">Send Emails</span>
</nav>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="block">
    <div class="block-header block-header-default">
        <h3 class="block-title">#Send Emails</h3>
    </div>
    <div class="block-content block-content-full">
        <div class="row">
            <div class="col">
                <form action="{{ route('superuser.utility.settings.emails.sendEmail') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="to">Kepada (Email Tujuan):</label>
                        <input type="email" class="form-control" id="to" name="to" required>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subjek:</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Pesan:</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Kirim</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection