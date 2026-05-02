@extends('reports.layouts.report')

@section('title', 'Attendance Sheet')
@section('report-subtitle', 'Attendance Sheet – Paid Applicants')

@section('extra-styles')
<style>
    .col-sign { width: 120pt; }
</style>
@endsection

@section('content')
<div class="report-meta">
    <span><span class="label">Exam:</span> {{ $exam->name }}</span>
    <span><span class="label">Total Paid Applicants:</span><span class="summary-badge">{{ $applications->count() }}</span></span>
</div>

<table class="report-table">
    <thead>
        <tr>
            <th class="col-sl">SL</th>
            <th class="col-appid">App. ID</th>
            <th>Applicant Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th class="col-sign">Attendance Signature</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($applications as $index => $application)
            <tr>
                <td class="col-sl">{{ $index + 1 }}</td>
                <td>{{ $application->application_id ?? $application->ulid }}</td>
                <td>{{ $application->applicant_name }}</td>
                <td>{{ $application->applicant_phone }}</td>
                <td>{{ $application->applicant_email }}</td>
                <td class="col-sign"></td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="muted" style="text-align:center;">No paid applicants found for this exam.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
