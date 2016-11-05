<?php


namespace IPS\teamspeak\modules\admin\teamspeak;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * bans
 */
class _bans extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'bans_manage' );
		parent::execute();
		\IPS\Application::load( 'teamspeak' )->isConfigured();
	}

	/**
	 * Display table of bans.
	 *
	 * @return	void
	 */
	protected function manage()
	{
		/* Get ban list */
		$banClass = \IPS\teamspeak\Api\Ban::i();
		$banList = $banClass->getBanList();

		/* Create the table */
		$table = new \IPS\Helpers\Table\Custom( $banList, \IPS\Http\Url::internal( "app=teamspeak&module=teamspeak&controller=bans" ) );
		$table->langPrefix = 'teamspeak_';

		/* Column stuff */
		$table->include = array( 'banid', 'ip', 'uid', 'lastnickname', 'created', 'duration', 'invokername', 'reason' );
		$table->mainColumn = 'lastnickname';

		/* Sort stuff */
		$table->sortBy = $table->sortBy ?: 'banid';
		$table->sortDirection = $table->sortDirection ?: 'asc';

		/* Search */
		$table->quickSearch = 'lastnickname';
		$table->advancedSearch = array(
			'banid' => \IPS\Helpers\Table\SEARCH_NUMERIC,
			'ip' => \IPS\Helpers\Table\SEARCH_CONTAINS_TEXT,
			'uid' => \IPS\Helpers\Table\SEARCH_CONTAINS_TEXT,
			'created' => \IPS\Helpers\Table\SEARCH_DATE_RANGE,
			'lastnickname' => \IPS\Helpers\Table\SEARCH_CONTAINS_TEXT,
			'invokername' => \IPS\Helpers\Table\SEARCH_CONTAINS_TEXT,
			'reason' => \IPS\Helpers\Table\SEARCH_CONTAINS_TEXT,
		);

		/* Formatters */
		$table->parsers = array(
			'created' => function ( $val, $row )
			{
				$dateTime = \IPS\DateTime::ts( $val );

				return $dateTime->localeDate() . ' @ ' . $dateTime->localeTime();
			},
			'duration' => function ( $val, $row )
			{
				$dateTime = \IPS\DateTime::ts( $val + $row['created'] );

				return $dateTime->localeDate() . ' @ ' . $dateTime->localeTime();
			},
		);

		/* Root buttons */
		$table->rootButtons = array(
			'delete' => array(
				'icon' => 'times-circle',
				'title' => 'teamspeak_bans_delete_all',
				'link' => \IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=bans&do=deleteall' ),
				'data' => array( 'confirm' => '' )
			)
		);

		/* Row buttons */
		$table->rowButtons = function ( $row )
		{
			$return['delete'] = array(
				'icon' => 'times-circle',
				'title' => 'teamspeak_unban',
				'link' => \IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=bans&do=delete&id=' ) .
					$row['banid'],
				'data' => array( 'confirm' => '' )
			);

			return $return;
		};

		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_bans_title' );
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'global', 'core' )->block( 'title', (string) $table );
	}

	/**
	 * Delete all bans from the TS server.
	 *
	 * @return void
	 */
	protected function deleteAll()
	{
		$banClass = \IPS\teamspeak\Api\Ban::i();

		try
		{
			$banClass->deleteAll();
		}
		catch ( \Exception $e )
		{
			\IPS\Output::i()->error( $e->getMessage(), '4P107/1' );
		}

		\IPS\Output::i()->redirect(
			\IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=bans' ), 'teamspeak_deleted_all_bans'
		);
	}

	/**
	 * Delete given ban id.
	 *
	 * @return void
	 */
	protected function delete()
	{
		/* Check if we have an ID */
		$id = \IPS\Request::i()->id;

		if ( !$id )
		{
			\IPS\Output::i()->error( 'teamspeak_id_missing', '3P107/1' );
		}

		$banClass = \IPS\teamspeak\Api\Ban::i();

		try
		{
			$banClass->deleteBan( $id );
		}
		catch ( \Exception $e )
		{
			\IPS\Output::i()->error( $e->getMessage(), '4P107/2' );
		}

		\IPS\Output::i()->redirect(
			\IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=bans' ), 'teamspeak_deleted_ban'
		);
	}
}