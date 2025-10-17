<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 130px 40px 70px 40px; /* enough space for header/footer */
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }


        header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
        }

        footer {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 40px;
            font-size: 11px;
            color: #555;
        }

        .header-meta { font-size: 13px; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        thead {
            display: table-header-group; /* ✅ repeat on each page */
        }

        tfoot {
            display: table-row-group;
        }

        tr {
            page-break-inside: avoid;
        }

        tfoot td {
            font-weight: bold;
            background-color: #fafafa;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <header>
        <div style="display:flex; align-items:center; justify-content:center; gap:10px;">
           {{-- @if(file_exists(public_path('logo/app.jpg')))
  <img src="{{ base_path('public/logo/app.jpg') }}" alt="Logo" width="55" height="55">
@else
    <div>Logo not found</div>
@endif --}}
            <div>
                <h2 style="margin:0;">Heritage Polytechnic <br/>Admissions Form Payment Report</h2>
                            <div class="header-meta">

                    @if(!empty($filters['start_date']) && !empty($filters['end_date']))
                        <span> {{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }}
                        to {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}</span>
                    @endif
                    @if(!empty($filters['faculty_id']) && isset($payments[0]['faculty']) && $payments[0]['faculty'])
                        <span> — Faculty of {{ $payments[0]['faculty'] }}</span>
                    @endif
                     @if(!empty($filters['application_type_name']))
                        <span> ({{ $filters['application_type_name'] }})</span>
                        @else
                        <span> (All Application Types)</span>
                    @endif
                </div>
            </div>
        </div>
    </header>

    {{-- BODY --}}
    <main>
        @php

            $totalAmount = collect($payments)->sum(fn($p) => $p['amount'] ?? 0);
            $totalRecords = count($payments);

        @endphp

        <table>
            <thead>
                <tr>
                    <th style="width:4%;">Sn</th>
                    <th style="width:14%;">Reg No</th>
                    <th style="width:28%;">Fullname</th>
                    <th style="width:16%;">Faculty</th>
                    <th style="width:18%;">Department</th>
                    <th style="width:10%; text-align:right;">Amount (₦)</th>
                    <th style="width:10%;">TransDate</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row['regno'] ?? '' }}</td>
                        <td>{{ $row['fullname'] ?? '' }}</td>
                        <td>{{ $row['faculty'] ?? '' }}</td>
                        <td>{{ $row['department'] ?? '' }}</td>
                        <td style="text-align:right;">{{ number_format($row['amount'] ?? 0, 2) }}</td>
                        <td>{{ \Carbon\Carbon::parse($row['trans_date'] ?? '')->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;">No records found.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="padding-left:8px;">Total Records: {{ $totalRecords }}</td>
                    <td colspan="2" style="text-align:right; padding-right:8px;">Total Amount: ₦{{ number_format($totalAmount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </main>
</body>
</html>
