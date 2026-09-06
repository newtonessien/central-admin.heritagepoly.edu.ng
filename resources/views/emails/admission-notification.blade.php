<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="color-scheme"
        content="light"
    >

    <title>
        Notification of Admission — Heritage Polytechnic
    </title>
</head>

<body
    style="
        margin:0;
        padding:0;
        background:#f4f6f8;
        font-family:Arial, Helvetica, sans-serif;
        color:#1f2937;
    "
>

<table
    role="presentation"
    width="100%"
    cellspacing="0"
    cellpadding="0"
    border="0"
    style="background:#f4f6f8; padding:35px 15px;"
>

<tr>

<td align="center">

<table
    role="presentation"
    width="100%"
    cellspacing="0"
    cellpadding="0"
    border="0"
    style="
        max-width:680px;
        background:#ffffff;
        border:1px solid #e5e7eb;
        border-radius:8px;
        overflow:hidden;
    "
>

{{-- ========================================================= --}}
{{-- UNIVERSITY HEADER --}}
{{-- ========================================================= --}}

<tr>

<td
    style="
        padding:28px 35px;
        border-bottom:1px solid #e5e7eb;
    "
>

<table
    role="presentation"
    width="100%"
    cellspacing="0"
    cellpadding="0"
    border="0"
>

<tr>

<td>

<table
    role="presentation"
    cellspacing="0"
    cellpadding="0"
    border="0"
>

<tr>

<td
    style="
        vertical-align:middle;
        padding-right:12px;
    "
>
 <img
    src="cid:heritage-logo"
    alt="Heritage"
    width="50"
    style="
        display:block;
        width:50px;
        height:auto;
        border:0;
    "
>
</td>

<td
    style="
        vertical-align:middle;
    "
>

<div
    style="
        font-size:20px;
        font-weight:bold;
        letter-spacing:.3px;
        color:#111827;
    "
>
    HERITAGE POLYTECHNIC
</div>

<div
    style="
        margin-top:5px;
        font-size:12px;
        color:#6b7280;
        letter-spacing:.5px;
        text-transform:uppercase;
    "
>
    Admissions Unit
</div>

</td>

</tr>

</table>

</td>

<td
    align="right"
    style="
        font-size:12px;
        color:#6b7280;
        vertical-align:top;
    "
>
    {{ $academicContext['session'] ?? '' }}
</td>

</tr>

</table>

</td>

</tr>


{{-- ========================================================= --}}
{{-- TITLE --}}
{{-- ========================================================= --}}

<tr>

<td
    style="
        padding:38px 35px 20px;
        text-align:center;
    "
>

<div
    style="
        font-size:13px;
        font-weight:bold;
        letter-spacing:1.5px;
        color:#6b7280;
        text-transform:uppercase;
    "
>
    Admission Status
</div>

<h1
    style="
        margin:10px 0 0;
        font-size:28px;
        line-height:1.3;
        color:#111827;
    "
>
    Notification of Admission
</h1>

</td>

</tr>


{{-- ========================================================= --}}
{{-- CONGRATULATIONS --}}
{{-- ========================================================= --}}

<tr>

<td style="padding:0 35px 25px;">

<div
    style="
        background:#f0fdf4;
        border:1px solid #bbf7d0;
        border-radius:7px;
        padding:20px 22px;
        text-align:center;
    "
>

<div
    style="
        font-size:18px;
        font-weight:bold;
        color:#166534;
    "
>
    Congratulations!
</div>

<p
    style="
        margin:8px 0 0;
        font-size:14px;
        line-height:1.7;
        color:#365314;
    "
>
    You have been offered conditional admission
    into the Heritage Polytechnic.
</p>

</div>

</td>

</tr>


{{-- ========================================================= --}}
{{-- GREETING --}}
{{-- ========================================================= --}}

<tr>

<td style="padding:0 35px 5px;">

<p
    style="
        margin:0 0 15px;
        font-size:15px;
        line-height:1.8;
    "
>
    Dear
    <strong>
        {{ $candidate['user']['last_name'] ?? '' }},
        {{ $candidate['user']['first_name'] ?? '' }}
        {{ $candidate['user']['other_names'] ?? '' }}
    </strong>,
</p>

<p
    style="
        margin:0;
        font-size:15px;
        line-height:1.8;
        color:#374151;
    "
>
    We are pleased to inform you that following the
    admission screening process, you have been offered
    conditional admission to pursue the programme indicated
    below for the
    <strong>
        {{ $academicContext['session'] ?? '—' }}
    </strong>
    Academic Session.
</p>

</td>

</tr>


{{-- ========================================================= --}}
{{-- CANDIDATE / ACADEMIC DETAILS --}}
{{-- ========================================================= --}}

<tr>

<td style="padding:25px 35px;">

<table
    role="presentation"
    width="100%"
    cellspacing="0"
    cellpadding="0"
    border="0"
    style="
        border:1px solid #e5e7eb;
        border-radius:7px;
        overflow:hidden;
    "
>

<tr>

<td
    colspan="2"
    style="
        padding:14px 18px;
        background:#f9fafb;
        border-bottom:1px solid #e5e7eb;
        font-size:13px;
        font-weight:bold;
        color:#374151;
        text-transform:uppercase;
        letter-spacing:.5px;
    "
>
    Admission Details
</td>

</tr>


<tr>

<td
    style="
        width:42%;
        padding:13px 18px;
        font-size:13px;
        color:#6b7280;
        border-bottom:1px solid #f3f4f6;
    "
>
    Registration Number
</td>

<td
    style="
        padding:13px 18px;
        font-size:14px;
        font-weight:bold;
        color:#111827;
        border-bottom:1px solid #f3f4f6;
    "
>
    {{ $candidate['jamb_no'] ?? $candidate['regno'] ?? '—' }}
</td>

</tr>


<tr>

<td
    style="
        padding:13px 18px;
        font-size:13px;
        color:#6b7280;
        border-bottom:1px solid #f3f4f6;
    "
>
    Programme
</td>

<td
    style="
        padding:13px 18px;
        font-size:14px;
        font-weight:bold;
        color:#111827;
        border-bottom:1px solid #f3f4f6;
    "
>
    {{ $academicContext['program'] ?? '—' }}
</td>

</tr>


<tr>

<td
    style="
        padding:13px 18px;
        font-size:13px;
        color:#6b7280;
        border-bottom:1px solid #f3f4f6;
    "
>
    Department
</td>

<td
    style="
        padding:13px 18px;
        font-size:14px;
        color:#111827;
        border-bottom:1px solid #f3f4f6;
    "
>
    {{ $academicContext['department'] ?? '—' }}
</td>

</tr>


<tr>

<td
    style="
        padding:13px 18px;
        font-size:13px;
        color:#6b7280;
        border-bottom:1px solid #f3f4f6;
    "
>
    Faculty
</td>

<td
    style="
        padding:13px 18px;
        font-size:14px;
        color:#111827;
        border-bottom:1px solid #f3f4f6;
    "
>
    {{ $academicContext['faculty'] ?? '—' }}
</td>

</tr>


<tr>

<td
    style="
        padding:13px 18px;
        font-size:13px;
        color:#6b7280;
    "
>
    Academic Session
</td>

<td
    style="
        padding:13px 18px;
        font-size:14px;
        font-weight:bold;
        color:#111827;
    "
>
    {{ $academicContext['session'] ?? '—' }}
</td>

</tr>

</table>

</td>

</tr>


{{-- ========================================================= --}}
{{-- ACCEPTANCE FEE --}}
{{-- ========================================================= --}}

<tr>

<td style="padding:0 35px 25px;">

<h2
    style="
        margin:0 0 10px;
        font-size:17px;
        color:#111827;
    "
>
    What you need to do next
</h2>

<p
    style="
        margin:0;
        font-size:14px;
        line-height:1.8;
        color:#4b5563;
    "
>
    To proceed with your admission, you are required to
    accept the offer and pay the approved non-refundable
    acceptance fee through the school's Student
    Portal.
</p>

<p
    style="
        margin:10px 0 0;
        font-size:14px;
        line-height:1.8;
        color:#4b5563;
    "
>
    Detailed instructions on acceptance, payment and the
    subsequent registration process are available on the
    Student Portal.
</p>

</td>

</tr>


{{-- ========================================================= --}}
{{-- PORTAL BUTTON --}}
{{-- ========================================================= --}}

<tr>

<td
    align="center"
    style="padding:0 35px 30px;"
>

<a
    href="{{ config('app.student_portal_url') }}"
    style="
        display:inline-block;
        padding:13px 25px;
        background:#111827;
        color:#ffffff;
        text-decoration:none;
        border-radius:6px;
        font-size:14px;
        font-weight:bold;
    "
>
    Access Student Portal
</a>

<p
    style="
        margin:12px 0 0;
        font-size:12px;
        color:#6b7280;
    "
>
    Please use your school credentials to log in.
</p>

</td>

</tr>


{{-- ========================================================= --}}
{{-- IMPORTANT NOTE --}}
{{-- ========================================================= --}}

<tr>

<td style="padding:0 35px 30px;">

<div
    style="
        padding:16px 18px;
        background:#f9fafb;
        border-left:3px solid #9ca3af;
        font-size:13px;
        line-height:1.7;
        color:#4b5563;
    "
>

<strong style="color:#111827;">
Important:
</strong>

Your admission remains conditional and is subject to
successful verification of your credentials and compliance
with the Heritage Polytechnic's admission and registration
requirements.

</div>

</td>

</tr>


{{-- ========================================================= --}}
{{-- FOOTER --}}
{{-- ========================================================= --}}

<tr>

<td
    style="
        padding:25px 35px;
        background:#f9fafb;
        border-top:1px solid #e5e7eb;
        text-align:center;
    "
>

<div
    style="
        font-size:13px;
        font-weight:bold;
        color:#374151;
    "
>
    HERITAGE POLYTECHNIC
</div>

<div
    style="
        margin-top:6px;
        font-size:12px;
        line-height:1.6;
        color:#6b7280;
    "
>
    Admissions Unit<br>
    Akwa Ibom State, Nigeria
</div>

<div
    style="
        margin-top:12px;
        font-size:11px;
        color:#9ca3af;
    "
>
    This is an automated admission notification.
    Please do not reply directly to this email.
</div>

</td>

</tr>

</table>

</td>

</tr>

</table>

</body>

</html>
