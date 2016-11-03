//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class teamspeak_hook_ban_sync extends _HOOK_CLASS_
{
	/**
	 * Set banned
	 *
	 * @param	string	$value	Value
	 * @return	void
	 */
	public function set_temp_ban( $value )
	{
		call_user_func_array( 'parent::set_temp_ban', func_get_args() );

		/* Check if banning setting is enabled */
		if ( (bool) \IPS\Settings::i()->teamspeak_sync_bans )
		{
			$tsMember = \IPS\teamspeak\Member::i();
			/* 0 => Unban */
			if ( $value === 0 )
			{
				$tsMember->unban( $this );
			}
			else
			{
				/* -1 => Perm ban */
				$time = ( $value === -1 ) ? 0 : ( $value - time() );
				$tsMember->ban( $this, $time, 'Banned on Forum' );
			}
		}
	}
}
