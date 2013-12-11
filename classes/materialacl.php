<?php
/**
 * Access Control Library
 *
 * ### Introduction
 *
 * Gleez_ACL provides a lightweight and flexible database-based
 * Access Control Library (ACL) implementation for privileges
 * management. In general, an application may utilize such ACL's
 * to control access to certain protected objects by other
 * requesting objects.
 *
 * ### System Requirements
 *
 * - Default Database module
 * - Any ORM implementation
 *
 * @package    Gleez\ACL
 * @version    2.1.2
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 *
 * @todo       Implement their own exceptions (eg. ACL_Exception)
 */
class MaterialACL {

	/**
	 * Make sure the user has permission to do certain action on this object
	 *
	 * Similar to [Post::access] but this return TRUE/FALSE instead of exception
	 *
	 * @param   string     $action  The action `view|edit|delete` default `view`
	 * @param   ORM        $post    The post object
	 * @param   Model_User $user    The user object to check permission, defaults to loaded in user
	 * @param   string     $misc    The misc element usually `id|slug` for logging purpose
	 *
	 * @return  boolean
	 *
	 * @throws  HTTP_Exception_404
	 *
	 * @uses    User::active_user
	 * @uses    Module::event
	 */
	public static function post($action = 'view', $post, Model_User $user = NULL, $misc = NULL)
	{
		if ( ! in_array($action, array('view', 'edit', 'delete', 'add', 'list'), TRUE))
		{
			// If the $action was not one of the supported ones, we return access denied.
			Log::notice('Unauthorized attempt to access non-existent action :act.',
				array(':act' => $action)
			);
			return FALSE;
		}

		if ($post instanceof ORM AND ! $post->loaded())
		{
			// If the post was not loaded, we return access denied.
			throw HTTP_Exception::factory(404, 'Attempt to access non-existent post.');
		}

		if ( ! $post instanceof ORM)
		{
			$post = (object) $post;
		}

		// If no user object is supplied, the access check is for the current user.
		if (is_null($user))
		{
			$user = User::active_user();
		}

		if (self::check('bypass post access', $user))
		{
			return TRUE;
		}

		// Allow other modules to interact with access
		Module::event('post_access', $action, $post);

		if ($action === 'view')
		{
			if ($post->status === 'publish' AND self::check('access content', $user))
			{
				return TRUE;
			}
			// Check if authors can view their own unpublished posts.
			elseif ($post->status != 'publish'
				AND self::check('view own unpublished content', $user)
				AND $post->author == (int)$user->id
				AND $user->id != 1)
			{
				return TRUE;
			}
			else
			{
				return self::check('administer content', $user) OR self::check('administer content '.$post->type, $user);
			}
		}

		if ($action === 'edit')
		{
			if ((self::check('edit own '.$post->type) OR self::check('edit any '.$post->type))
				AND $post->author == (int)$user->id
				AND $user->id != 1)
			{
				return TRUE;
			}
			else
			{
				return self::check('administer content', $user) OR self::check('administer content '.$post->type, $user);
			}
		}

		if ($action === 'delete')
		{
			if ((self::check('delete own '.$post->type) OR self::check('delete any '.$post->type))
				AND $post->author == (int)$user->id
				AND $user->id != 1)
			{
				return TRUE;
			}
			else
			{
				return self::check('administer content', $user) OR self::check('administer content '.$post->type, $user);
			}
		}

		return TRUE;
	}
}
