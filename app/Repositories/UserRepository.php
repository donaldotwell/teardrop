<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    /**
     * Find a user by its id.
     *
     * @param int $id
     * @return \App\Models\User
     */
    public static function find($id) : \App\Models\User
    {
        return User::find($id);
    }

    /**
     * Find a user by its private username.
     *
     * @param string $username
     * @return \App\Models\User
     */
    public static function findByUsernamePri($username) : \App\Models\User
    {
        return User::where('username_pri', $username)->first();
    }

    /**
     * Find a user by its public username.
     *
     * @param string $username
     * @return \App\Models\User
     */
    public static function findByUsernamePub($username) : \App\Models\User
    {
        return User::where('username_pub', $username)->first();
    }

}
