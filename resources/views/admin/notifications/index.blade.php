@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="h3 mb-4 text-gray-800">Notification Center Configuration</h2>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">SMS Gateway Settings</h6>
                </div>
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Gateway Provider</label>
                            <select class="form-select">
                                <option>Twilio</option>
                                <option>MessageBird</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">API Key</label>
                            <input type="password" class="form-control" value="************">
                        </div>
                        <button type="button" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Email Gateway Settings</h6>
                </div>
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">SMTP Host</label>
                            <input type="text" class="form-control" value="smtp.mailtrap.io">
                        </div>
                        <button type="button" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
