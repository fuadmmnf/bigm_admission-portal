@extends('reports.layouts.report')

@section('title', 'Attendance Sheet')
@section('report-subtitle', 'Attendance Sheet')

@section('extra-styles')
<style>
    .attendance-table td {
        vertical-align: middle !important;
        font-size: 9pt;
    }
    .col-sign {
        width: 120pt;
    }
    .att-photo-box {
        width: 50pt;
        height: 60pt;
        border: 1px solid #111827;
        text-align: center;
        line-height: 60pt;
        font-size: 7pt;
        color: #6b7280;
        overflow: hidden;
        margin: 0 auto;
    }
    .att-photo-box img {
        width: 50pt;
        height: 60pt;
        object-fit: cover;
        display: block;
    }
    .att-sign-box {
        height: 28pt;
        border: 1px solid #111827;
        margin-bottom: 2pt;
        background: #fff;
    }
    .att-sign-label {
        text-align: center;
        font-size: 7.5pt;
        color: #374151;
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

@if ($applications->isEmpty())
    <div class="empty-row" style="margin-top: 24px;">No paid applicants found for this exam.</div>
@else
    <table class="report-table attendance-table">
        <thead>
            <tr>
                <th class="col-sl">SL No</th>
                <th>Application ID</th>
                <th>Name</th>
                <th class="col-photo">Photo</th>
                <th class="col-sign">Signature</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($applications as $index => $application)
            <tr>
                <td class="col-sl">{{ $index + 1 }}</td>
                <td>{{ $application->application_id ?? $application->ulid }}</td>
                <td>{{ $application->applicant_name }}</td>
                <td class="col-photo">
                    <div class="att-photo-box">
                        @if($application->photo_data_uri)
                            <img src="{{ $application->photo_data_uri }}" alt="Photo">
                        @else
                            Photo
                        @endif
                    </div>
                </td>
                <td class="col-sign">
                    <div class="att-sign-box"></div>
                    <div class="att-sign-label">Applicant Signature</div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
@endsection
