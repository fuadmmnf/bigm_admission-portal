@extends('reports.layouts.report')

@section('title', 'Attendance Sheet')
@section('report-subtitle', 'Attendance Sheet')

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
        padding: 2pt;
        min-height: 122pt;
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
        border: 1px solid #111827;
        text-align: center;
        line-height: 68pt;
        font-size: 7.5pt;
        color: #6b7280;
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
        font-size: 9pt;
        line-height: 1.45;
    }
    .att-row {
        margin-bottom: 2pt;
    }
    .att-label {
        font-weight: bold;
    }
    .att-sign {
        margin-top: 10pt;
    }
    .att-sign-box {
        height: 26pt;
        border: 1px solid #111827;
        margin-bottom: 2pt;
    }
    .att-sign-label {
        text-align: center;
        font-size: 8pt;
        line-height: 1.2;
    }

    /* ── Attendance footer fields ─────────────────────── */
    .att-footer-field {
        font-size: 8.5pt;
        line-height: 1.3;
        margin-bottom: 10pt;
    }
    .att-footer-field .field-label {
        font-weight: bold;
        display: block;
        margin-bottom: 2pt;
    }
    .att-footer-line {
        display: inline-block;
        width: 140pt;
        border-bottom: 1px solid #111827;
    }
    .att-footer-sig {
        text-align: right;
        margin-bottom: 10pt;
    }
    .att-footer-sig .sig-box {
        height: 26pt;
        width: 150pt;
        border: 1px solid #111827;
        display: inline-block;
        margin-bottom: 2pt;
    }
    .att-footer-sig .field-label {
        font-weight: bold;
        font-size: 8pt;
        display: block;
        text-align: center;
        width: 150pt;
        margin-left: auto;
    }
</style>
@endsection

@section('footer-left')
<div class="att-footer-field">
    <span class="field-label">Invigilator's Name:</span>
    <span class="att-footer-line">&nbsp;</span>
</div>
@endsection

@section('footer-right')
<div class="att-footer-sig">
    <span class="sig-box"></span>
    <span class="field-label">Invigilator's Signature</span>
</div>
@endsection

@section('content')
<div class="report-meta"><span><span class="label">Exam:</span> {{ $exam->name }}</span></div>

@php
    $rows = $applications->values()->chunk(2);
@endphp

@if ($applications->isEmpty())
    <div class="empty-row" style="margin-top: 24px;">No paid applicants found for this exam.</div>
@else
    <table class="attendance-grid">
        <tbody>
        @foreach ($rows as $pair)
            <tr>
                @foreach ($pair as $application)
                    <td>
                        <div class="att-card">
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
                                </div>
                            </div>
                            <div class="att-sign">
                                <div class="att-sign-box"></div>
                                <div class="att-sign-label">Applicant's Signature</div>
                            </div>
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
