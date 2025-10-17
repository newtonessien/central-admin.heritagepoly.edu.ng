<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Fee Report</title>
 <style>
    @page {
        margin: 80px 30px 80px 30px; /* Balanced top/bottom margins */
    }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        color: #000;
        margin-top: 90px; /* aligns content just below header */
    }


    header {
        position: fixed;
        top: -45px;
        left: 0;
        right: 0;
        /* height: 90px; */
        text-align: center;
        border-bottom: 1px solid #ccc;
        padding-bottom: 6px;
    }

    header img {
        display: block;
        margin: 0 auto 4px auto; /* centers logo with small gap below */
    }

    header h2 {
        margin: 0;
        font-size: 14px;
        font-weight: bold;
    }

    header p {
        margin: 2px 0 0 0;
        font-size: 11px;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 0;
    }

    th, td {
        border: 1px solid #000;
        padding: 5px;
        text-align: left;
        font-size: 11px;
    }

    th {
        background: #f5f5f5;
        font-weight: bold;
    }

    footer {
        position: fixed;
        bottom: -50px;
        left: 20;
        right: 20;
        height: 40px;
        text-align: right;
        font-size: 11px;
        border-top: 1px solid #ccc;
        padding-top: 5px;
    }


</style>
</head>
<body>
    <header>
         <img src="{{ public_path('logo/app.jpg') }}" alt="Logo" width="55" height="55">
         <h2 style="margin:0; text-transform: uppercase;">Heritage Polytechnic</h2>
         <h2 style="text-transform: uppercase;">SCHOOL FEE PAYMENT REPORT</h2>
         <p>
             @if(!empty($filters['start_date']) && !empty($filters['end_date']))
                 From {{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }}
                 to {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}
             @endif
               {{-- 👇 Add Faculty display here --}}
             @if(!empty($filters['faculty_id']) && isset($reports[0]['faculty']) && $reports[0]['faculty'])
                 <span> — Faculty of {{ $reports[0]['faculty'] }}</span>
                 @else
                 <span> — All Faculties</span>
             @endif
                @if(isset($reports[0]['program_type']) && $reports[0]['program_type'])
                 <span> ({{ $reports[0]['program_type'] }})</span>
             @endif
         </p>
     </header>



    <main>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>REG NO</th>
                    <th>FULLNAME</th>
                    <th>FACULTY</th>
                    <th>DEPARTMENT</th>
                    <th style="text-align: right;">AMOUNT (₦)</th>
                    <th style="text-align: center;">TRANSDATE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $index => $r)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $r['regno'] ?? '' }}</td>
                        <td>{{ $r['fullname'] ?? '' }}</td>
                        <td>{{ $r['faculty'] ?? '' }}</td>
                        <td>{{ $r['department'] ?? '' }}</td>
                        <td style="text-align: right;">
                            {{ number_format($r['amount'] ?? 0, 2) }}
                        </td>
                        <td style="text-align: center;">
                            {{ \Carbon\Carbon::parse($r['trans_date'] ?? '')->format('d M Y') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- <div class="summary">
            Total Records: {{ number_format(count($reports)) }}
            <span>
                Total Amount: ₦{{ number_format(collect($reports)->sum('amount'), 2) }}
            </span>
        </div> --}}
    </main>


</body>
</html>
