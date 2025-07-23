<?php

namespace App\Traits;

use App\Models\User;
use App\Models\UserManagesSectors;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait SectorFilter
{
    protected function applySectorFilter($query, $colName = 'sectorId')
    {
        $userid = auth()->user()->id;
        $userHasSectors = UserManagesSectors::where('userId', $userid)->get();

        $canPass = $this->adminFilter($userid);
        if($canPass) return $query;

        $sectorIds = [];

        foreach ($userHasSectors as $item) {
            $sectorIds[] = $item->sectorId;
        }
        return $query->whereIn($colName, $sectorIds);
    }

    private function adminFilter($userid) {
      $user = User::find($userid);

      if($user->isAdmin || $user->isSuperAdmin) return true;
      else return false;
    }



    protected function additionalSectorFilter($query, $sectorId) {
      $userid = auth()->user()->id;
      $canPass = $this->adminFilter($userid);

      if($canPass) return $query->where('sectorId', $sectorId);

      $userHasSectors = UserManagesSectors::query()
        ->where('userId', $userid)
        ->pluck('sectorId')
        ->toArray();

      return in_array($sectorId, $userHasSectors) ? $query->where('sectorId', $sectorId) : $query;
    }
}
