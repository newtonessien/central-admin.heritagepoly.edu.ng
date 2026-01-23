@if ($eligibilityMessage)
<div class="rounded-xl border p-5 transition-all duration-300
{{ $isEligible
? 'border-green-200 dark:border-green-800 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20'
: 'border-red-200 dark:border-red-800 bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20'
}}"
role="alert"
aria-live="assertive"
>
<div class="flex items-start space-x-4">
<!-- Icon Container -->
<div class="flex-shrink-0">
<div class="p-2.5 rounded-lg
{{ $isEligible
? 'bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400'
: 'bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400'
}}"
>
@if ($isEligible)
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
</svg>
@else
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
</svg>
@endif
</div>
</div>

<!-- Message Content -->
<div class="flex-1 min-w-0">
<div class="flex items-center justify-between mb-1">
<h3 class="font-semibold
{{ $isEligible
? 'text-green-900 dark:text-green-300'
: 'text-red-900 dark:text-red-300'
}}"
>
@if ($isEligible)
<span class="flex items-center space-x-2">
<span>Eligibility Check Passed</span>
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200">
Eligible
</span>
</span>
@else
<span class="flex items-center space-x-2">
<span>Eligibility Check Failed</span>
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200">
Not Eligible
</span>
</span>
@endif
</h3>

@if ($isEligible)
<div class="hidden sm:block">
<div class="flex items-center space-x-1 text-xs
{{ $isEligible
? 'text-green-700 dark:text-green-400'
: 'text-red-700 dark:text-red-400'
}}"
>
{{-- <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
</svg> --}}
<flux:link href="{{ route('students.change-of-course') }}">
  Back to Home
</flux:link>
</div>
</div>
@endif
</div>

<p class="text-sm leading-relaxed
{{ $isEligible
? 'text-green-800 dark:text-green-300/90'
: 'text-red-800 dark:text-red-300/90'
}}"
>
{{ $eligibilityMessage }}
</p>

<!-- Additional Guidance -->
@if (!$isEligible)
<div class="mt-3 pt-3 border-t
{{ $isEligible
? 'border-green-200 dark:border-green-800'
: 'border-red-200 dark:border-red-800'
}}"
>
<p class="text-xs
{{ $isEligible
? 'text-green-700 dark:text-green-400'
: 'text-red-700 dark:text-red-400'
}}"
>
<span class="font-medium">Note:</span>
Please review the student's current status or contact the academic office for further assistance.
</p>
</div>
@else
<div class="mt-3 flex items-center space-x-2 text-xs
{{ $isEligible
? 'text-green-700 dark:text-green-400'
: 'text-red-700 dark:text-red-400'
}}"
>
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
</svg>
<span>Student meets all requirements for course change</span>

</div>
@endif
</div>

<!-- Close Button (Optional) -->
@if (false) {{-- Set to true if you want close functionality --}}
<button
type="button"
class="flex-shrink-0 p-1 rounded-full hover:bg-white/50 dark:hover:bg-gray-800/50 transition-colors"
aria-label="Dismiss message"
>
<svg class="w-4 h-4
{{ $isEligible
? 'text-green-600 dark:text-green-400'
: 'text-red-600 dark:text-red-400'
}}"
fill="none"
stroke="currentColor"
viewBox="0 0 24 24"
>
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
d="M6 18L18 6M6 6l12 12"/>
</svg>
</button>
@endif
</div>
</div>
@endif
