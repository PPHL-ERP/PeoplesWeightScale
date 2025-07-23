<?php

namespace App\Traits;

use App\Models\UserManageProduct;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait CategoryFilter
{
    protected function applyCategoryFilter($query)
    {
        $userid = auth()->user()->id;

        // Check if the user is an admin or super admin
        $canPass = $this->adminFilter($userid);
        if ($canPass) {
            return $query;
        }

        // Fetch the categories the user is allowed to access
        $userManagedCategories = UserManageProduct::where('userId', $userid)->get();

        $categoryIds = [];
        foreach ($userManagedCategories as $item) {
            $categoryIds[] = $item->productCategoryId;
        }

        // Filter the query to include only the allowed categories
        return $query->whereIn('id', $categoryIds);
    }

    private function adminFilter($userid)
    {
        $user = User::find($userid);

        // Allow unrestricted access for admin and super admin users
        return $user->isAdmin || $user->isSuperAdmin;
    }
}
