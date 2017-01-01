<?php


namespace IPS\teamspeak\setup\upg_10023;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * 1.0.17 Beta Upgrade Code
 */
class _Upgrade
{
	/**
	 * Remove duplicate member_ids.
	 *
	 * @return	array	If returns TRUE, upgrader will proceed to next step. If it returns any other value, it will set this as the value of the 'extra' GET parameter and rerun this step (useful for loops)
	 */
	public function step1()
	{
	    $query = <<<SQL
DELETE t1 FROM teamspeak_member_sync t1
JOIN teamspeak_member_sync t2
ON t2.s_member_id = t1.s_member_id
AND t2.s_id > t1.s_id

SQL;

        \IPS\Db::i()->query( $query );

		return TRUE;
	}
}