<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Report') – {{ $exam->name ?? '' }}</title>
    <style>
        @page {
            margin: 28mm 14mm 22mm 14mm;
            size: A4 portrait;
        }

        * { box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }

        /* ── Fixed Header ──────────────────────────────────── */
        .pdf-header {
            position: fixed;
            top: -28mm;
            left: -14mm;
            right: -14mm;
            background: #1e3a5f;
            color: #ffffff;
            padding: 6pt 14mm 5pt;
        }
        .pdf-header-inner {
            display: table;
            width: 100%;
        }
        .pdf-header-logo {
            display: table-cell;
            vertical-align: middle;
            width: 78pt;
        }
        .pdf-header-logo img {
            width: 72pt;
            height: auto;
            display: block;
        }
        .pdf-header-text {
            display: table-cell;
            vertical-align: middle;
            padding-left: 10pt;
        }
        .pdf-header-org {
            font-size: 9.5pt;
            font-weight: bold;
            letter-spacing: 0.3pt;
            margin: 0;
        }
        .pdf-header-report-title {
            font-size: 8pt;
            margin: 1pt 0 0;
            opacity: 0.88;
        }
        .pdf-header-meta {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            font-size: 7.5pt;
            opacity: 0.82;
            white-space: nowrap;
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

        /* ── Shared table styles ────────────────────────────── */
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }
        table.report-table th,
        table.report-table td {
            border: 1px solid #d1d5db;
            padding: 5pt 6pt;
            vertical-align: middle;
        }
        table.report-table th {
            background: #1e3a5f;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3pt;
        }
        table.report-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        .col-sl { width: 26pt; text-align: center; }
        .col-photo { width: 44pt; text-align: center; }
        .col-marks { width: 38pt; text-align: center; }
        .muted { color: #6b7280; }
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
    </style>
    @yield('extra-styles')
</head>
<body>
    @php
        $logoPath = public_path('images/logo.png');
        $logoDataUri = null;
        if (is_file($logoPath) && is_readable($logoPath)) {
            $logoDataUri = 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath));
        }
    @endphp

    {{-- Fixed Header --}}
    <div class="pdf-header">
        <div class="pdf-header-inner">
            <div class="pdf-header-logo">
                @if($logoDataUri)
                    <img src="{{ $logoDataUri }}" alt="BIGM Logo">
                @else
                    <span style="font-size:11pt;font-weight:bold;letter-spacing:1pt;">BIGM</span>
                @endif
            </div>
            <div class="pdf-header-text">
                <p class="pdf-header-org">Bangladesh Institute of Governance and Management</p>
                <p class="pdf-header-report-title">@yield('report-subtitle', 'Exam Report') &mdash; {{ $exam->name ?? 'N/A' }}</p>
            </div>
            <div class="pdf-header-meta">
                Generated: {{ isset($generatedAt) ? $generatedAt->format('d M Y, h:i A') : now()->format('d M Y, h:i A') }}
            </div>
        </div>
    </div>

    {{-- Fixed Footer --}}
    <div class="pdf-footer">
        <div class="pdf-footer-inner">
            <div class="pdf-footer-left">BIGM Admission Portal &mdash; Confidential</div>
            <div class="pdf-footer-center">@yield('footer-center', '')</div>
            <div class="pdf-footer-right"><span class="page-number"></span></div>
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

