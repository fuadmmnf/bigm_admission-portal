@extends('reports.layouts.report')

@section('title', 'Gender Wise Applicant Report')
@section('report-subtitle', 'Gender Wise Report' . (isset($genderFilter) && $genderFilter ? ' – ' . $genderFilter : ' – All Genders'))

@section('content')
<div class="report-meta">
    <span><span class="label">Exam:</span> {{ $exam->name }}</span>
    <span><span class="label">Gender Filter:</span> {{ $genderFilter ?? 'All' }}</span>
    <span><span class="label">Total Applicants:</span><span class="summary-badge">{{ $applications->count() }}</span></span>
</div>

<table class="report-table">
    <thead>
        <tr>
            <th class="col-sl">SL</th>
            <th class="col-photo">Photo / App. ID</th>
            <th>Applicant Name</th>
            <th>Gender</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($applications as $index => $application)
            <tr>
                <td class="col-sl">{{ $index + 1 }}</td>
                <td class="col-photo photo-with-id">
                    @if($application->photo_data_uri)
                        <img src="{{ $application->photo_data_uri }}" alt="Photo" class="report-photo">
                    @else
                        <div class="report-photo-placeholder">N/A</div>
                    @endif
                    <div class="photo-app-id">{{ $application->application_id ?? $application->ulid }}</div>
                </td>
                <td>{{ $application->applicant_name }}</td>
                <td>{{ ucfirst($application->gender ?? data_get($application->additional_info, 'personal.gender', 'N/A')) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="muted" style="text-align:center;">No paid applicants found for the selected gender.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
