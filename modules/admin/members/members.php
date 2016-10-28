<?php

namespace IPS\teamspeak\modules\admin\members;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

use IPS\DateTime;
use IPS\Dispatcher;
use IPS\Helpers\Table;
use IPS\Http\Url;
use IPS\Member;
use IPS\Output;
use IPS\Request;
use IPS\teamspeak\Member as TsMember;
use IPS\teamspeak\Uuid;
use IPS\Theme;

/**
 * members
 */
class _Members extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return    void
	 */
	public function execute()
	{
		Dispatcher::i()->checkAcpPermission( 'members_manage' );
		parent::execute();
	}

	/**
	 * Manage
	 *
	 * @return void
	 */
	protected function manage()
	{
		/* Create the table */
		$table = new Table\Db(
			'teamspeak_member_sync', Url::internal( 'app=teamspeak&module=members&controller=members' )
		);
		$table->langPrefix = 'teamspeak_table_';

		/* Joins */
		$table->joins = array(
			array(
				'select' => 'm.name',
				'from' => array( 'core_members', 'm' ),
				'where' => array( 'm.member_id=teamspeak_member_sync.s_member_id' )
			)
		);

		/* Column stuff */
		$table->include = array( 's_member_id', 's_uuid', 's_date' );
		$table->mainColumn = 's_date';

		/* Sort stuff */
		$table->sortBy = $table->sortBy ?: 's_date';
		$table->sortDirection = $table->sortDirection ?: 'desc';

		/* Search */
		$table->quickSearch = 'name';
		$table->advancedSearch = array(
			's_member_id' => Table\SEARCH_MEMBER,
			's_date' => Table\SEARCH_DATE_RANGE,
			's_uuid' => Table\SEARCH_CONTAINS_TEXT
		);

		/* Formatters */
		$table->parsers = array(
			's_member_id' => function ( $val, $row )
			{
				try
				{
					$member = htmlentities( Member::load( $val )->name, \IPS\HTMLENTITIES, 'UTF-8', FALSE );

					return $member;
				}
				catch ( \OutOfRangeException $e )
				{
					return '';
				}
			},
			's_date' => function ( $val, $row )
			{
				$date = DateTime::ts( $val );

				return $date->localeDate();
			},
		);

		/* Row buttons */
		$table->rowButtons = function ( $row )
		{
			$return['view'] = array(
				'icon' => 'search',
				'title' => 'view',
				'link' => Url::internal( 'app=core&module=members&controller=members&do=edit&id=' ) .
					$row['s_member_id']
			);

			$return['delete'] = array(
				'icon' => 'times-circle',
				'title' => 'delete',
				'link' => Url::internal( 'app=teamspeak&module=members&controller=members&do=delete&id=' ) .
					$row['s_id'],
				'data' => array( 'delete' => '' ),
			);

			$return['resync'] = array(
				'icon' => 'refresh',
				'title' => 'resync',
				'link' => Url::internal( 'app=teamspeak&module=members&controller=members&do=resync&id=' ) .
					$row['s_id'],
				'data' => array( 'confirm' => '' ),
			);

			return $return;
		};

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_members_title' );
		Output::i()->output = Theme::i()->getTemplate( 'global', 'core' )->block( 'title', (string) $table );
	}

	protected function delete()
	{
		$id = Request::i()->id;
		$uuid = Uuid::load( $id );
		$uuid->delete();

		Output::i()->redirect(
			Url::internal( 'app=teamspeak&module=members&controller=members' ), 'teamspeak_member_deleted'
		);
	}

	protected function resync()
	{
		$id = Request::i()->id;
		$uuid = Uuid::load( $id );

		$tsMember = TsMember::i();
		if ( !$tsMember->resyncGroups( Member::load( $uuid->member_id ), $uuid->uuid ) )
		{
			Output::i()->error( 'teamspeak_resync_groups_failed', '4P1001' );
		}

		Output::i()->redirect(
			Url::internal( 'app=teamspeak&module=members&controller=members' ), 'teamspeak_member_resynced'
		);
	}
}