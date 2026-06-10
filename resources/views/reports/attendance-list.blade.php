@extends('reports.layouts.report')

@section('title', 'Attendance Sheet')
@section('report-subtitle', '')

@section('extra-styles')
    <style>

        .report-header {
            text-align: center;
            margin-bottom: 6px;
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
            font-size: 9px;
        }

        .attendance-table th,
        .attendance-table td {
            border: 1px solid #d1d5db;
            padding: 4px;
        }

        .att-photo-box {
            width: 40pt;
            height: 40pt;
            border: 1px solid #111827;
            text-align: center;
            margin: auto;
        }

        .att-photo-box img {
            width: 40pt;
            height: 40pt;
            object-fit: cover;
        }

    </style>
@endsection

@section('content')

    <div class="report-header">
        <div class="title">{{ $exam->name }}</div>
        <div class="subtitle">Attendance Sheet</div>
    </div>

    @if($applications->isEmpty())

        <p style="margin-top:20px;text-align:center;">No applicants found.</p>

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

    @endif

    {{-- INVIGILATOR (ONLY LAST PAGE, FIXED POSITION) --}}
    @if(!$applications->isEmpty())

        <div class="invigilator-wrapper">

            <table class="inv-table">
                <tr>
                    <td>
                        <div class="inv-block">
                            <div class="inv-box"></div>
                            <div class="inv-label">Invigilator Name</div>
                        </div>

                        <div style="margin-top:8px;" class="inv-block">
                            <div class="inv-box"></div>
                            <div class="inv-label">Invigilator Signature</div>
                        </div>
                    </td>

                    <td style="text-align:right;">
                        <div class="inv-block right">
                            <div class="inv-box"></div>
                            <div class="inv-label">Invigilator Name</div>
                        </div>

                        <div style="margin-top:8px;" class="inv-block right">
                            <div class="inv-box"></div>
                            <div class="inv-label">Invigilator Signature</div>
                        </div>
                    </td>
                </tr>
            </table>

        </div>

    @endif

@endsection
