<?php
/**
 * Setting the Routes
 *
 * @package    Gleez\User\Routing
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
if ( ! Route::cache())
{   
    Route::set('materials', 'materials(/<type>)', array(
        'type'      => 'video|audio|text'
    ))
    ->defaults(array(
        'controller' => 'material',   
        'action'     => 'list',
    ));

    Route::set('material', 'material(/<action>(/<id>))', array(
        'action'    => 'view|add|edit|delete',
        'id'        => '\d+'
    ))
    ->defaults(array(
        'controller' => 'material',
        'action'     => 'view',
    ));
    
    Route::set('admin/material', 'admin/material(/<action>)(/<id>)', array(
        'action'    => 'list|bulk',
        'id'        => '\d+'
    ))
    ->defaults(array(
        'directory'  => 'admin',
        'controller' => 'material',
        'action'     => 'list',
    ));
}


/**
 * Define Module specific Permissions
 *
 * Definition of user privileges by default if the ACL is present in the system.
 * Note: Parameter `restrict access` indicates that these privileges have serious
 * implications for safety.
 *
 * @uses ACL Used to define the privileges
 */
if ( ! ACL::cache() )
{
    ACL::set('material', array(

        'access content'      => array(
            'title'           => __('Access content'),
            'restrict access' => TRUE,
            'description'     => __('Access material content'),
        ),
        'administer permissions' => array(
            'title'           => __('Administer permissions'),
            'restrict access' => TRUE,
            'description'     => __('Managing material authority'),
        ),
        'create material' => array(
            'title'           => __('Create material'),
            'restrict access' => TRUE,
            'description'     => __('Create material'),
        ),
        'edit own material' => array(
            'title'           => __('Edit own material'),
            'restrict access' => FALSE,
            'description'     => __('Edit own material'),
        ),
        'edit any material' => array(
            'title'           => __('Edit any material'),
            'restrict access' => FALSE,
            'description'     => __('Edit any material'),
        ),
        'delete own material' => array(
            'title'           => __('Delete own material'),
            'restrict access' => FALSE,
            'description'     => __('Delete own material'),
        ),
        'delete any material' => array(
            'title'           => __('Delete any material'),
            'restrict access' => FALSE,
            'description'     => __('Delete any material'),
        )
    ));

    /** Cache the module specific permissions in production */
    ACL::cache(FALSE, Kohana::$environment === Kohana::PRODUCTION);
}

