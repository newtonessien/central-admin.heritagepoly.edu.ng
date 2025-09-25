<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admitted Students Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Admitted Students Screening List</h2>

    <p>
        <strong>Academic Session:</strong> {{ $acadSession }}<br>
        <strong>Program Type:</strong> {{ $programType }}<br>
        <strong>Faculty:</strong> {{ $faculty }}<br>
        <strong>Department:</strong> {{ $department }}<br>
        <strong>Program:</strong> {{ $program }}
    </p>

    <table>
        <thead>
            <tr>
                <th>RegNo</th>
                <th>Name</th>
                <th>Sex</th>
                <th>Program Type</th>
                <th>Faculty</th>
                <th>Department</th>
                <th>Program</th>
                <th>Screening Code</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $s)
                <tr>
                    <td>{{ $s['regno'] ?? '—' }}</td>
                    <td>{{ $s['name'] ?? '—' }}</td>
                    <td>{{ $s['sex'] ?? '—' }}</td>
                    <td>{{ $s['program_type'] ?? '—' }}</td>
                    <td>{{ $s['faculty'] ?? '—' }}</td>
                    <td>{{ $s['department'] ?? '—' }}</td>
                    <td>{{ $s['program'] ?? '—' }}</td>
                    <td>{{ $s['screening_code'] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
