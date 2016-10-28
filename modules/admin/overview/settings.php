<?php


namespace IPS\teamspeak\modules\admin\overview;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

use IPS\Dispatcher;
use IPS\Helpers\Form;
use IPS\Helpers\Form\Number;
use IPS\Helpers\Form\Password;
use IPS\Helpers\Form\Text;
use IPS\Member;
use IPS\Output;
use IPS\Settings;

/**
 * settings
 */
class _settings extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		Dispatcher::i()->checkAcpPermission( 'settings_manage' );
		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{
		/* Build Settings Form */
		$settings = new Form('teamspeak_settings');

		$settings->addTab('teamspeak_basic_settings');
		$settings->add( new Text( 'teamspeak_server_ip', Settings::i()->teamspeak_server_ip ?: null, true ) );
		$settings->add( new Number( 'teamspeak_virtual_port', Settings::i()->teamspeak_virtual_port ?: null, true ) );
		$settings->add( new Number( 'teamspeak_query_port', Settings::i()->teamspeak_query_port ?: null, true ) );
		$settings->add( new Number( 'teamspeak_file_transfer_port', Settings::i()->teamspeak_file_transfer_port ?: null, true ) );
		$settings->add( new Text( 'teamspeak_query_admin', Settings::i()->teamspeak_query_admin ?: null, true ) );
		$settings->add( new Password( 'teamspeak_query_password', Settings::i()->teamspeak_query_password ?: null, true ) );
		$settings->add( new Text( 'teamspeak_query_nickname', Settings::i()->teamspeak_query_nickname ?: null, true/*,  [ 'regex' => "/^[\w\-]+$/" ]*/ ) );

		$settings->addTab('teamspeak_other_settings');
		$settings->add( new Form\YesNo( 'teamspeak_uuid_on_register', Settings::i()->teamspeak_uuid_on_register ?: null, false, array( 'togglesOn' => array( 'ts_uuid_register_force' ) ) ) );
		$settings->add( new Form\YesNo( 'teamspeak_uuid_on_register_force', Settings::i()->teamspeak_uuid_on_register_force ?: null, false, array(), null, null, null, 'ts_uuid_register_force' ) );

		if ( $values = $settings->values() )
		{
			$settings->saveAsSettings();
		}

		/* Output */
		Output::i()->title = Member::loggedIn()->language()->addToStack('teamspeak_settings_title');
		Output::i()->output = $settings;
	}
}