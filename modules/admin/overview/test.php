<?php


namespace IPS\teamspeak\modules\admin\overview;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

use IPS\Application;
use IPS\Dispatcher;
use IPS\Member;
use IPS\Output;
use IPS\Settings;
use IPS\teamspeak\Api\Server;
use IPS\Theme;

/**
 * test
 */
class _test extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return    void
	 */
	public function execute()
	{
		Dispatcher::i()->checkAcpPermission( 'test_manage' );
		parent::execute();
		Application::load( 'teamspeak' )->isConfigured();
	}

	/**
	 * ...
	 *
	 * @return    void
	 */
	protected function manage()
	{
		$ports = [
			(int) Settings::i()->teamspeak_query_port => false,
			(int) Settings::i()->teamspeak_file_transfer_port => false,
		];

		/* Check if needed ports are open */
		foreach ( $ports as $port => &$success )
		{
			if ( Server::isPortOpen( $port, Settings::i()->teamspeak_server_ip ) )
			{
				$success = true;
				continue;
			}

			$success = false;
		}

		try
		{
			$ts = Server::i()->checkConnection();
		}
		catch ( \Exception $e )
		{
			$ts = null;
		}

		/* Output */
		Output::i()->title = Member::loggedIn()->language()->addToStack( "teamspeak_test_title" );
		Output::i()->output = Theme::i()->getTemplate( 'test' )->test( $ts, $ports );
	}
}