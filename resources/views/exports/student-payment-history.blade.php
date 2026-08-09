<!DOCTYPE html>
<html>

<head>
<meta charset="utf-8">

<title>Student Payment History</title>

<style>

@page{
margin:25px;
}

body{
font-family: DejaVu Sans, sans-serif;
font-size:11px;
color:#222;
}

table{
width:100%;
border-collapse:collapse;
}

th,
td{
padding:6px;
border:1px solid #d9d9d9;
}

th{
background:#f2f2f2;
font-weight:bold;
text-align:left;
}

.no-border td{
border:none;
}

.header{
text-align:center;
margin-bottom:18px;
}

.logo{
width:80px;
height:80px;
margin-bottom:8px;
}

.title{
font-size:18px;
font-weight:bold;
}

.subtitle{
font-size:13px;
margin-top:4px;
font-weight:bold;
color: #900;
}

.student-photo{
width:130px;
height:130px;
border:1px solid #999;
object-fit:cover;
}

.placeholder{

width:110px;
height:130px;
border:1px solid #999;
text-align:center;
line-height:130px;
color:#888;
font-size:10px;

}

.summary{

width:40%;
margin-left:auto;
margin-top:15px;

}

.summary td{

font-weight:bold;

}

.text-center{
text-align:center;
}

.text-right{
text-align:right;
}

.mt-20{
margin-top:20px;
}

.footer{

position:fixed;

bottom:-10px;

left:0;

right:0;

text-align:center;

color:#777;

font-size:10px;

}

.signatory{
    width:100%;
    margin-top:15px;
    border-collapse:collapse;
}

.signatory td{
    width:45%;
    border:none;
    text-align:center;
    vertical-align:top;
    padding-top:40px;
    font-size:11px;
}

.signatory td.spacer{
    width:10%;
}

.signatory .line{
    width:80%;
    margin:0 auto 8px auto;
    border-top:1px solid #222;
}

.signatory .title{
    font-weight:bold;
    margin-top:3px;
}

.signatory .designation{
    font-size:10px;
    color:#555;
    margin-top:2px;
}

</style>

</head>

<body>

{{-- ===========================================
HEADER
=========================================== --}}

<div class="header">

@php
$logo = public_path('logo/app.jpg');
@endphp

@if(file_exists($logo))

<img
src="{{ $logo }}"
class="logo">

@endif

<div class="title">

HERITAGE POLYTECHNIC

</div>

<div class="subtitle">

STUDENT PAYMENT HISTORY

</div>
<hr/>

</div>


{{-- ===========================================
STUDENT INFORMATION
=========================================== --}}

<table class="no-border">

<tr>

<td width="82%">

<table>

<tr>

<th width="25%">Full Name</th>

<td>{{ $student['name'] }} ({{ $student['user']['sex'] }})</td>

</tr>

<tr>
<th>Registration No.</th>
<td>{{ $student['matric_no'] ?? $student['regno'] }}</td>
</tr>

<tr>
<th>Phone Number</th>
<td>{{ $student['user']['phone_no'] ?? 'N/A' }}</td>
</tr>


<tr>

<th>Programme</th>

<td>{{ $student['program'] }}</td>

</tr>

<tr>

<th>Programme Type</th>

<td>{{ $student['program_type'] }}</td>

</tr>

<tr>

<th>Faculty/Dept</th>

<td>{{ $student['faculty'] }} / {{ $student['department'] }}</td>

</tr>

{{-- <tr>

<th>Department</th>

<td>{{ $student['department'] }}</td>

</tr> --}}

</table>

</td>

<td width="18%" class="text-center">


@if(!empty($student['user']['photo']))

<img
src="{{ file_url($student['user']['photo'], 'students') }}"
class="student-photo">

@else

<div class="placeholder">

NO PHOTO

</div>

@endif

</td>

</tr>

</table>


{{-- ===========================================
SUMMARY
=========================================== --}}

<table class="summary">

<tr>

<td>Total Transactions</td>

<td class="text-right">

{{ number_format($summary['transactions'] ?? 0) }}

</td>

</tr>

<tr>

<td>Total Amount Paid</td>

<td class="text-right">

₦{{ number_format($summary['total_amount'] ?? 0,2) }}

</td>

</tr>

</table>


{{-- ===========================================
PAYMENTS
=========================================== --}}

<table class="mt-20">

<thead>

<tr>

<th width="5%">#</th>

<th width="18%">Tranx Ref#</th>

<th width="20%">Payment Type</th>

<th width="14%">Session</th>

<th width="10%">Semester</th>

<th width="8%">Level</th>

<th width="11%" class="text-right">Amount</th>

<th width="14%">Date</th>

</tr>

</thead>

<tbody>

@forelse($payments as $payment)

<tr>

<td class="text-center">

{{ $loop->iteration }}

</td>

<td>

{{ $payment['transaction_reference'] }}

</td>

<td>

{{ $payment['payment_type'] }}

</td>

<td>

{{ $payment['session'] }}

</td>

<td>

{{ semester_name($payment['fee_period_id']) }}

</td>

<td class="text-center">

{{ is_numeric($payment['level_id'])
? $payment['level_id'].'00'
: '-' }}

</td>

<td class="text-right">

₦{{ number_format($payment['amount'],2) }}

</td>

<td>

{{ \Carbon\Carbon::parse($payment['payment_date'])->format('d M Y') }}

</td>

</tr>

@empty

<tr>

<td colspan="8" class="text-center">

No payment history available.

</td>

</tr>

@endforelse

</tbody>

</table>


@include('exports.partials.signatory')


{{-- ===========================================
FOOTER
=========================================== --}}

<div class="footer">

Generated on {{ now()->format('d F Y \a\t h:i A') }}

</div>

<script type="text/php">
if (isset($pdf)) {
$pdf->page_text(
520,
820,
"Page {PAGE_NUM} of {PAGE_COUNT}",
null,
9,
array(0,0,0)
);
}
</script>

</body>
</html>
