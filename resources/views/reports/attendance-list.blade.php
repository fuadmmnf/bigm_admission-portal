@extends('reports.layouts.report')

@section('title', 'Attendance Sheet')
@section('report-subtitle', 'Attendance Sheet')

@section('extra-styles')
    <style>

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

        /* Small SL column */
        .attendance-table .sl-col {
            width: 30pt !important;
            text-align: center;
        }

        /* Medium App ID */
        .attendance-table .app-col {
            width: 95pt !important;
        }

        /* Photo */
        .attendance-table .photo-col {
            width: 65pt !important;
            text-align: center;
        }

        /* Signature */
        .attendance-table .sign-col {
            width: 120pt !important;
        }

        /* Name takes remaining width */
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
        <span class="att-footer-line">&nbsp;</span><br/>
        <span class="field-label">Invigilator's Name:</span>
    </div>
@endsection

@section('footer-right')
    <div class="att-footer-sig">
        <span class="sig-box"></span>
        <span class="field-label">Invigilator's Signature</span><br/>
    </div>
@endsection

@section('content')

    <div class="report-meta">
    <span>
        <span class="label">Exam:</span>
        {{ $exam->name }}
    </span>
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
                        <div class="att-sign-box"></div>

                        <div class="att-sign-label">
                            Applicant Signature
                        </div>
                    </td>

                </tr>

            @endforeach
            </tbody>

        </table>

    @endif

@endsection
