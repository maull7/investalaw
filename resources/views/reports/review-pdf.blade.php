<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Compliance Review Report — {{ $document->title }}</title>
    <style>
        * { box-sizing: border-box; }
        @page { margin: 24mm 18mm; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #071833;
            line-height: 1.55;
            margin: 0;
        }
        .header {
            border-bottom: 2px solid #c99a3e;
            padding-bottom: 14px;
            margin-bottom: 22px;
        }
        .brand {
            display: inline-block;
            vertical-align: middle;
        }
        .brand .mark {
            display: inline-block;
            width: 28px;
            height: 28px;
            background: #c99a3e;
            color: #071b3a;
            text-align: center;
            font-weight: 800;
            font-size: 16px;
            line-height: 28px;
            border-radius: 6px;
            vertical-align: middle;
            margin-right: 8px;
        }
        .brand .name {
            display: inline-block;
            font-size: 14px;
            font-weight: 700;
            color: #071b3a;
            vertical-align: middle;
        }
        .brand .tag {
            display: block;
            font-size: 8px;
            font-weight: 600;
            color: #c99a3e;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-left: 36px;
            margin-top: -2px;
        }
        .doc-meta {
            float: right;
            text-align: right;
            font-size: 9.5px;
            color: #667085;
        }
        .doc-meta strong {
            color: #071833;
        }
        .clearfix { clear: both; }

        .eyebrow {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #c99a3e;
            margin: 0 0 6px;
        }
        h1.title {
            font-size: 22px;
            font-weight: 700;
            color: #071833;
            margin: 0 0 6px;
            line-height: 1.25;
        }
        .subtitle {
            color: #667085;
            font-size: 11px;
            margin: 0 0 24px;
        }

        h2 {
            font-size: 13px;
            font-weight: 700;
            color: #071833;
            margin: 26px 0 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e7eaf0;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 0;
        }
        .meta-table td {
            padding: 9px 12px;
            border: 1px solid #e7eaf0;
            font-size: 10.5px;
            vertical-align: top;
        }
        .meta-table td.label {
            width: 30%;
            font-weight: 700;
            background: #f6f8fb;
            color: #071833;
        }

        .summary-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin: 6px -8px 0;
        }
        .summary-grid td {
            border: 1px solid #e7eaf0;
            border-radius: 12px;
            padding: 14px 12px;
            text-align: center;
            background: #ffffff;
            width: 20%;
        }
        .summary-grid .number {
            font-size: 22px;
            font-weight: 700;
            color: #071833;
            display: block;
        }
        .summary-grid .label {
            font-size: 9px;
            color: #667085;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .compliant { color: #16a34a !important; }
        .partial   { color: #ca8a04 !important; }
        .non-compliant { color: #dc2626 !important; }
        .gold { color: #c99a3e !important; }

        .rate-bar {
            margin-top: 10px;
            height: 9px;
            background: #f6f8fb;
            border-radius: 999px;
            overflow: hidden;
            border: 1px solid #e7eaf0;
        }
        .rate-bar > div {
            height: 100%;
            background: #c99a3e;
        }

        table.findings {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.findings th, table.findings td {
            padding: 9px 10px;
            border: 1px solid #e7eaf0;
            text-align: left;
            font-size: 10px;
            vertical-align: top;
        }
        table.findings th {
            background: #071b3a;
            color: #ffffff;
            font-weight: 700;
            letter-spacing: 0.5px;
            font-size: 9.5px;
            text-transform: uppercase;
        }
        table.findings tr:nth-child(even) td {
            background: #f6f8fb;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-green  { background: #dcfce7; color: #166534; }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .badge-red    { background: #fee2e2; color: #991b1b; }

        .narrative {
            background: #f6f8fb;
            border: 1px solid #e7eaf0;
            border-left: 3px solid #c99a3e;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 11px;
            margin-top: 6px;
        }

        .footer-note {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px solid #e7eaf0;
            color: #667085;
            font-size: 9px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">
            <span class="mark">I</span>
            <span class="name">InvestaLaw</span>
            <span class="tag">Legal · Strategic</span>
        </div>
        <div class="doc-meta">
            <div><strong>Compliance Review Report</strong></div>
            <div>Generated {{ now()->format('d F Y · H:i') }}</div>
        </div>
        <div class="clearfix"></div>
    </div>

    <p class="eyebrow">Compliance Review Report</p>
    <h1 class="title">{{ $document->title }}</h1>
    <p class="subtitle">Institutional-grade compliance assessment summary.</p>

    <h2>Document Information</h2>
    <table class="meta-table">
        <tr><td class="label">Document Title</td><td>{{ $document->title }}</td></tr>
        <tr><td class="label">Reviewer</td><td>{{ $reviewer->name }}</td></tr>
        <tr><td class="label">Review Date</td><td>{{ $review->created_at->format('F d, Y') }}</td></tr>
        <tr><td class="label">Document Status</td><td>{{ $document->status->label() }}</td></tr>
    </table>

    <h2>Compliance Summary</h2>
    <table class="summary-grid">
        <tr>
            <td>
                <span class="number">{{ $summary['total_regulations'] }}</span>
                <span class="label">Total Regulasi</span>
            </td>
            <td>
                <span class="number compliant">{{ $summary['compliant'] }}</span>
                <span class="label">Compliant</span>
            </td>
            <td>
                <span class="number partial">{{ $summary['partially_compliant'] }}</span>
                <span class="label">Partial</span>
            </td>
            <td>
                <span class="number non-compliant">{{ $summary['non_compliant'] }}</span>
                <span class="label">Non-Compliant</span>
            </td>
            <td>
                <span class="number gold">{{ $summary['compliance_rate'] }}%</span>
                <span class="label">Compliance Rate</span>
            </td>
        </tr>
    </table>
    <div class="rate-bar"><div style="width: {{ $summary['compliance_rate'] }}%"></div></div>

    @if($review->summary)
        <h2>Review Summary</h2>
        <div class="narrative">{{ $review->summary }}</div>
    @endif

    @if($review->notes)
        <h2>Internal Notes</h2>
        <div class="narrative">{{ $review->notes }}</div>
    @endif

    <h2>Detailed Findings</h2>
    @if($findings->isEmpty())
        <p style="color: #667085;">No findings recorded.</p>
    @else
        <table class="findings">
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 26%;">Regulation</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 29%;">Findings</th>
                    <th style="width: 29%;">Recommendations</th>
                </tr>
            </thead>
            <tbody>
                @foreach($findings as $index => $finding)
                    <tr>
                        <td style="text-align: center; font-weight: 700;">{{ $index + 1 }}</td>
                        <td>
                            @if($finding->regulation)
                                <strong>{{ $finding->regulation->regulation_number }}</strong>
                                <br><span style="color: #667085; font-size: 9.5px;">{{ $finding->regulation->title }}</span>
                            @elseif($finding->category)
                                <strong>{{ $finding->category->name }}</strong>
                                @if($finding->category->description)
                                    <br><span style="color: #667085; font-size: 9.5px;">{{ $finding->category->description }}</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $finding->compliance_status->color() }}">{{ $finding->compliance_status->label() }}</span>
                        </td>
                        <td>{{ $finding->findings ?? '—' }}</td>
                        <td>{{ $finding->recommendations ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="footer-note">
        &copy; {{ date('Y') }} InvestaLaw · Legal · Strategic · Trusted · This report is confidential and intended solely for the addressee.
    </p>
</body>
</html>
