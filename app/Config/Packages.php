<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Author Information For Package Generation
    |--------------------------------------------------------------------------
    |
    */

    'author' => array(
        'name'     => 'John Doe',
        'email'    => 'john.doe@novaframework.dev',
        'homepage' => 'http://novaframework.dev',
    ),

    //--------------------------------------------------------------------------
    // Path To The Cache File
    //--------------------------------------------------------------------------

    'cache' => STORAGE_PATH .'framework' .DS .'packages.php',

    /*
    |---------------------------------------------------------------------------
    | Modules Options
    |---------------------------------------------------------------------------
    |
    |*/

    'modules' => array(

        //----------------------------------------------------------------------
        // Path to Modules
        //----------------------------------------------------------------------

        'path' => BASEPATH .'modules',

        //----------------------------------------------------------------------
        // Modules Base Namespace
        //----------------------------------------------------------------------

        'namespace' => 'Modules\\',
    ),

    /*
    |---------------------------------------------------------------------------
    | Loading Options For The Installed Packages
    |---------------------------------------------------------------------------
    |
    */

    'options' => array(
        'platform' => array(
            'enabled'  => true,
            'order'    => 7001,
        ),
        'fields' => array(
            'enabled'  => true,
            'order'    => 7002,
        ),
        'permissions' => array(
            'enabled'  => true,
            'order'    => 8001,
        ),
        'roles' => array(
            'enabled'  => true,
            'order'    => 8002,
        ),
        'users' => array(
            'enabled'  => true,
            'order'    => 8003,
        ),
        'content' => array(
            'enabled'  => true,
            'order'    => 8004,
        ),
        'chat' => array(
            'enabled'  => true,
            'order'    => 9001,
        ),
        'messages' => array(
            'enabled'  => true,
            'order'    => 9001,
        ),
    ),
);
