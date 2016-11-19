<?php

namespace IPS\teamspeak\modules\admin\teamspeak;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Server
 */
class _Server extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return    void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'server_manage' );
		parent::execute();
		\IPS\Application::load( 'teamspeak' )->isConfigured();
	}

	/**
	 * Display form to edit the TeamSpeak server information.
	 *
	 * @return    void
	 */
	protected function manage()
	{
		/* Get Server/Channel Groups */
		$serverGroups = \IPS\teamspeak\Api\Group::getCachedServerGroups( true, false );
		$channelGroups = \IPS\teamspeak\Api\Group::getCachedChannelGroups();
		$tsInstance = null;

		if ( is_null( $serverGroups ) || is_null( $channelGroups ) )
		{
			$groupClass = \IPS\teamspeak\Api\Group::i();
		}

		if ( isset( $groupClass ) )
		{
			$serverGroups = $groupClass->getServerGroups( true, false );
			$channelGroups = $groupClass->getChannelGroups();
			$tsInstance = $groupClass->getInstance(); // Re-use the already established connection.
		}

		/* Get server information */
		$tsServer = \IPS\teamspeak\Api\Server::i( $tsInstance );
		$serverInfo = $tsServer->getServerInfo();

		if ( !$serverInfo )
		{
			\IPS\Output::i()->error( 'teamspeak_serverinfo_error', '4P101/1' );
		}

		/* Build form */
		$form = new \IPS\Helpers\Form();
		$form->addTab( 'teamspeak_server' );
		$form->add( new \IPS\Helpers\Form\Text( 'teamspeak_name', $serverInfo['teamspeak_name'] ?: null, true ) );

		$form->add(
			new \IPS\Helpers\Form\Text( 'teamspeak_welcomemessage', $serverInfo['teamspeak_welcomemessage'] ?: null, true )
		);
		$form->add( new \IPS\Helpers\Form\Number( 'teamspeak_maxclients', $serverInfo['teamspeak_maxclients'] ?: 0, true ) );
		$form->add( new \IPS\Helpers\Form\Number( 'teamspeak_reserved_slots', $serverInfo['teamspeak_reserved_slots'] ?: 0, true ) );

		$form->addTab( 'teamspeak_transfer' );
		$form->addHeader( 'teamspeak_download' );
		$form->add(
			new \IPS\Helpers\Form\Number(
				'teamspeak_max_download_total_bandwidth', $serverInfo['teamspeak_max_download_total_bandwidth'] ?: 0,
				true
			)
		);
		$form->add( new \IPS\Helpers\Form\Number( 'teamspeak_download_quota', $serverInfo['teamspeak_download_quota'] ?: 0, true ) );
		$form->addHeader( 'teamspeak_upload' );
		$form->add(
			new \IPS\Helpers\Form\Number(
				'teamspeak_max_upload_total_bandwidth', $serverInfo['teamspeak_max_upload_total_bandwidth'] ?: 0, true
			)
		);
		$form->add( new \IPS\Helpers\Form\Number( 'teamspeak_upload_quota', $serverInfo['teamspeak_upload_quota'] ?: 0, true ) );

		$form->addTab( 'teamspeak_anti_flood' );
		$form->add(
			new \IPS\Helpers\Form\Number(
				'teamspeak_antiflood_points_needed_ip_block',
				$serverInfo['teamspeak_antiflood_points_needed_ip_block'] ?: 0, true
			)
		);
		$form->add(
			new \IPS\Helpers\Form\Number(
				'teamspeak_antiflood_points_tick_reduce', $serverInfo['teamspeak_antiflood_points_tick_reduce'] ?: 0,
				true
			)
		);
		$form->add(
			new \IPS\Helpers\Form\Number(
				'teamspeak_antiflood_points_needed_command_block',
				$serverInfo['teamspeak_antiflood_points_needed_command_block'] ?: 0, true
			)
		);

		$form->addTab( 'teamspeak_security' );
		$form->add(
			new \IPS\Helpers\Form\Number(
				'teamspeak_needed_identity_security_level',
				$serverInfo['teamspeak_needed_identity_security_level'] ?: 0, true
			)
		);

		$form->addTab( 'teamspeak_other' );
		$form->add(
			new \IPS\Helpers\Form\Select(
				'teamspeak_default_server_group', $serverInfo['teamspeak_default_server_group'] ?: 0, true,
				array( 'options' => $serverGroups )
			)
		);
		$form->add(
			new \IPS\Helpers\Form\Select(
				'teamspeak_default_channel_group', $serverInfo['teamspeak_default_channel_group'] ?: 0, true,
				array( 'options' => $channelGroups )
			)
		);
		$form->add(
			new \IPS\Helpers\Form\Select(
				'teamspeak_default_channel_admin_group', $serverInfo['teamspeak_default_channel_admin_group'] ?: 0,
				true, array( 'options' => $channelGroups )
			)
		);

		if ( $values = $form->values() )
		{
			if ( $tsServer->updateServerInfo( $values ) )
			{
				\IPS\Output::i()->redirect(
					\IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=server', 'admin' ),
					'teamspeak_updated_server'
				);
			}

			\IPS\Output::i()->error( 'teamspeak_update_server_failed', '4P101/2' );
		}

		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_server_title' );
		\IPS\Output::i()->output = $form;
	}
}