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
	 *
	 * @param bool $return Return false if not configured.
	 * @return bool
	 */
	public function isConfigured( $return = false )
	{
		if ( empty( \IPS\Settings::i()->teamspeak_server_ip ) || empty( \IPS\Settings::i()->teamspeak_query_password ) )
		{
			if ( $return )
			{
				return false;
			}

			\IPS\Output::i()->error( 'teamspeak_not_configured', '4P103/1' );
		}

		return true;
	}

	/**
	 * [Node] Get Icon for tree
	 *
	 * @note	Return the class for the icon (e.g. 'globe')
	 * @return	string|null
	 */
	protected function get__icon()
	{
		return 'phone';
	}

	/**
	 * Default front navigation
	 *
	 * @code

	// Each item...
	array(
	'key'		=> 'Example',		// The extension key
	'app'		=> 'core',			// [Optional] The extension application. If omitted, uses this application
	'config'	=> array(...),		// [Optional] The configuration for the menu item
	'title'		=> 'SomeLangKey',	// [Optional] If provided, the value of this language key will be copied to menu_item_X
	'children'	=> array(...),		// [Optional] Array of child menu items for this item. Each has the same format.
	)

	return array(
	'rootTabs' 		=> array(), // These go in the top row
	'browseTabs'	=> array(),	// These go under the Browse tab on a new install or when restoring the default configuration; or in the top row if installing the app later (when the Browse tab may not exist)
	'browseTabsEnd'	=> array(),	// These go under the Browse tab after all other items on a new install or when restoring the default configuration; or in the top row if installing the app later (when the Browse tab may not exist)
	'activityTabs'	=> array(),	// These go under the Activity tab on a new install or when restoring the default configuration; or in the top row if installing the app later (when the Activity tab may not exist)
	)
	 * @endcode
	 * @return array
	 */
	public function defaultFrontNavigation()
	{
		return array(
			'rootTabs'		=> array( array( 'key' => "TeamSpeakTab" ) ),
			'browseTabs'	=> array(),
			'browseTabsEnd'	=> array(),
			'activityTabs'	=> array()
		);
	}
}