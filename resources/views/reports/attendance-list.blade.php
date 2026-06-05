@extends('reports.layouts.report')

@section('title', 'Attendance Sheet')
@section('report-subtitle', '')

@section('extra-styles')
    <style>

        @page {
            margin: 28mm 14mm 48mm 14mm;
        }

        .pdf-footer {
            bottom: -48mm;
            padding: 2pt 14mm 12pt;
        }

        .pdf-footer-left,
        .pdf-footer-right {
            vertical-align: top;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto !important;
        }

        .attendance-table th,
        .attendance-table td {
            vertical-align: middle !important;
            font-size: 9pt;
            padding: 4pt;
        }

        .attendance-table .sl-col {
            width: 30pt !important;
            text-align: center;
        }

        .attendance-table .app-col {
            width: 95pt !important;
            text-align: center;
        }

        .attendance-table .photo-col {
            width: 65pt !important;
            text-align: center;
        }

        .attendance-table .sign-col {
            width: 120pt !important;
        }

        .attendance-table .name-col {
            width: auto !important;
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

        .att-sign-blank {
            height: 34pt;
        }

        .att-footer-block {
            font-size: 8.5pt;
            line-height: 1.3;
            color: #111827;
            width: 150pt;
        }

        .att-footer-block.right {
            margin-left: auto;
            text-align: right;
        }

        .att-footer-row {
            margin-bottom: 8pt;
        }

        .att-footer-row:last-child {
            margin-bottom: 0;
        }

        .att-footer-box {
            width: 150pt;
            height: 28pt;
            border: 1px solid #111827;
            display: block;
        }

        .att-footer-label {
            width: 150pt;
            display: block;
            margin-top: 2pt;
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
        }

        .att-footer-block.right .att-footer-box,
        .att-footer-block.right .att-footer-label {
            margin-left: auto;
        }

    </style>
@endsection

@section('footer-left')
    <div class="att-footer-block" style="margin-bottom: 10px">
        <div class="att-footer-row">
            <span class="att-footer-box"></span>
            <span class="att-footer-label">Invigilator Name</span>
        </div>
        <div class="att-footer-row">
            <span class="att-footer-box"></span>
            <span class="att-footer-label">Invigilator Signature</span>
        </div>
    </div>
@endsection

@section('footer-right')
    <div class="att-footer-block right" style="margin-bottom: 10px">
        <div class="att-footer-row">
            <span class="att-footer-box"></span>
            <span class="att-footer-label">Invigilator Name</span>
        </div>
        <div class="att-footer-row">
            <span class="att-footer-box"></span>
            <span class="att-footer-label">Invigilator Signature</span>
        </div>
    </div>
@endsection

@section('content')

    <div class="report-header">
        <p class="report-header-subtitle" style="font-size: 15px; margin: 0; color: #111827;">{{ $exam->name }}</p>
        <p class="report-header-subtitle">Attendance Sheet</p>
    </div>

    @if ($applications->isEmpty())

        <div class="empty-row" style="margin-top: 24px;">
            No paid applicants found for this exam.
        </div>

    @else

        <table class="report-table attendance-table">

            <thead>
            <tr>
                <th class="sl-col">SL</th>
                <th class="app-col">Application ID</th>
                <th class="name-col">Name</th>
                <th class="photo-col">Photo</th>
                <th class="sign-col">Signature</th>
            </tr>
            </thead>

            <tbody>
            @foreach ($applications as $index => $application)

                <tr>

                    <td class="sl-col">
                        {{ $index + 1 }}
                    </td>

                    <td class="app-col">
                        {{ $application->application_id ?? $application->ulid }}
                    </td>

                    <td class="name-col">
                        {{ $application->applicant_name }}
                    </td>

                    <td class="photo-col">
                        <div class="att-photo-box">
                            @if($application->photo_data_uri)
                                <img src="{{ $application->photo_data_uri }}" alt="Photo">
                            @else
                                Photo
                            @endif
                        </div>
                    </td>

                    <td class="sign-col">
                        <div class="att-sign-blank"></div>
                    </td>

                </tr>

            @endforeach
            </tbody>

        </table>

    @endif

@endsection
