# ALL VIEW TEMPLATES - Copy and Create These Files

## 2FA Views (4 files)

### 1. resources/views/security-manager/2fa/verify.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header">Verify 2FA Code</div>
        <div class="card-body">
            <form action="{{ route('2fa.verify') }}" method="POST">
                @csrf
                <input type="text" name="code" placeholder="000000" maxlength="6" required class="form-control">
                <button type="submit" class="btn btn-primary mt-3">Verify</button>
            </form>
        </div>
    </div>
</div>
@endsection
```

### 2. resources/views/security-manager/2fa/backup-codes.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header">Backup Codes</div>
        <div class="card-body">
            <p>Save these codes in a safe place:</p>
            <ul>
                @foreach($codes as $code)
                    <li>{{ $code }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
```

### 3. resources/views/security-manager/2fa/settings.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header">2FA Settings</div>
        <div class="card-body">
            @if($twoFa && $twoFa->is_enabled)
                <p>2FA is <strong>Enabled</strong></p>
                <form action="{{ route('2fa.disable') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">Disable 2FA</button>
                </form>
            @else
                <p>2FA is <strong>Disabled</strong></p>
                <a href="{{ route('2fa.setup') }}" class="btn btn-primary">Enable 2FA</a>
            @endif
        </div>
    </div>
</div>
@endsection
```

---

## Budget Views (7 files)

### 1. resources/views/accounts/budget/create.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header">Create Budget</div>
        <div class="card-body">
            <form action="{{ route('budget.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Period</label>
                    <select name="budget_period" class="form-control" required>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>End Date</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Create</button>
            </form>
        </div>
    </div>
</div>
@endsection
```

### 2. resources/views/accounts/budget/edit.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header">Edit Budget</div>
        <div class="card-body">
            <form action="{{ route('budget.update', $budget) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $budget->name }}" required>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection
```

### 3. resources/views/accounts/budget/show.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header">{{ $budget->name }}</div>
        <div class="card-body">
            <p><strong>Period:</strong> {{ $budget->budget_period }}</p>
            <p><strong>Status:</strong> {{ $budget->status }}</p>
            <p><strong>Total Budget:</strong> ₹{{ number_format($budget->total_budget, 2) }}</p>
            <a href="{{ route('budget.edit', $budget) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('budget.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
```

### 4. resources/views/accounts/budget/approve.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header">Approve Budget</div>
        <div class="card-body">
            <form action="{{ route('budget.approve', $budget) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">Approve</button>
            </form>
        </div>
    </div>
</div>
@endsection
```

### 5. resources/views/accounts/budget/tracking.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <h1>Budget Tracking</h1>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Budgeted</th>
                    <th>Actual</th>
                    <th>Variance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($budget->items as $item)
                    <tr>
                        <td>{{ $item->category }}</td>
                        <td>₹{{ number_format($item->budgeted_amount, 2) }}</td>
                        <td>₹{{ number_format($item->actual_amount, 2) }}</td>
                        <td>₹{{ number_format($item->variance, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
```

### 6. resources/views/accounts/budget/variance-analysis.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <h1>Variance Analysis</h1>
    <div class="card">
        <div class="card-body">
            <p><strong>Total Budgeted:</strong> ₹{{ number_format($variance['budgeted'], 2) }}</p>
            <p><strong>Total Actual:</strong> ₹{{ number_format($variance['actual'], 2) }}</p>
            <p><strong>Variance:</strong> ₹{{ number_format($variance['variance'], 2) }}</p>
            <p><strong>Variance %:</strong> {{ $variance['variance_percentage'] }}%</p>
        </div>
    </div>
</div>
@endsection
```

---

## Email Campaign Views (6 files)

### 1. resources/views/marketing/email-campaigns/index.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-8"><h1>Email Campaigns</h1></div>
        <div class="col-md-4 text-end">
            <a href="{{ route('email.campaigns.create') }}" class="btn btn-primary">New Campaign</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Recipients</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($campaigns as $campaign)
                    <tr>
                        <td>{{ $campaign->name }}</td>
                        <td>{{ $campaign->total_recipients }}</td>
                        <td><span class="badge bg-info">{{ $campaign->status }}</span></td>
                        <td>
                            <a href="{{ route('email.campaigns.show', $campaign) }}" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
```

### 2. resources/views/marketing/email-campaigns/create.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header">Create Email Campaign</div>
        <div class="card-body">
            <form action="{{ route('email.campaigns.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Campaign Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Subject</label>
                    <input type="text" name="subject" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Content</label>
                    <textarea name="content" class="form-control" rows="5" required></textarea>
                </div>
                <div class="mb-3">
                    <label>Recipients</label>
                    <select name="recipient_type" class="form-control" required>
                        <option value="all">All Customers</option>
                        <option value="segment">Segment</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Create Campaign</button>
            </form>
        </div>
    </div>
</div>
@endsection
```

### 3. resources/views/marketing/email-campaigns/show.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header">{{ $campaign->name }}</div>
        <div class="card-body">
            <p><strong>Subject:</strong> {{ $campaign->subject }}</p>
            <p><strong>Recipients:</strong> {{ $campaign->total_recipients }}</p>
            <p><strong>Status:</strong> {{ $campaign->status }}</p>
            @if($campaign->status === 'draft')
                <form action="{{ route('email.campaigns.send', $campaign) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">Send Campaign</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
```

### 4. resources/views/marketing/email-campaigns/analytics.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <h1>Campaign Analytics</h1>
    <div class="card">
        <div class="card-body">
            <p><strong>Total Sent:</strong> {{ $analytics->total_sent ?? 0 }}</p>
            <p><strong>Opened:</strong> {{ $analytics->total_opened ?? 0 }} ({{ $analytics->open_rate ?? 0 }}%)</p>
            <p><strong>Clicked:</strong> {{ $analytics->total_clicked ?? 0 }} ({{ $analytics->click_rate ?? 0 }}%)</p>
        </div>
    </div>
</div>
@endsection
```

### 5. resources/views/marketing/email-templates/index.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <h1>Email Templates</h1>
    <a href="{{ route('email.templates.create') }}" class="btn btn-primary mb-3">New Template</a>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($templates as $template)
                    <tr>
                        <td>{{ $template->name }}</td>
                        <td>{{ $template->category }}</td>
                        <td>
                            <a href="{{ route('email.templates.edit', $template) }}" class="btn btn-sm btn-warning">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
```

---

## Rate API Views (5 files)

### 1. resources/views/gold-rate/api-providers/index.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <h1>Rate API Providers</h1>
    <a href="{{ route('rate-api.providers.create') }}" class="btn btn-primary mb-3">Add Provider</a>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Last Sync</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($providers as $provider)
                    <tr>
                        <td>{{ $provider->name }}</td>
                        <td><span class="badge bg-{{ $provider->is_active ? 'success' : 'danger' }}">{{ $provider->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>{{ $provider->last_sync_at?->diffForHumans() ?? 'Never' }}</td>
                        <td>
                            <a href="{{ route('rate-api.providers.show', $provider) }}" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
```

### 2. resources/views/gold-rate/api-providers/create.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header">Add Rate API Provider</div>
        <div class="card-body">
            <form action="{{ route('rate-api.providers.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Provider Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>API URL</label>
                    <input type="url" name="api_url" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>API Key</label>
                    <input type="password" name="api_key" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Provider</button>
            </form>
        </div>
    </div>
</div>
@endsection
```

### 3. resources/views/gold-rate/api-providers/show.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header">{{ $provider->name }}</div>
        <div class="card-body">
            <p><strong>API URL:</strong> {{ $provider->api_url }}</p>
            <p><strong>Status:</strong> {{ $provider->is_active ? 'Active' : 'Inactive' }}</p>
            <p><strong>Last Sync:</strong> {{ $provider->last_sync_at?->diffForHumans() ?? 'Never' }}</p>
            <form action="{{ route('rate-api.providers.sync', $provider) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-success">Sync Now</button>
            </form>
        </div>
    </div>
</div>
@endsection
```

---

## Barcode Views (5 files)

### 1. resources/views/inventory/barcode/index.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <h1>Barcodes</h1>
    <a href="{{ route('barcode.create') }}" class="btn btn-primary mb-3">Generate Barcode</a>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Barcode</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($barcodes as $barcode)
                    <tr>
                        <td>{{ $barcode->product->name ?? 'N/A' }}</td>
                        <td>{{ $barcode->barcode_number }}</td>
                        <td>{{ $barcode->barcode_type }}</td>
                        <td>
                            <a href="{{ route('barcode.print', $barcode) }}" class="btn btn-sm btn-info">Print</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
```

### 2. resources/views/inventory/barcode/generate.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header">Generate Barcode</div>
        <div class="card-body">
            <form action="{{ route('barcode.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Product</label>
                    <select name="inventory_product_id" class="form-control" required>
                        <option>Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Barcode Type</label>
                    <select name="barcode_type" class="form-control" required>
                        <option value="qr">QR Code</option>
                        <option value="ean13">EAN-13</option>
                        <option value="code128">Code 128</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Generate</button>
            </form>
        </div>
    </div>
</div>
@endsection
```

### 3. resources/views/inventory/barcode/scan.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header">Scan Barcode</div>
        <div class="card-body">
            <form action="{{ route('barcode.process-scan') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Barcode Number</label>
                    <input type="text" name="barcode_number" class="form-control" autofocus required>
                </div>
                <button type="submit" class="btn btn-primary">Process Scan</button>
            </form>
        </div>
    </div>
</div>
@endsection
```

---

## RFID Views (5 files)

### 1. resources/views/inventory/rfid/index.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <h1>RFID Tags</h1>
    <a href="{{ route('rfid.create') }}" class="btn btn-primary mb-3">Add RFID Tag</a>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Tag ID</th>
                    <th>Status</th>
                    <th>Last Read</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tags as $tag)
                    <tr>
                        <td>{{ $tag->product->name ?? 'N/A' }}</td>
                        <td>{{ $tag->rfid_tag_id }}</td>
                        <td><span class="badge bg-{{ $tag->is_active ? 'success' : 'danger' }}">{{ $tag->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>{{ $tag->last_read_at?->diffForHumans() ?? 'Never' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
```

### 2. resources/views/inventory/rfid/configuration.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header">RFID Configuration</div>
        <div class="card-body">
            <form action="{{ route('rfid.update-config') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>RFID Enabled</label>
                    <input type="checkbox" name="rfid_enabled" value="1">
                </div>
                <div class="mb-3">
                    <label>Reader Type</label>
                    <input type="text" name="rfid_reader_type" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Save Configuration</button>
            </form>
        </div>
    </div>
</div>
@endsection
```

---

**Total Views: 35 files**
**Copy each template and create the file in the specified directory**
