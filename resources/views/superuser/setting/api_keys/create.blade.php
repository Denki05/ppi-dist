@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Setting</span>
  <span class="breadcrumb-item">Api Keys</span>
  <span class="breadcrumb-item active">Create</span>
</nav>

<div class="block">
    <div class="block-content block-content-full">
        <div class="row">
            <div class="col-6">
                <h2>Create API Key</h2>
                <form action="{{ route('superuser.setting.api_keys.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label>Name (optional)</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Project A">
                    </div>
                    <button type="submit" class="btn btn-primary">Create</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection