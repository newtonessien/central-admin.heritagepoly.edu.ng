<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class ProgramTypeAccessService
{
    protected ?User $user;

    protected ?array $cachedAccessibleIds = null;

    public function __construct(?User $user = null)
    {
        $this->user = $user ?? auth()->user();
    }

    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    */

    public function user(): ?User
    {
        return $this->user;
    }

    /*
    |--------------------------------------------------------------------------
    | Super Admin
    |--------------------------------------------------------------------------
    */

    public function isUnrestricted(): bool
    {
        return $this->user?->hasRole('super-admin') ?? false;
    }

//     public function isUnrestricted(): bool
// {
//     return $this->user?->hasAnyRole([
//         'super_admin',
//         'admin',
//     ]) ?? false;
// }

    /*
    |--------------------------------------------------------------------------
    | Accessible Programme Types
    |--------------------------------------------------------------------------
    */

    /**
     * Get programme type IDs accessible to the current user.
     *
     * An empty array means unrestricted access.
     */
     public function accessibleIds(): array
{
    if ($this->cachedAccessibleIds !== null) {
        return $this->cachedAccessibleIds;
    }

    if (!$this->user || $this->isUnrestricted()) {
        return $this->cachedAccessibleIds = [];
    }

    return $this->cachedAccessibleIds = $this->user
        ->programTypeAccess()
        ->pluck('program_type_id')
        ->map(fn ($id) => (int) $id)
        ->values()
        ->all();
}

    /**
     * Determine whether the current user can access
     * a specific programme type.
     */
    public function canAccess(int $programTypeId): bool
    {
        if (!$this->user) {
            return false;
        }

        if ($this->isUnrestricted()) {
            return true;
        }

        return in_array(
            $programTypeId,
            $this->accessibleIds(),
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Filtering
    |--------------------------------------------------------------------------
    */

    /**
     * Filter programme types according to user access.
     */
    public function filter(array|Collection $programTypes): Collection
    {
        $programTypes = collect($programTypes);

        if (!$this->user) {
            return collect();
        }

        if ($this->isUnrestricted()) {
            return $programTypes->values();
        }

        $allowedIds = $this->accessibleIds();

        return $programTypes
            ->filter(function ($programType) use ($allowedIds) {
                return in_array(
                    (int) ($programType['id'] ?? 0),
                    $allowedIds,
                    true
                );
            })
            ->values();
    }

    /**
     * Filter programme types and return a plain array.
     */
    public function filterArray(array|Collection $programTypes): array
    {
        return $this->filter($programTypes)->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

    /**
     * Authorize access to a programme type.
     *
     * Throws HTTP 403 when access is denied.
     */
    public function authorize(int $programTypeId): void
    {
        abort_unless(
            $this->canAccess($programTypeId),
            403,
            'You are not authorized to access this programme type.'
        );
    }
}
