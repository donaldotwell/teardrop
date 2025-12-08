<?php

namespace App\Repositories;

class PermissionsRepository
{
    /**
     * Get all permissions.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function all() : \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Permission::all();
    }

    /**
     * Find a permission by its id.
     *
     * @param int $id
     * @return \App\Models\Permission
     */
    public static function find($id) : \App\Models\Permission
    {
        return \App\Models\Permission::find($id);
    }

    /**
     * Find a permission by its name.
     *
     * @param string $name
     * @return \App\Models\Permission
     */
    public static function findByName(string $name) : \App\Models\Permission
    {
        return \App\Models\Permission::where('name', $name)->first();
    }

    /**
     * Create a new permission.
     *
     * @param array $data
     * @return \App\Models\Permission
     */
    public static function create(array $data) : \App\Models\Permission
    {
        return \App\Models\Permission::create($data);
    }

    /**
     * Update a permission.
     *
     * @param \App\Models\Permission $permission
     * @param array $data
     * @return bool
     */
    public static function update(\App\Models\Permission $permission, array $data) : bool
    {
        return $permission->update($data);
    }

    /**
     * Delete a permission.
     *
     * @param \App\Models\Permission $permission
     * @return bool
     */
    public static function delete(\App\Models\Permission $permission) : bool
    {
        return $permission->delete();
    }

    /**
     * Sync the roles for a permission.
     *
     * @param \App\Models\Permission $permission
     * @param array $roles
     * @return void
     */
    public static function syncRoles(\App\Models\Permission $permission, array $roles) : void
    {
        $permission->roles()->sync($roles);
    }

}
