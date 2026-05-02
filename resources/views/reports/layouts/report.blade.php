<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Report') – {{ $exam->name ?? '' }}</title>
    <style>
        @page {
            margin: 28mm 14mm 22mm 14mm;
            size: @yield('paper-size', 'A4') @yield('paper-orientation', 'portrait');
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
            top: -22mm;
            left: 0;
            right: 0;
            background: #1e3a5f;
            color: #ffffff;
            padding: 6pt 12pt 5pt;
        }
        .pdf-header-inner {
            display: table;
            width: 100%;
        }
        .pdf-header-logo {
            display: table-cell;
            vertical-align: middle;
            width: 60pt;
        }
        .pdf-header-logo .logo-badge {
            display: inline-block;
            border: 1.5pt solid #84cc16;
            border-radius: 20pt;
            padding: 2pt 10pt;
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 2pt;
            color: #ffffff;
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
            bottom: -18mm;
            left: 0;
            right: 0;
            border-top: 1px solid #c7d2e0;
            padding-top: 4pt;
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
        .pdf-footer-right .page-number:before {
            content: "Page " counter(page) " of " counter(pages);
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
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3pt;
        }
        table.report-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        .col-sl { width: 26pt; text-align: center; }
        .col-appid { width: 60pt; }
        .col-marks { width: 38pt; text-align: center; }
        .muted { color: #6b7280; }

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

    {{-- Fixed Header --}}
    <div class="pdf-header">
        <div class="pdf-header-inner">
            <div class="pdf-header-logo">
                <span class="logo-badge">BIGM</span>
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

    {{-- Main Content --}}
    <div class="report-content">
        @yield('content')
    </div>

</body>
</html>

