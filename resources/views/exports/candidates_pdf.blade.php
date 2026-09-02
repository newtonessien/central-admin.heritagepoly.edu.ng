<!doctype html>
<html>
<head>
    <meta charset="utf-8">

    <title>{{ $title ?? 'Admission List' }}</title>

    <style>

        @page {
            margin: 125px 24px 45px 24px;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #111;
        }

        /*
        |--------------------------------------------------------------------------
        | Repeating Header
        |--------------------------------------------------------------------------
        */

        header {
            position: fixed;
            top: -105px;
            left: 0;
            right: 0;
            height: 95px;
            text-align: center;

        }

        .hdr-line-1 {
            font-size: 16px;
            font-weight: 700;
        }

        .hdr-line-2 {
            font-size: 13px;
            font-weight: 600;
            margin-top: 3px;
        }

        .hdr-line-3 {
            font-size: 12px;
            margin-top: 2px;
        }

        .hdr-line-4 {
            font-size: 12px;
            margin-top: 2px;
        }

        /*
        |--------------------------------------------------------------------------
        | Table
        |--------------------------------------------------------------------------
        */

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #d5d5d5;
            padding: 5px;
            vertical-align: top;
        }

        th {
            background: #f1f1f1;
            font-weight: 700;
            text-align: left;
        }

        .sn {
            text-align: right;
        }

        .regno {
            white-space: nowrap;
        }

        .qualification {
            word-break: break-word;
            white-space: normal;
            line-height: 1.45;
        }

        .remarks {
            word-break: break-word;
        }

        .muted {
            color: #666;
        }

        .jamb-no {
    word-break: break-all;
    overflow-wrap: anywhere;
    font-size: 9px;
}

.jamb-score {
    text-align: center;
    white-space: nowrap;
}

    </style>
</head>

<body>

@php

    /*
    |--------------------------------------------------------------------------
    | Dynamic Header Information
    |--------------------------------------------------------------------------
    */

    $school = $hdr['school'] ?? 'Heritage Polytechnic';

    $session = $hdr['session'] ?? null;

    $faculty = $hdr['faculty'] ?? null;

    $department = $hdr['department'] ?? null;

    $program = $hdr['program'] ?? null;

    $ptype = $hdr['ptype'] ?? null;

    $applicationType = $hdr['application_type'] ?? null;

    /*
    |--------------------------------------------------------------------------
    | Determine whether JAMB columns are required
    |--------------------------------------------------------------------------
    |
    | CandidateResource already exposes this information.
    |
    */

    $isJamb = collect($rows ?? [])
        ->contains(
            fn ($row) =>
                !empty($row['JAMB No'])
                || !empty($row['JAMB Score'])
        );

@endphp


{{-- ===================================================================== --}}
{{-- REPEATING HEADER                                                       --}}
{{-- ===================================================================== --}}

<header>

    <div class="hdr-line-1">
        {{ $hdr['school'] ?? 'Heritage Polytechnic' }}
    </div>

    @if(!empty($hdr['session']))
        <div class="hdr-line-2">
            {{ $hdr['session'] }} Candidates Admission Screening List
        </div>
    @endif

    @if(!empty($hdr['faculty']))
        <div class="hdr-line-3">
            {{ $hdr['faculty'] }}
        </div>
    @endif

    @if(!empty($hdr['department']))
        <div class="hdr-line-4">
            {{ $hdr['department'] }}
        </div>
    @endif

    @if(!empty($hdr['program']))
        <div class="hdr-line-4">
            {{ $hdr['program'] }}
        </div>
    @endif

    @if(!empty($hdr['ptype']) || !empty($hdr['application_type']))

        <div class="hdr-line-4">

            @if(!empty($hdr['ptype']))
                {{ $hdr['ptype'] }}
            @endif

            @if(!empty($hdr['application_type']))
                — {{ $hdr['application_type'] }}
            @endif

        </div>

    @endif

</header>


{{-- ===================================================================== --}}
{{-- CANDIDATE TABLE                                                        --}}
{{-- ===================================================================== --}}

<table>

<thead>
    <tr>

        <th style="width:4%;">
            S/N
        </th>

        <th style="width:12%;">
            RegNo
        </th>

        <th style="width:20%;">
            Name
        </th>

        <th style="width:4%;">
            Sex
        </th>

        <th style="width:10%;">
            State
        </th>

        @if($isJamb)

            <th style="width:11%;">
                JAMB No
            </th>

            <th style="width:6%;">
                JAMB Score
            </th>

            <th style="width:27%;">
                Qualification(s)
            </th>

        @else

            <th style="width:39%;">
                Qualification(s)
            </th>

        @endif

        <th style="width:6%;">
            Remarks
        </th>

    </tr>
</thead>

 <tbody>

@forelse(($rows ?? []) as $r)

    <tr>

        <td class="sn">
            {{ $loop->iteration }}
        </td>

        <td class="regno">
            {{ $r['RegNo'] ?? '' }}
        </td>

        <td>
            {{ $r['Name'] ?? '' }}
        </td>

        <td>
            {{ $r['Sex'] ?? '' }}
        </td>

        <td>
            {{ $r['State'] ?? '' }}
        </td>


        @if($isJamb)

            <td class="jamb-no">
                {{ $r['JAMB No'] ?? '' }}
            </td>

            <td class="jamb-score">
                {{ $r['JAMB Score'] == 0 ? '-' : $r['JAMB Score'] }}
            </td>

            <td class="qualification">
                {{ $r['Qualification(s)'] ?? '' }}
            </td>

        @else

            <td class="qualification">
                {{ $r['Qualification(s)'] ?? '' }}
            </td>

        @endif


        <td class="remarks">
            {{ $r['Remarks'] ?? '' }}
        </td>

    </tr>

@empty

    <tr>

        <td
            colspan="{{ $isJamb ? 9 : 7 }}"
            style="text-align:center; color:#666;"
        >
            No records.
        </td>

    </tr>

@endforelse

</tbody>

</table>

</body>
</html>
