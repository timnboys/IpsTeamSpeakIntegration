<?php

namespace IPS\teamspeak\modules\admin\teamspeak;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

use IPS\Application;
use IPS\Dispatcher;
use IPS\Helpers\Form;
use IPS\Http\Url;
use IPS\Member;
use IPS\Output;
use IPS\teamspeak\Api\Group;
use IPS\teamspeak\Api\Server;

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
		Dispatcher::i()->checkAcpPermission( 'server_manage' );
		parent::execute();
		Application::load( 'teamspeak' )->isConfigured();
	}

	/**
	 * Display form to edit the TeamSpeak server information.
	 *
	 * @return    void
	 */
	protected function manage()
	{
		$tsServer = Server::i();
		$serverGroups = Group::getServerGroups( $tsServer->getInstance(), false );
		$channelGroups = Group::getChannelGroups( $tsServer->getInstance() );
		$serverInfo = $tsServer->getServerInfo();

		if ( !$serverInfo )
		{
			Output::i()->error( 'teamspeak_serverinfo_error', '4P101/1' );
		}

		/* Build form */
		$form = new Form();
		$form->addTab( 'teamspeak_server' );
		$form->add( new Form\Text( 'teamspeak_name', $serverInfo['teamspeak_name'] ?: null, true ) );

		$form->add(
			new Form\Text( 'teamspeak_welcomemessage', $serverInfo['teamspeak_welcomemessage'] ?: null, true )
		);
		$form->add( new Form\Number( 'teamspeak_maxclients', $serverInfo['teamspeak_maxclients'] ?: 0, true ) );
		$form->add( new Form\Number( 'teamspeak_reserved_slots', $serverInfo['teamspeak_reserved_slots'] ?: 0, true ) );

		$form->addTab( 'teamspeak_transfer' );
		$form->addHeader( 'teamspeak_download' );
		$form->add(
			new Form\Number(
				'teamspeak_max_download_total_bandwidth', $serverInfo['teamspeak_max_download_total_bandwidth'] ?: 0,
				true
			)
		);
		$form->add( new Form\Number( 'teamspeak_download_quota', $serverInfo['teamspeak_download_quota'] ?: 0, true ) );
		$form->addHeader( 'teamspeak_upload' );
		$form->add(
			new Form\Number(
				'teamspeak_max_upload_total_bandwidth', $serverInfo['teamspeak_max_upload_total_bandwidth'] ?: 0, true
			)
		);
		$form->add( new Form\Number( 'teamspeak_upload_quota', $serverInfo['teamspeak_upload_quota'] ?: 0, true ) );

		$form->addTab( 'teamspeak_anti_flood' );
		$form->add(
			new Form\Number(
				'teamspeak_antiflood_points_needed_ip_block',
				$serverInfo['teamspeak_antiflood_points_needed_ip_block'] ?: 0, true
			)
		);
		$form->add(
			new Form\Number(
				'teamspeak_antiflood_points_tick_reduce', $serverInfo['teamspeak_antiflood_points_tick_reduce'] ?: 0,
				true
			)
		);
		$form->add(
			new Form\Number(
				'teamspeak_antiflood_points_needed_command_block',
				$serverInfo['teamspeak_antiflood_points_needed_command_block'] ?: 0, true
			)
		);

		$form->addTab( 'teamspeak_security' );
		$form->add(
			new Form\Number(
				'teamspeak_needed_identity_security_level',
				$serverInfo['teamspeak_needed_identity_security_level'] ?: 0, true
			)
		);

		$form->addTab( 'teamspeak_other' );
		$form->add(
			new Form\Select(
				'teamspeak_default_server_group', $serverInfo['teamspeak_default_server_group'] ?: 0, true,
				array( 'options' => $serverGroups )
			)
		);
		$form->add(
			new Form\Select(
				'teamspeak_default_channel_group', $serverInfo['teamspeak_default_channel_group'] ?: 0, true,
				array( 'options' => $channelGroups )
			)
		);
		$form->add(
			new Form\Select(
				'teamspeak_default_channel_admin_group', $serverInfo['teamspeak_default_channel_admin_group'] ?: 0,
				true, array( 'options' => $channelGroups )
			)
		);

		if ( $values = $form->values() )
		{
			if ( $tsServer->updateServerInfo( $values ) )
			{
				Output::i()->redirect(
					Url::internal( 'app=teamspeak&module=teamspeak&controller=server', 'admin' ),
					'teamspeak_updated_server'
				);
			}

			Output::i()->error( 'teamspeak_update_server_failed', '4P101/2' );
		}

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_server_title' );
		Output::i()->output = $form;
	}
}