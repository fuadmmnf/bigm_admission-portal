<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Report') – {{ $exam->name ?? '' }}</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <style>
        @page {
            margin: 28mm 14mm 22mm 14mm;
            size: {{ $pageOrientation ?? 'portrait' }};
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 0;
            padding: 0;
            line-height: 1.4;
            background: #ffffff;
        }

        .pdf-watermark {
            position: fixed;
            top: 38%;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 88pt;
            font-weight: bold;
            letter-spacing: 4pt;
            color: rgba(17, 24, 39, 0.06);
            transform: rotate(-26deg);
            z-index: -1;
            white-space: nowrap;
        }

        /* ── Fixed Header ──────────────────────────────────── */
        .pdf-header {
            position: fixed;
            top: -28mm;
            left: -14mm;
            right: -14mm;
            color: #111827;
            padding: 6pt 14mm 4pt;
            border-bottom: 1px solid #d1d5db;
        }

        .pdf-header-inner {
            width: 100%;
            text-align: center;
        }

        .pdf-header-logo {
            width: 60px;
            height: auto;
            display: inline-block;
            margin-bottom: 2pt;
        }

        .pdf-header-short {
            font-size: 9.5pt;
            font-weight: bold;
            letter-spacing: 0.4pt;
            margin: 0;
        }

        .pdf-header-org {
            font-size: 10pt;
            font-weight: bold;
            letter-spacing: 0.2pt;
            margin: 0;
        }

        .pdf-header-report-title {
            font-size: 9pt;
            margin: 2pt 0 0;
        }

        /* ── Fixed Footer ──────────────────────────────────── */
        .pdf-footer {
            position: fixed;
            bottom: -22mm;
            left: -14mm;
            right: -14mm;
            border-top: 1px solid #c7d2e0;
            padding: 4pt 14mm 0;
            font-size: 8pt;
            color: #6b7280;
        }

        .pdf-footer-inner {
            display: table;
            width: 100%;
        }

        .pdf-footer-left {
            display: table-cell;
            vertical-align: middle;
            text-align: left;
        }

        .pdf-footer-center {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }

        .pdf-footer-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        /* ── Content area ──────────────────────────────────── */
        .report-content {
            /* top/bottom margin is handled by @page */
        }

        /* ── Shared table styles ────────────────────────── */
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
            table-layout: fixed;
        }

        /* Allow flexible layout for reports that override with table-layout: auto */
        table.report-table.auto-layout {
            table-layout: auto !important;
        }

        table.section-table.auto-layout {
            table-layout: auto !important;
        }

        table.report-table th,
        table.report-table td {
            border: 1px solid #d1d5db;
            padding: 5pt;
            vertical-align: top;
            word-break: break-word;
        }

        table.report-table th {
            background: #1e3a5f;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3pt;
            vertical-align: middle;
        }

        table.report-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .col-sl {
            width: 26pt;
            text-align: center;
        }

        .col-photo {
            width: 44pt;
            text-align: center;
        }

        .col-marks {
            width: 38pt;
            text-align: center;
        }

        .muted {
            color: #6b7280;
        }

        .report-photo {
            width: 34pt;
            height: 40pt;
            border: 1px solid #d1d5db;
            object-fit: cover;
            display: block;
            margin: 0 auto;
            background: #f9fafb;
        }

        .report-photo-placeholder {
            width: 34pt;
            height: 40pt;
            border: 1px solid #d1d5db;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 7px;
            color: #9ca3af;
            background: #f9fafb;
        }

        .photo-with-id {
            text-align: center;
        }

        .photo-app-id {
            margin-top: 2pt;
            font-size: 7pt;
            line-height: 1.2;
            font-weight: bold;
            color: #1f2937;
        }

        /* ── Section subheadings ────────────────────────────── */
        .section-heading {
            margin-top: 16px;
            margin-bottom: 4px;
            font-size: 11px;
            font-weight: bold;
            color: #1e3a5f;
            border-bottom: 1px solid #c7d2e0;
            padding-bottom: 3px;
        }

        .report-meta {
            font-size: 10px;
            margin-bottom: 8px;
            color: #374151;
        }

        .report-meta span {
            margin-right: 16px;
        }

        .report-meta .label {
            font-weight: bold;
            color: #111827;
        }

        .summary-badge {
            display: inline-block;
            background: #ede9fe;
            color: #4f46e5;
            border-radius: 3pt;
            padding: 1pt 6pt;
            font-size: 9px;
            font-weight: bold;
            margin-left: 4px;
        }

        .empty-row {
            text-align: center;
            color: #6b7280;
        }

        .report-note {
            font-size: 9px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .small-cell {
            font-size: 9px;
            color: #111827;
        }

        .section-table {
            margin-bottom: 14px;
        }

        /* ── Report Header (reusable across all reports) ────── */
        .report-header {
            text-align: center;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .report-header-logo-wrap {
            margin-bottom: 4px;
        }

        .report-header-logo {
            width: 54px;
            height: auto;
            display: inline-block;
        }

        .report-header-title {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 0.15px;
        }

        .report-header-subtitle {
            margin-top: 2px;
            font-size: 9.2px;
            color: #374151;
        }

        .report-title-chip {
            margin-top: 7px;
            display: inline-block;
            border: 1px solid #111827;
            padding: 2px 12px;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
    </style>
    @yield('extra-styles')
</head>
<body>
<div class="pdf-watermark">BIGM</div>

{{-- Fixed Header --}}
<div class="pdf-header">
    <div class="pdf-header-inner">
        <img src="{{ public_path('images/logo.png') }}" alt="BIGM Logo" class="pdf-header-logo">
        <p class="pdf-header-org">Bangladesh Institute of Governance and Management (BIGM)</p>
        <p class="pdf-header-report-title">@yield('report-subtitle', 'Exam Report')</p>
    </div>
</div>

{{-- Fixed Footer --}}
<div class="pdf-footer">
    <div class="pdf-footer-inner">
        <div class="pdf-footer-left">@yield('footer-left', 'BIGM Admission Portal &mdash; Confidential')</div>
        <div class="pdf-footer-center">@yield('footer-center', '')</div>
        <div class="pdf-footer-right">@yield('footer-right', '')</div>
    </div>
</div>

{{-- Page numbers via PHP (dompdf) --}}
<script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->getFont("DejaVu Sans", "normal");
        $size = 7;
        $color = array(107/255, 114/255, 128/255);
        $width  = $pdf->get_width();
        $height = $pdf->get_height();
        // x: right-aligned ~14mm from right edge; y: near bottom of page footer area
        $x = $width - 112;
        $y = $height - 14;
        $pdf->page_text($x, $y, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, $size, $color);
    }
</script>

{{-- Main Content --}}
<div class="report-content">
    @yield('content')
</div>

</body>
</html>

