<?php

namespace IPS\teamspeak\modules\admin\teamspeak;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

use IPS\Application;
use IPS\DateTime;
use IPS\Dispatcher;
use IPS\Helpers\Form;
use IPS\Helpers\Table;
use IPS\Http\Url;
use IPS\Member;
use IPS\Output;
use IPS\Request;
use IPS\teamspeak\Api\Client;
use IPS\teamspeak\Api\Group;
use IPS\Theme;

/**
 * clients
 */
class _clients extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return    void
	 */
	public function execute()
	{
		Dispatcher::i()->checkAcpPermission( 'clients_manage' );
		parent::execute();
		Application::load( 'teamspeak' )->isConfigured();
	}

	/**
	 * Display table containing all clients that are currently connected to the TeamSpeak server.
	 *
	 * @return    void
	 */
	protected function manage()
	{
		/* Get client list */
		$clientList = Client::i()->getClientList();

		/* Create the table */
		$table = new Table\Custom( $clientList, Url::internal( "app=teamspeak&module=teamspeak&controller=clients" ) );
		$table->langPrefix = 'teamspeak_';

		/* Column stuff */
		$table->include = array( 'client_nickname', 'clid' );
		$table->mainColumn = 'client_nickname';

		/* Sort stuff */
		$table->sortBy = $table->sortBy ?: 'client_nickname';
		$table->sortDirection = $table->sortDirection ?: 'asc';

		/* Search */
		$table->quickSearch = 'client_nickname';
		$table->advancedSearch = array(
			'client_nickname' => Table\SEARCH_CONTAINS_TEXT
		);

		/* Root buttons */
		$table->rootButtons = array(
			'masspoke' => array(
				'icon' => 'comments',
				'title' => 'teamspeak_masspoke',
				'link' => Url::internal( 'app=teamspeak&module=teamspeak&controller=clients&do=masspoke' ),
				'data' => array(
					'ipsdialog' => '',
					'ipsdialog-modal' => 'true',
					'ipsdialog-title' => Member::loggedIn()->language()->addToStack( 'teamspeak_masspoke_title' )
				)
			)
		);

		/* Row buttons */
		$table->rowButtons = function ( $row )
		{
			$return['kick'] = array(
				'icon' => 'crosshairs',
				'title' => 'teamspeak_kick',
				'link' => Url::internal( 'app=teamspeak&module=teamspeak&controller=clients&do=kick&id=' ) .
					$row['clid'],
				'data' => array(
					'ipsdialog' => '',
					'ipsdialog-modal' => 'true',
					'ipsdialog-title' => Member::loggedIn()->language()->addToStack( 'teamspeak_kick_title' )
				)
			);

			$return['poke'] = array(
				'icon' => 'comment',
				'title' => 'teamspeak_poke',
				'link' => Url::internal( 'app=teamspeak&module=teamspeak&controller=clients&do=poke&id=' ) .
					$row['clid'],
				'data' => array(
					'ipsdialog' => '',
					'ipsdialog-modal' => 'true',
					'ipsdialog-title' => Member::loggedIn()->language()->addToStack( 'teamspeak_poke_title' )
				)
			);

			$return['ban'] = array(
				'icon' => 'ban',
				'title' => 'teamspeak_ban',
				'link' => Url::internal( 'app=teamspeak&module=teamspeak&controller=clients&do=ban&id=' ) .
					$row['clid'],
				'data' => array(
					'ipsdialog' => '',
					'ipsdialog-modal' => 'true',
					'ipsdialog-title' => Member::loggedIn()->language()->addToStack( 'teamspeak_ban_title' )
				)
			);

			return $return;
		};

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_clients_title' );
		Output::i()->output = Theme::i()->getTemplate( 'global', 'core' )->block( 'title', (string) $table );
	}

	/**
	 * Kick client.
	 *
	 * @return void
	 */
	protected function kick()
	{
		/* Check if we have an ID */
		$clientId = Request::i()->id;

		if ( !$clientId )
		{
			Output::i()->error( 'teamspeak_id_missing', '3P101/1' );
		}

		/* Build form for the kick message */
		$form = new Form( 'teamspeak_kick', 'teamspeak_kick' );
		$form->add( new Form\Text( 'teamspeak_kick_message' ) );

		if ( $values = $form->values() )
		{
			$client = Client::i();

			try
			{
				/* Kick client with given message */
				$client->kick( $clientId, $values['teamspeak_kick_message'] );
			}
			catch ( \Exception $e )
			{
				Output::i()->error( $e->getMessage(), '4P104/1' );
			}

			/* Redirect back to the table and display a message that the client has been kicked */
			Output::i()->redirect(
				Url::internal( 'app=teamspeak&module=teamspeak&controller=clients' ), 'teamspeak_client_kicked'
			);
		}

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_kick_title' );
		Output::i()->output = $form;
	}

	/**
	 * Poke client.
	 *
	 * @return void
	 */
	protected function poke()
	{
		/* Check if we have an ID */
		$clientId = Request::i()->id;

		if ( !$clientId )
		{
			Output::i()->error( 'teamspeak_id_missing', '3P101/2' );
		}

		/* Build form for the poke message */
		$form = new Form( 'teamspeak_poke', 'teamspeak_poke' );
		$form->add( new Form\Text( 'teamspeak_poke_message', null, true ) );

		if ( $values = $form->values() )
		{
			$client = Client::i();

			try
			{
				/* Poke client with given message */
				$client->poke( $clientId, $values['teamspeak_poke_message'] );
			}
			catch ( \Exception $e )
			{
				Output::i()->error( $e->getMessage(), '4P104/2' );
			}

			/* Redirect back to the table and display a message that the client has been poked */
			Output::i()->redirect(
				Url::internal( 'app=teamspeak&module=teamspeak&controller=clients' ), 'teamspeak_client_poked'
			);
		}

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_poke_title' );
		Output::i()->output = $form;
	}

	/**
	 * Ban client.
	 *
	 * @return void
	 */
	protected function ban()
	{
		/* Check if we have an ID */
		$clientId = Request::i()->id;

		if ( !$clientId )
		{
			Output::i()->error( 'teamspeak_id_missing', '3P101/3' );
		}

		/* Build form for the poke message */
		$form = new Form( 'teamspeak_ban', 'teamspeak_ban' );
		$form->add( new Form\Text( 'teamspeak_ban_message', null, true ) );
		$form->add(
			new Form\Date(
				'teamspeak_ban_date', time() + 24*60*60, TRUE, array( 'unlimited' => 0, 'unlimitedLang' => 'teamspeak_indefinite', 'min' => DateTime::ts( time() ) )
			)
		);

		if ( $values = $form->values() )
		{
			$client = Client::i();

			try
			{
				/* Ban client with given message */
				$client->ban( $clientId, $values['teamspeak_ban_date'], $values['teamspeak_ban_message'] );
			}
			catch ( \Exception $e )
			{
				Output::i()->error( $e->getMessage(), '4P104/3' );
			}

			/* Redirect back to the table and display a message that the client has been banned */
			Output::i()->redirect(
				Url::internal( 'app=teamspeak&module=teamspeak&controller=clients' ), 'teamspeak_client_banned'
			);
		}

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_ban_title' );
		Output::i()->output = $form;
	}

	protected function masspoke()
	{
		/* Get client class */
		$client = Client::i();

		/* Get Server Groups */
		$serverGroups = Group::getServerGroups( $client->getInstance(), true, false );

		/* Build form for the poke message */
		$form = new Form( 'teamspeak_poke', 'teamspeak_poke' );
		$form->add( new Form\Text( 'teamspeak_poke_message', null, true ) );
		$form->add( new Form\Select( 'teamspeak_poke_groups', -1, true, array( 'options' => $serverGroups, 'multiple' => true, 'unlimited' => -1 ) ) );

		if ( $values = $form->values() )
		{

			try
			{
				/* Poke client with given message */
				$client->masspoke( $values['teamspeak_poke_message'], $values['teamspeak_poke_groups'] );
			}
			catch ( \Exception $e )
			{
				Output::i()->error( $e->getMessage(), '4P104/4' );
			}

			/* Redirect back to the table and display a message that the client has been poked */
			Output::i()->redirect(
				Url::internal( 'app=teamspeak&module=teamspeak&controller=clients' ), 'teamspeak_client_masspoked'
			);
		}

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_masspoke_title' );
		Output::i()->output = $form;
	}
}