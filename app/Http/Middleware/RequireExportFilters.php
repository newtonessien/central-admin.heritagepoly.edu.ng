<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RequireExportFilters
{
    public function handle(Request $request, Closure $next)
    {
        $status = $request->query('status');
        $q      = trim((string) $request->query('q', ''));

        $hasAny =
            (int) $request->query('program_type_id', 0) > 0 ||
            (int) $request->query('faculty_id', 0)      > 0 ||
            (int) $request->query('department_id', 0)   > 0 ||
            (int) $request->query('program_id', 0)      > 0 ||
            in_array($status, ['pending','approved'], true) ||
            $q !== '';

        if (! $hasAny) {
            throw ValidationException::withMessages([
                'filters' => 'Please apply at least one filter or search before exporting.',
            ]);
        }

        return $next($request);
    }
}
