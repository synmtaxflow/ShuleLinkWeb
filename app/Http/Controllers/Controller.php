<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function staffHasPermission(string $permissionName): bool
    {
        if (\Illuminate\Support\Facades\Session::get('user_type') !== 'Staff') {
            return false;
        }

        $staffID = \Illuminate\Support\Facades\Session::get('staffID');
        if (!$staffID) {
            return false;
        }

        $professionId = \Illuminate\Support\Facades\DB::table('other_staff')
            ->where('id', $staffID)
            ->value('profession_id');

        if (!$professionId) {
            return false;
        }

        return \Illuminate\Support\Facades\DB::table('staff_permissions')
            ->where('profession_id', $professionId)
            ->where('name', $permissionName)
            ->exists();
    }

    protected function staffHasAnyPermission(array $permissionNames): bool
    {
        foreach ($permissionNames as $permissionName) {
            if ($this->staffHasPermission($permissionName)) {
                return true;
            }
        }

        return false;
    }
}
