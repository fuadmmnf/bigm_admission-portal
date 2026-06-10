@extends('reports.layouts.report')

@section('title', 'Attendance Sheet')
@section('report-subtitle', '')

@section('extra-styles')
    <style>

        .report-header {
            text-align: center;
            margin-bottom: 5px;
            padding-bottom: 2px;
        }

        .report-header p {
            margin: 0;
            line-height: 1.1;
        }

        .report-header .title {
            font-size: 15px;
            font-weight: bold;
        }

        .report-header .subtitle {
            font-size: 10px;
            margin-top: 2px;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        .attendance-table th,
        .attendance-table td {
            border: 1px solid #d1d5db;
            padding: 4pt;
            font-size: 9pt;
        }

        .att-photo-box {
            width: 40pt;
            height: 40pt;
            border: 1px solid #111827;
            text-align: center;
            overflow: hidden;
            margin: auto;
        }

        .att-photo-box img {
            width: 40pt;
            height: 40pt;
            object-fit: cover;
        }

        /* ================================
           INVIGILATOR (BOTTOM FIXED ABOVE FOOTER)
           ================================ */

        .invigilator-wrapper {
            position: fixed;

            left: 14mm;
            right: 14mm;

            /* 🔥 KEY CHANGE: push ABOVE footer area */
            bottom: 32mm; /* footer is ~22mm, so we lift above it */

            font-size: 9pt;
        }

        .inv-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .inv-table td {
            width: 50%;
            vertical-align: top;
        }

        .inv-col {
            width: 100%;
        }

        .inv-block {
            margin-bottom: 10px;
            text-align: center;
        }

        .inv-box {
            width: 150pt;
            height: 22pt;
            border: 1px solid #111827;
            margin: 0 auto;
        }

        .inv-label {
            font-size: 8pt;
            margin-top: 2pt;
            text-align: center;
            display: block;
        }

        /* LEFT / RIGHT alignment */
        .inv-left { text-align: left; }
        .inv-right { text-align: right; }

    </style>
@endsection

@section('content')

    <div class="report-header">
        <p class="title">{{ $exam->name }}</p>
        <p class="subtitle">Attendance Sheet</p>
    </div>

    @if($applications->isEmpty())

        <p style="margin-top:20px;">No applicants found.</p>

    @else

        <table class="attendance-table">
            <thead>
            <tr>
                <th>SL</th>
                <th>Application ID</th>
                <th>Name</th>
                <th>Photo</th>
                <th>Signature</th>
            </tr>
            </thead>

            <tbody>
            @foreach($applications as $i => $app)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $app->application_id ?? $app->ulid }}</td>
                    <td>{{ $app->applicant_name }}</td>
                    <td>
                        <div class="att-photo-box">
                            @if($app->photo_data_uri)
                                <img src="{{ $app->photo_data_uri }}">
                            @endif
                        </div>
                    </td>
                    <td></td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{-- INVIGILATOR (NOW ABOVE FOOTER) --}}
        <div class="invigilator-wrapper">

            <table class="inv-table">
                <tr>

                    {{-- LEFT --}}
                    <td class="inv-left">

                        <div class="inv-col">

                            <div class="inv-block">
                                <div class="inv-box"></div>
                                <div class="inv-label">Invigilator Name</div>
                            </div>

                            <div class="inv-block">
                                <div class="inv-box"></div>
                                <div class="inv-label">Invigilator Signature</div>
                            </div>

                        </div>

                    </td>

                    {{-- RIGHT --}}
                    <td class="inv-right">

                        <div class="inv-col">

                            <div class="inv-block">
                                <div class="inv-box"></div>
                                <div class="inv-label">Invigilator Name</div>
                            </div>

                            <div class="inv-block">
                                <div class="inv-box"></div>
                                <div class="inv-label">Invigilator Signature</div>
                            </div>

                        </div>

                    </td>

                </tr>
            </table>

        </div>

    @endif

@endsection
