<!-- resources/views/admin/attendance_dashboard.blade.php -->
@extends('layouts.admin')
@section('content')
<div id="attendance-admin-root">
  <attendance-admin-dashboard></attendance-admin-dashboard>
</div>
@endsection
@push('scripts')
<script type="module" src="/js/attendance-admin.js"></script>
@endpush
