<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Export' }}</title>
    <style>
    /* Reserve enough space for a tall header + a footer */
    @page { margin: 130px 24px 50px 24px; } /* top, right, bottom, left */

    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }

    /* Repeating header */
    header {
        position: fixed;
        top: -110px;     /* should be ~ (page top margin - 20px padding) */
        left: 0;
        right: 0;
        height: 100px;   /* keep this <= (page top margin) */
        text-align: center;
    }
    .hdr-line-1 { font-size: 16px; font-weight: 700; }
    .hdr-line-2 { font-size: 13px; font-weight: 600; margin-top: 2px; }
    .hdr-line-3 { font-size: 12px; margin-top: 1px; }
    .hdr-line-4 { font-size: 12px; margin-top: 1px; }
    .hdr-line-5 { font-size: 12px; margin-top: 6px; text-transform: uppercase; }

    .muted { color: #666; font-size: 11px; margin: 0 0 8px 0; }

    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    th, td { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
    th { background: #f4f4f4; text-align: left; }
</style>

</head>
<body>

    {{-- Repeating header content --}}
<header>
  <div class="hdr-line-1">{{ $hdr['school'] ?? 'University of Uyo' }}</div>
  @if(!empty($hdr['faculty']))    <div class="hdr-line-2">{{ $hdr['faculty'] }}</div>@endif
  @if(!empty($hdr['department'])) <div class="hdr-line-3">{{ $hdr['department'] }}</div>@endif
  @if(!empty($hdr['program']))    <div class="hdr-line-4">{{ $hdr['program'] }}</div>@endif

  {{-- <div class="hdr-line-5"> --}}
  @php
      $y = (int) now()->year;
        $m = (int) now()->month;
        $start = ($m >= 8) ? $y : $y - 1;
        $session = $start . '/' . ($start + 1);
  @endphp
{{-- </div> --}}
  @if(!empty($hdr['ptype'])) <div class="hdr-line-4">{{ $hdr['ptype'] }} ({{ $session . ' - Screening list' }})</div>@endif

</header>

    {{-- Optional generated-at line under the header (first page visible, repeats in flow) --}}
    {{-- <div class="muted">Generated: {{ optional($generated_at)->format('Y-m-d H:i') }}</div> --}}

    {{-- === Your table goes here (keep your working version) === --}}
    <main>
    <table style="width:100%; border-collapse:collapse; table-layout:fixed;">
        <thead>
            <tr>
                <th style="width:2%;  text-align:right; background:#f4f4f4; border:1px solid #ddd; padding:6px;">S/N</th>
                <th style="width:14%; background:#f4f4f4; border:1px solid #ddd; padding:6px;">RegNo</th>
                <th style="width:24%; background:#f4f4f4; border:1px solid #ddd; padding:6px;">Name</th>
                <th style="width:2%;  background:#f4f4f4; border:1px solid #ddd; padding:6px;">Sex</th>
                <th style="width:12%; background:#f4f4f4; border:1px solid #ddd; padding:6px;">State</th>
                <th style="width:40%; background:#f4f4f4; border:1px solid #ddd; padding:6px;">O'Level Details</th>
                <th style="width:6%;  background:#f4f4f4; border:1px solid #ddd; padding:6px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($rows ?? []) as $r)
                <tr>
                    <td style="width:3%;  text-align:right; border:1px solid #ddd; padding:6px;">{{ $loop->iteration }}</td>
                    <td style="width:12%; border:1px solid #ddd; padding:6px; white-space:nowrap;">{{ $r['RegNo'] ?? '' }}</td>
                    <td style="width:24%; border:1px solid #ddd; padding:6px;">{{ $r['Name'] ?? '' }}</td>
                    <td style="width:3%;  border:1px solid #ddd; padding:6px;">{{ $r['Sex'] ?? '' }}</td>
                    <td style="width:12%; border:1px solid #ddd; padding:6px;">{{ $r['State'] ?? '' }}</td>
                    <td style="width:40%; border:1px solid #ddd; padding:6px; word-break:break-word; white-space:normal;">
                        {{ $r['Olevel Details'] ?? '' }}
                    </td>
                    <td style="width:6%;  border:1px solid #ddd; padding:6px;">{{ $r['Status'] ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="border:1px solid #ddd; padding:6px; color:#666; font-size:11px;">No records.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </main>

</body>
</html>
