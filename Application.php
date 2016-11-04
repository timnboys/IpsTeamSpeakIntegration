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
		$isInstalled = (bool) \IPS\Db::i()->select( 'app_id', 'core_applications', array( 'app_directory=?', 'ts3integration' ) )->count();

		if ( $isInstalled )
		{
			foreach ( \IPS\Db::i()->select( 'member_id, ts3_uuid', 'ts3integration_uuids' ) as $info )
			{
				$uuid = new \IPS\teamspeak\Uuid;
				$uuid->member_id = $info['member_id'];
				$uuid->uuid = $info['ts3_uuid'];
				$uuid->save();
			}

			/* Disable old app */
			\IPS\Db::i()->update( 'core_applications', array( 'app_enabled' => 0 ), array( 'app_directory=?', 'ts3integration' ) );
		}
	}

	/**
	 * Output friendly error, in-case teamspeak is not configured.
	 */
	public function isConfigured()
	{
		if ( empty( \IPS\Settings::i()->teamspeak_server_ip ) || empty( \IPS\Settings::i()->teamspeak_query_password ) )
		{
			\IPS\Output::i()->error( 'teamspeak_not_configured', '4P103/1' );
		}
	}
}