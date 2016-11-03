<?php
/**
 * @brief		TeamSpeak Integration Application Class
 * @author		<a href=''>Ahmad E.</a>
 * @copyright	(c) 2016 Ahmad E.
 * @package		IPS Community Suite
 * @subpackage	TeamSpeak Integration
 * @since		08 Oct 2016
 * @version		
 */
 
namespace IPS\teamspeak;

use IPS\Db;
use IPS\Output;
use IPS\Settings;

/**
 * TeamSpeak Integration Application Class
 */
class _Application extends \IPS\Application
{
	/**
	 * Convert UUIDs from previous version if available.
	 */
	public function installOther()
	{
		/* Convert UUIDs from TS3Integration to the new format if the old app is installed */
		$isInstalled = (bool) Db::i()->select( 'app_id', 'core_applications', array( 'app_directory=?', 'ts3integration' ) )->count();

		if ( $isInstalled )
		{
			foreach ( Db::i()->select( 'member_id, ts3_uuid', 'ts3integration_uuids' ) as $info )
			{
				$uuid = new Uuid;
				$uuid->member_id = $info['member_id'];
				$uuid->uuid = $info['ts3_uuid'];
				$uuid->save();
			}

			/* Disable old app */
			Db::i()->update( 'core_applications', array( 'app_enabled' => 0 ), array( 'app_directory=?', 'ts3integration' ) );
		}
	}

	/**
	 * Output friendly error, in-case teamspeak is not configured.
	 */
	public function isConfigured()
	{
		if ( empty( Settings::i()->teamspeak_server_ip ) || empty( Settings::i()->teamspeak_query_password ) )
		{
			Output::i()->error( 'teamspeak_not_configured', '4P103/1' );
		}
	}
}