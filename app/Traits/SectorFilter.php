<?php

namespace App\Traits;

use App\Models\User;
use App\Models\UserManagesSectors;
use Illuminate\Support\Facades\Auth;

trait SectorFilter
{
    /**
     * Logged-in user এর managed sectors অনুযায়ী query restrict করবে।
     * $colName: target table sector column name (e.g. 'sector_id' or 'sectorId')
     */
    protected function applySectorFilter($query, $colName = 'sector_id')
{
    $userid = auth()->id();
    if (!$userid) return $query->whereRaw('1=0');

    $canPass = $this->adminFilter($userid);
    if ($canPass) return $query;

    $sectorIds = UserManagesSectors::where('userId', $userid)
        ->pluck('sectorId')
        ->map(fn($x) => (int)$x)
        ->unique()
        ->values()
        ->toArray();

    return empty($sectorIds)
        ? $query->whereRaw('1=0')
        : $query->whereIn($colName, $sectorIds);
}

protected function additionalSectorFilter($query, $sectorId, $colName = 'sector_id')
{
    $userid = auth()->id();
    if (!$userid) return $query->whereRaw('1=0');

    $canPass = $this->adminFilter($userid);
    if ($canPass) return $query->where($colName, $sectorId);

    $userHasSectors = UserManagesSectors::query()
        ->where('userId', $userid)
        ->pluck('sectorId')
        ->map(fn($x) => (int)$x)
        ->toArray();

    return in_array((int)$sectorId, $userHasSectors, true)
        ? $query->where($colName, $sectorId)
        : $query->whereRaw('1=0');
}

    protected function applySectorFilter000($query, string $colName = 'sectorId')
    {
        $userId = Auth::id();

        // unauthenticated => no data
        if (!$userId) {
            return $query->whereRaw('1=0');
        }

        // admin/superadmin => full access
        if ($this->adminFilter($userId)) {
            return $query;
        }

        $sectorIds = UserManagesSectors::query()
            ->where('userId', $userId)
            ->pluck('sectorId')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // no sector assigned => no data
        if (empty($sectorIds)) {
            return $query->whereRaw('1=0');
        }

        return $query->whereIn($colName, $sectorIds);
    }

    /**
     * নির্দিষ্ট sector request এ safe filter apply করবে।
     * non-admin unauthorized sector দিলে no rows.
     */
    protected function additionalSectorFilter000($query, $sectorId, string $colName = 'sectorId')
    {
        $userId = Auth::id();

        if (!$userId) {
            return $query->whereRaw('1=0');
        }

        if (empty($sectorId)) {
            return $query;
        }

        if ($this->adminFilter($userId)) {
            return $query->where($colName, (int)$sectorId);
        }

        $allowed = UserManagesSectors::query()
            ->where('userId', $userId)
            ->pluck('sectorId')
            ->map(fn($v) => (int)$v)
            ->toArray();

        if (in_array((int)$sectorId, $allowed, true)) {
            return $query->where($colName, (int)$sectorId);
        }

        return $query->whereRaw('1=0');
    }

    private function adminFilter($userId): bool
    {
        $user = User::find($userId);
        if (!$user) return false;

        return $this->toBool($user->isAdmin) || $this->toBool($user->isSuperAdmin);
    }

    private function toBool($value): bool
    {
        if (is_bool($value)) return $value;

        $v = strtolower(trim((string)$value));
        return in_array($v, ['1', 'true', 'yes', 'y', 'on'], true);
    }

}
