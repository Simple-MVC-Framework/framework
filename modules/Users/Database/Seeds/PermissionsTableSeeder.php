<?php

namespace Modules\Users\Database\Seeds;

use Nova\Database\ORM\Model;
use Nova\Database\Seeder;

use Modules\Permissions\Traits\ManagePermissionsTrait;


class PermissionsTableSeeder extends Seeder
{
    use ManagePermissionsTrait;


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = array(
            array(
                'name'  => 'View the Users List',
                'slug'  => 'module.users.lists',
                'group' => 'users',

                'roles' => array(1, 2, 3),
            ),
            array(
                'name'  => 'View the User accounts',
                'slug'  => 'module.users.view',
                'group' => 'users',

                'roles' => array(1, 2, 3),
            ),
            array(
                'name'  => 'View his own User account',
                'slug'  => 'module.users.view.own',
                'group' => 'users',

                'roles' => array(1, 2, 3),
            ),
            array(
                'name'  => 'Create new User accounts',
                'slug'  => 'module.users.create',
                'group' => 'users',

                'roles' => array(1, 2, 3),
            ),
            array(
                'name'  => 'Update the User accounts',
                'slug'  => 'module.users.update',
                'group' => 'users',

                'roles' => array(1, 2, 3),
            ),
            array(
                'name'  => 'Update his own User account',
                'slug'  => 'module.users.update.own',
                'group' => 'users',

                'roles' => array(1, 2, 3),
            ),
            array(
                'name'  => 'Delete User accounts',
                'slug'  => 'module.users.delete',
                'group' => 'users',

                'roles' => array(1, 2, 3),
            ),
            array(
                'name'  => 'Delete his own User account',
                'slug'  => 'module.users.delete.own',
                'group' => 'users',

                'roles' => array(1, 2, 3),
            ),

            // FieldItems
            array(
                'name'  => 'View the Field Items List',
                'slug'  => 'module.users.fields.lists',
                'group' => 'users',

                'roles' => array(1, 2, 3),
            ),
            array(
                'name'  => 'View the Field Items',
                'slug'  => 'module.users.fields.view',
                'group' => 'users',

                'roles' => array(1, 2, 3),
            ),
            array(
                'name'  => 'Create new Field Items',
                'slug'  => 'module.users.fields.create',
                'group' => 'users',

                'roles' => array(1, 2, 3),
            ),
            array(
                'name'  => 'Update the Field Items',
                'slug'  => 'module.users.fields.update',
                'group' => 'users',

                'roles' => array(1, 2, 3),
            ),
            array(
                'name'  => 'Delete Field Items',
                'slug'  => 'module.users.fields.delete',
                'group' => 'users',

                'roles' => array(1, 2, 3),
            ),
         );

        $this->createPermissions($permissions);
    }
}
