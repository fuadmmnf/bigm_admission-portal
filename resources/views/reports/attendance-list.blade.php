@extends('reports.layouts.report')

@section('title', 'Attendance Sheet')
@section('report-subtitle', 'Attendance Sheet – Paid Applicants')

@section('extra-styles')
<style>
    .attendance-grid {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        table-layout: fixed;
    }
    .attendance-grid td {
        width: 50%;
        vertical-align: top;
        padding: 5pt;
    }
    .att-card {
        border: 1px solid #d1d5db;
        border-radius: 4pt;
        padding: 6pt;
        min-height: 120pt;
    }
    .att-header {
        font-size: 9px;
        font-weight: bold;
        color: #1e3a5f;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 3pt;
        margin-bottom: 5pt;
    }
    .att-body {
        display: table;
        width: 100%;
    }
    .att-photo,
    .att-info {
        display: table-cell;
        vertical-align: top;
    }
    .att-photo {
        width: 62pt;
    }
    .att-photo-box {
        width: 56pt;
        height: 68pt;
        border: 1px solid #d1d5db;
        background: #f9fafb;
        text-align: center;
        line-height: 68pt;
        font-size: 7.5pt;
        color: #9ca3af;
        overflow: hidden;
    }
    .att-photo-box img {
        width: 56pt;
        height: 68pt;
        object-fit: cover;
        display: block;
    }
    .att-info {
        padding-left: 6pt;
        font-size: 8.8pt;
        color: #111827;
        line-height: 1.45;
    }
    .att-row {
        margin-bottom: 2pt;
    }
    .att-label {
        font-weight: bold;
        color: #374151;
    }
    .att-sign {
        margin-top: 8pt;
        border-top: 1px dashed #9ca3af;
        padding-top: 2pt;
        text-align: center;
        font-size: 8pt;
        color: #6b7280;
    }
</style>
@endsection

@section('content')
<div class="report-meta">
    <span><span class="label">Exam:</span> {{ $exam->name }}</span>
    <span><span class="label">Total Paid Applicants:</span><span class="summary-badge">{{ $applications->count() }}</span></span>
</div>

@php
    $rows = $applications->values()->chunk(2);
@endphp

@if ($applications->isEmpty())
    <div class="muted" style="text-align:center; margin-top: 24px;">No paid applicants found for this exam.</div>
@else
    <table class="attendance-grid">
        <tbody>
        @foreach ($rows as $pair)
            <tr>
                @foreach ($pair as $index => $application)
                    <td>
                        <div class="att-card">
                            <div class="att-header">SL {{ $loop->parent->index * 2 + $index + 1 }}</div>
                            <div class="att-body">
                                <div class="att-photo">
                                    <div class="att-photo-box">
                                        @if($application->photo_data_uri)
                                            <img src="{{ $application->photo_data_uri }}" alt="Photo">
                                        @else
                                            Photo
                                        @endif
                                    </div>
                                </div>
                                <div class="att-info">
                                    <div class="att-row"><span class="att-label">App. ID:</span> {{ $application->application_id ?? $application->ulid }}</div>
                                    <div class="att-row"><span class="att-label">Name:</span> {{ $application->applicant_name }}</div>
                                    <div class="att-row"><span class="att-label">Phone:</span> {{ $application->applicant_phone }}</div>
                                    <div class="att-row"><span class="att-label">Email:</span> {{ $application->applicant_email }}</div>
                                </div>
                            </div>
                            <div class="att-sign">Attendance Signature</div>
                        </div>
                    </td>
                @endforeach
                @if($pair->count() === 1)
                    <td></td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
@endsection
