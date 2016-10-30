<?php

namespace IPS\teamspeak\modules\admin\members;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

use IPS\Application;
use IPS\DateTime;
use IPS\Db;
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
		Application::load( 'teamspeak' )->isConfigured();
	}

	/**
	 * Display display with all UUIDs that has been registered.
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

		/* Root buttons */
		$table->rootButtons = array(
			'resync' => array(
				'icon' => 'refresh',
				'title' => 'teamspeak_resync_all',
				'link' => Url::internal(
					'app=teamspeak&module=members&controller=members&do=resyncAllMembers'
				),
				'data' => array( 'confirm' => '' )
			)
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

	/**
	 * Remove given UUID.
	 *
	 * @return void
	 */
	protected function delete()
	{
		/* Check if we have an ID */
		$id = Request::i()->id;

		if ( !$id )
		{
			Output::i()->error( 'teamspeak_id_missing', '3P100/1' );
		}

		$uuid = Uuid::load( $id );
		$uuid->delete();

		/* Redirect back to the table with a message that the UUID has been removed */
		Output::i()->redirect(
			Url::internal( 'app=teamspeak&module=members&controller=members' ), 'teamspeak_member_deleted'
		);
	}

	/**
	 * Re-sync given UUID.
	 *
	 * @return void
	 */
	protected function resync()
	{
		/* Check if we have an ID */
		$id = Request::i()->id;

		if ( !$id )
		{
			Output::i()->error( 'teamspeak_id_missing', '3P100/2' );
		}

		$uuid = Uuid::load( $id );

		$tsMember = TsMember::i();
		if ( !$tsMember->resyncGroups( Member::load( $uuid->member_id ), $uuid->uuid ) )
		{
			Output::i()->error( 'teamspeak_resync_groups_failed', '4P100/1' );
		}

		/* Redirect back to the table with a message that the UUID has been re-synced */
		Output::i()->redirect(
			Url::internal( 'app=teamspeak&module=members&controller=members' ), 'teamspeak_member_resynced'
		);
	}

	/**
	 * Re-sync all UUIDs of given member.
	 *
	 * @return void
	 */
	protected function resyncAll()
	{
		/* Check if we have an ID */
		$id = Request::i()->id;

		if ( !$id )
		{
			Output::i()->error( 'teamspeak_id_missing', '3P100/4' );
		}

		$member = Member::load( $id );
		$tsMember = TsMember::i();

		if ( !$tsMember->resyncGroupsAllUuids( $member ) )
		{
			Output::i()->error( 'teamspeak_resync_groups_failed', '4P100/2' );
		}
		/* Redirect back to the member form with a message that the UUIDs have been re-synced */
		Output::i()->redirect(
			Url::internal( 'app=core&module=members&controller=members&do=edit&id=' . $member->member_id ),
			'teamspeak_member_resynced'
		);
	}

	/**
	 * Re-sync all UUIDs of all members.
	 *
	 * @return void
	 */
	protected function resyncAllMembers()
	{
		$tsMember = TsMember::i();

		try
		{
			/* Get the members who have a UUID set */
			foreach ( Db::i()->select( 's_member_id, s_uuid', 'teamspeak_member_sync' ) as $info )
			{
				try
				{
					$member = Member::load( $info['s_member_id'] );
				}
				catch ( \OutOfRangeException $e )
				{
					continue;
				}

				$tsMember->resyncGroups( $member, $info['s_uuid'] );
			}
		}
		catch ( \Exception $e )
		{
			Output::i()->error( $e->getMessage(), '4P100/3' );
		}

		/* Redirect back to the table with a message that the UUIDs have been re-synced */
		Output::i()->redirect(
			Url::internal( 'app=teamspeak&module=members&controller=members' ), 'teamspeak_members_resynced'
		);
	}
}