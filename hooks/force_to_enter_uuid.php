//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class teamspeak_hook_force_to_enter_uuid extends _HOOK_CLASS_
{
	/**
	 * Init
	 *
	 * @return    void
	 */
	public function init()
	{
		call_user_func_array( 'parent::init', func_get_args() );
		$member = \IPS\Member::loggedIn();

		/* Is member? */
		if ( $member->member_id )
		{
			$request = \IPS\Request::i();
			$forced = false;

			/* Is member in a group that is forced to enter their UUID? */
			foreach ( $member->get_groups() as $groupId )
			{
				try
				{
					$group = \IPS\Member\Group::load( $groupId );
				}
				catch ( \OutOfRangeException $e )
				{
					/* Apparently some users have a groupId of 0 */
					continue;
				}

				if ( (bool) $group->teamspeak_require_uuid )
				{
					$forced = true;
					break;
				}
			}

			if ( $forced )
			{
				$hasUuid = \IPS\Db::i()
				             ->select( 's_id', 'teamspeak_member_sync', array( 's_member_id=?', $member->member_id ) )
				             ->count();


				$app = 'teamspeak';
				$module = 'teamspeak';
				$controller = 'membersync';
				$alreadyOnPage =
					$request->app === $app && $request->module === $module && $request->controller === $controller;

				if ( !$member->members_bitoptions['validating'] && !$hasUuid && !$alreadyOnPage &&
					\IPS\Dispatcher::i()->controllerLocation === 'front' && $request->controller !== 'login'
				)
				{
					\IPS\Output::i()->redirect(
						\IPS\Http\Url::internal(
							"app={$app}&module={$module}&controller={$controller}&forced=1", 'front'
						)
					);
				}
			}
		}
	}
}
