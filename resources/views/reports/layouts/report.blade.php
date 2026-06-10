<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Report') – {{ $exam->name ?? '' }}</title>

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

    <style>
        @page {
            margin: 40mm 14mm 35mm 14mm;
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
        }

        /* HEADER */
        .pdf-header {
            position: fixed;
            top: -32mm;
            left: -14mm;
            right: -14mm;
            border-bottom: 1px solid #d1d5db;
            padding: 6pt 14mm 4pt;
            text-align: center;
        }

        .pdf-header-logo {
            width: 90px;
        }

        /* FOOTER */
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

        .pdf-footer-right {
            display: table-cell;
            text-align: right;
        }

        /* PAGE NUMBER FIX */
        .page-number {
            position: fixed;
            bottom: -12mm;
            right: 0;
            font-size: 8px;
            color: #6b7280;
        }

        /* TABLE */
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        table.report-table th,
        table.report-table td {
            border: 1px solid #d1d5db;
            padding: 5px;
        }

        table.report-table th {
            background: #1e3a5f;
            color: #fff;
        }

        /* INVIGILATOR SECTION (IMPORTANT FIX) */
        .invigilator-wrapper {
            position: fixed;
            bottom: 10mm;   /* 👈 sits just above footer */
            left: 14mm;
            right: 14mm;
            font-size: 9px;
        }

        .inv-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inv-table td {
            width: 50%;
            vertical-align: bottom;
        }

        .inv-block {
            display: inline-block;
            text-align: center;
        }

        .inv-block.right {
            float: right;
            text-align: center;
        }

        .inv-box {
            width: 160px;
            height: 22px;
            border: 1px solid #111827;
        }

        .inv-label {
            font-size: 8px;
            margin-top: 2px;
            text-align: center;
        }

    </style>

    @yield('extra-styles')
</head>

<body>

<div class="pdf-header">
    <img src="{{ public_path('images/logo.png') }}" class="pdf-header-logo">
    <div>Bangladesh Institute of Governance and Management</div>
</div>

<div class="pdf-footer">
    <div class="pdf-footer-inner">
        <div class="pdf-footer-right">
            @yield('footer-right')
        </div>
    </div>
</div>

{{-- PAGE NUMBER (RESTORED) --}}
<script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->getFont("DejaVu Sans", "normal");
        $size = 8;
        $pdf->page_text($pdf->get_width() - 110, $pdf->get_height() - 16,
            "Page {PAGE_NUM} of {PAGE_COUNT}",
            $font, $size, array(0.4,0.4,0.4));
    }
</script>

<div class="report-content">
    @yield('content')
</div>

</body>
</html>
