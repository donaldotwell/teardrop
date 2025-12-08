<?php

namespace App\Repositories;

class RolesRepository
{
    /**
     * Get all roles.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function all() : \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Role::all();
    }

    /**
     * Find a role by its id.
     *
     * @param int $id
     * @return \App\Models\Role
     */
    public static function find($id) : \App\Models\Role
    {
        return \App\Models\Role::find($id);
    }

    /**
     * Find a role by its name.
     *
     * @param string $name
     * @return \App\Models\Role
     */
    public static function findByName(string $name) : \App\Models\Role
    {
        return \App\Models\Role::where('name', $name)->first();
    }

    /**
     * Create a new role.
     *
     * @param array $data
     * @return \App\Models\Role
     */
    public static function create(array $data) : \App\Models\Role
    {
        return \App\Models\Role::create($data);
    }

    /**
     * Update a role.
     *
     * @param \App\Models\Role $role
     * @param array $data
     * @return bool
     */
    public static function update(\App\Models\Role $role, array $data) : bool
    {
        return $role->update($data);
    }

    /**
     * Delete a role.
     *
     * @param \App\Models\Role $role
     * @return bool
     */
    public static function delete(\App\Models\Role $role) : bool
    {
        return $role->delete();
    }

    // assign a role to a user. role is string
    public static function assignRole(\App\Models\User $user, string $role) : void
    {
        $role = \App\Models\Role::where('name', $role)->first();
        $user->roles()->attach($role);
    }

    /**
     * Get the users associated with the role.
     *
     * @param \App\Models\Role $role
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function users(\App\Models\Role $role) : \Illuminate\Database\Eloquent\Collection
    {
        return $role->users;
    }

    /**
     * Get the permissions associated with the role.
     *
     * @param \App\Models\Role $role
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function permissions(\App\Models\Role $role) : \Illuminate\Database\Eloquent\Collection
    {
        return $role->permissions;
    }

    /**
     * Attach a permission to the role.
     *
     * @param \App\Models\Role $role
     * @param \App\Models\Permission $permission
     * @return void
     */
    public static function attach(\App\Models\Role $role, \App\Models\Permission $permission) : void
    {
        $role->permissions()->attach($permission);
    }

    /**
     * Detach a permission from the role.
     *
     * @param \App\Models\Role $role
     * @param \App\Models\Permission $permission
     * @return void
     */
    public static function detach(\App\Models\Role $role, \App\Models\Permission $permission) : void
    {
        $role->permissions()->detach($permission);
    }

    /**
     * Sync the permissions for the role.
     *
     * @param \App\Models\Role $role
     * @param array $permissions
     * @return void
     */
    public static function sync(\App\Models\Role $role, array $permissions) : void
    {
        $role->permissions()->sync($permissions);
    }

}
