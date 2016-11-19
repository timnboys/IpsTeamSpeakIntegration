<?php

namespace IPS\teamspeak\modules\admin\teamspeak;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

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
		\IPS\Dispatcher::i()->checkAcpPermission( 'clients_manage' );
		parent::execute();
		\IPS\Application::load( 'teamspeak' )->isConfigured();
	}

	/**
	 * Display table containing all clients that are currently connected to the TeamSpeak server.
	 *
	 * @return    void
	 */
	protected function manage()
	{
		/* Get client list */
		$clientList = \IPS\teamspeak\Api\Client::i()->getClientList();

		/* Create the table */
		$table = new \IPS\Helpers\Table\Custom( $clientList, \IPS\Http\Url::internal( "app=teamspeak&module=teamspeak&controller=clients" ) );
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
			'client_nickname' => \IPS\Helpers\Table\SEARCH_CONTAINS_TEXT
		);

		/* Root buttons */
		$table->rootButtons = array(
			'masspoke' => array(
				'icon' => 'comments',
				'title' => 'teamspeak_masspoke',
				'link' => \IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=clients&do=masspoke' ),
				'data' => array(
					'ipsdialog' => '',
					'ipsdialog-modal' => 'true',
					'ipsdialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_masspoke_title' )
				)
			)
		);

		/* Row buttons */
		$table->rowButtons = function ( $row )
		{
			$return['kick'] = array(
				'icon' => 'crosshairs',
				'title' => 'teamspeak_kick',
				'link' => \IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=clients&do=kick&id=' ) .
					$row['clid'],
				'data' => array(
					'ipsdialog' => '',
					'ipsdialog-modal' => 'true',
					'ipsdialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_kick_title' )
				)
			);

			$return['poke'] = array(
				'icon' => 'comment',
				'title' => 'teamspeak_poke',
				'link' => \IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=clients&do=poke&id=' ) .
					$row['clid'],
				'data' => array(
					'ipsdialog' => '',
					'ipsdialog-modal' => 'true',
					'ipsdialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_poke_title' )
				)
			);

			$return['ban'] = array(
				'icon' => 'ban',
				'title' => 'teamspeak_ban',
				'link' => \IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=clients&do=ban&id=' ) .
					$row['clid'],
				'data' => array(
					'ipsdialog' => '',
					'ipsdialog-modal' => 'true',
					'ipsdialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_ban_title' )
				)
			);

			return $return;
		};

		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_clients_title' );
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'global', 'core' )->block( 'title', (string) $table );
	}

	/**
	 * Kick client.
	 *
	 * @return void
	 */
	protected function kick()
	{
		/* Check if we have an ID */
		$clientId = \IPS\Request::i()->id;

		if ( !$clientId )
		{
			\IPS\Output::i()->error( 'teamspeak_id_missing', '3P101/1' );
		}

		/* Build form for the kick message */
		$form = new \IPS\Helpers\Form( 'teamspeak_kick', 'teamspeak_kick' );
		$form->add( new \IPS\Helpers\Form\Text( 'teamspeak_kick_message' ) );

		if ( $values = $form->values() )
		{
			$client = \IPS\teamspeak\Api\Client::i();

			try
			{
				/* Kick client with given message */
				$client->kick( $clientId, $values['teamspeak_kick_message'] );
			}
			catch ( \Exception $e )
			{
				\IPS\Output::i()->error( $e->getMessage(), '4P104/1' );
			}

			/* Redirect back to the table and display a message that the client has been kicked */
			\IPS\Output::i()->redirect(
				\IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=clients' ), 'teamspeak_client_kicked'
			);
		}

		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_kick_title' );
		\IPS\Output::i()->output = $form;
	}

	/**
	 * Poke client.
	 *
	 * @return void
	 */
	protected function poke()
	{
		/* Check if we have an ID */
		$clientId = \IPS\Request::i()->id;

		if ( !$clientId )
		{
			\IPS\Output::i()->error( 'teamspeak_id_missing', '3P101/2' );
		}

		/* Build form for the poke message */
		$form = new \IPS\Helpers\Form( 'teamspeak_poke', 'teamspeak_poke' );
		$form->add( new \IPS\Helpers\Form\Text( 'teamspeak_poke_message', null, true ) );

		if ( $values = $form->values() )
		{
			$client = \IPS\teamspeak\Api\Client::i();

			try
			{
				/* Poke client with given message */
				$client->poke( $clientId, $values['teamspeak_poke_message'] );
			}
			catch ( \Exception $e )
			{
				\IPS\Output::i()->error( $e->getMessage(), '4P104/2' );
			}

			/* Redirect back to the table and display a message that the client has been poked */
			\IPS\Output::i()->redirect(
				\IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=clients' ), 'teamspeak_client_poked'
			);
		}

		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_poke_title' );
		\IPS\Output::i()->output = $form;
	}

	/**
	 * Ban client.
	 *
	 * @return void
	 */
	protected function ban()
	{
		/* Check if we have an ID */
		$clientId = \IPS\Request::i()->id;

		if ( !$clientId )
		{
			\IPS\Output::i()->error( 'teamspeak_id_missing', '3P101/3' );
		}

		/* Build form for the poke message */
		$form = new \IPS\Helpers\Form( 'teamspeak_ban', 'teamspeak_ban' );
		$form->add( new \IPS\Helpers\Form\Text( 'teamspeak_ban_message', null, true ) );
		$form->add(
			new \IPS\Helpers\Form\Date(
				'teamspeak_ban_date', time() + 24*60*60, TRUE, array( 'unlimited' => 0, 'unlimitedLang' => 'teamspeak_indefinite', 'min' => \IPS\DateTime::ts( time() ) )
			)
		);

		if ( $values = $form->values() )
		{
			$client = \IPS\teamspeak\Api\Client::i();

			try
			{
				/* Ban client with given message */
				$client->ban( $clientId, $values['teamspeak_ban_date'], $values['teamspeak_ban_message'] );
			}
			catch ( \Exception $e )
			{
				\IPS\Output::i()->error( $e->getMessage(), '4P104/3' );
			}

			/* Redirect back to the table and display a message that the client has been banned */
			\IPS\Output::i()->redirect(
				\IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=clients' ), 'teamspeak_client_banned'
			);
		}

		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_ban_title' );
		\IPS\Output::i()->output = $form;
	}

	/**
	 * Mass poke.
	 *
	 * @return void
	 */
	protected function masspoke()
	{
		/* Get Server Groups */
		$serverGroups = \IPS\teamspeak\Api\Group::getCachedServerGroups( true, false );
		$tsInstance = null;

		if ( is_null( $serverGroups ) )
		{
			$groupClass = \IPS\teamspeak\Api\Group::i();
			$serverGroups = $groupClass->getServerGroups( true, false );
			$tsInstance = $groupClass->getInstance(); // Re-use the already established connection.
		}

		/* Build form for the poke message */
		$form = new \IPS\Helpers\Form( 'teamspeak_poke', 'teamspeak_poke' );
		$form->add( new \IPS\Helpers\Form\Text( 'teamspeak_poke_message', null, true ) );
		$form->add( new \IPS\Helpers\Form\Select( 'teamspeak_poke_groups', -1, true, array( 'options' => $serverGroups, 'multiple' => true, 'unlimited' => -1 ) ) );

		if ( $values = $form->values() )
		{

			try
			{
				/* Get client class */
				$client = \IPS\teamspeak\Api\Client::i( $tsInstance );

				/* Poke client with given message */
				$client->masspoke( $values['teamspeak_poke_message'], $values['teamspeak_poke_groups'] );
			}
			catch ( \Exception $e )
			{
				\IPS\Output::i()->error( $e->getMessage(), '4P104/4' );
			}

			/* Redirect back to the table and display a message that the client has been poked */
			\IPS\Output::i()->redirect(
				\IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=clients' ), 'teamspeak_client_masspoked'
			);
		}

		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_masspoke_title' );
		\IPS\Output::i()->output = $form;
	}
}