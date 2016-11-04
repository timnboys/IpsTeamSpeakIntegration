<?php

namespace IPS\teamspeak\modules\admin\members;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

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
		\IPS\Dispatcher::i()->checkAcpPermission( 'members_manage' );
		parent::execute();
		\IPS\Application::load( 'teamspeak' )->isConfigured();
	}

	/**
	 * Display display with all UUIDs that has been registered.
	 *
	 * @return void
	 */
	protected function manage()
	{
		/* Create the table */
		$table = new \IPS\Helpers\Table\Db(
			'teamspeak_member_sync', \IPS\Http\Url::internal( 'app=teamspeak&module=members&controller=members' )
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
			's_member_id' => \IPS\Helpers\Table\SEARCH_MEMBER,
			's_date' => \IPS\Helpers\Table\SEARCH_DATE_RANGE,
			's_uuid' => \IPS\Helpers\Table\SEARCH_CONTAINS_TEXT
		);

		/* Formatters */
		$table->parsers = array(
			's_member_id' => function ( $val, $row )
			{
				try
				{
					$member = htmlentities( \IPS\Member::load( $val )->name, \IPS\HTMLENTITIES, 'UTF-8', FALSE );

					return $member;
				}
				catch ( \OutOfRangeException $e )
				{
					return '';
				}
			},
			's_date' => function ( $val, $row )
			{
				$date = \IPS\DateTime::ts( $val );

				return $date->localeDate();
			},
		);

		/* Root buttons */
		$table->rootButtons = array(
			'resync' => array(
				'icon' => 'refresh',
				'title' => 'teamspeak_resync_all',
				'link' => \IPS\Http\Url::internal(
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
				'link' => \IPS\Http\Url::internal( 'app=core&module=members&controller=members&do=edit&id=' ) .
					$row['s_member_id']
			);

			$return['delete'] = array(
				'icon' => 'times-circle',
				'title' => 'delete',
				'link' => \IPS\Http\Url::internal( 'app=teamspeak&module=members&controller=members&do=delete&id=' ) .
					$row['s_id'],
				'data' => array( 'delete' => '' ),
			);

			$return['resync'] = array(
				'icon' => 'refresh',
				'title' => 'resync',
				'link' => \IPS\Http\Url::internal( 'app=teamspeak&module=members&controller=members&do=resync&id=' ) .
					$row['s_id'],
				'data' => array( 'confirm' => '' ),
			);

			return $return;
		};

		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_members_title' );
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'global', 'core' )->block( 'title', (string) $table );
	}

	/**
	 * Remove given UUID.
	 *
	 * @return void
	 */
	protected function delete()
	{
		/* Check if we have an ID */
		$id = \IPS\Request::i()->id;

		if ( !$id )
		{
			\IPS\Output::i()->error( 'teamspeak_id_missing', '3P100/1' );
		}

		$uuid = \IPS\teamspeak\Uuid::load( $id );
		$uuid->delete();

		/* Redirect back to the table with a message that the UUID has been removed */
		\IPS\Output::i()->redirect(
			\IPS\Http\Url::internal( 'app=teamspeak&module=members&controller=members' ), 'teamspeak_member_deleted'
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
		$id = \IPS\Request::i()->id;

		if ( !$id )
		{
			\IPS\Output::i()->error( 'teamspeak_id_missing', '3P100/2' );
		}

		$uuid = \IPS\teamspeak\Uuid::load( $id );

		$tsMember = \IPS\teamspeak\Member::i();
		if ( !$tsMember->resyncGroups( \IPS\Member::load( $uuid->member_id ), $uuid->uuid ) )
		{
			\IPS\Output::i()->error( 'teamspeak_resync_groups_failed', '4P100/1' );
		}

		/* Redirect back to the table with a message that the UUID has been re-synced */
		\IPS\Output::i()->redirect(
			\IPS\Http\Url::internal( 'app=teamspeak&module=members&controller=members' ), 'teamspeak_member_resynced'
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
		$id = \IPS\Request::i()->id;

		if ( !$id )
		{
			\IPS\Output::i()->error( 'teamspeak_id_missing', '3P100/4' );
		}

		$member = \IPS\Member::load( $id );
		$tsMember = \IPS\teamspeak\Member::i();

		if ( !$tsMember->resyncGroupsAllUuids( $member ) )
		{
			\IPS\Output::i()->error( 'teamspeak_resync_groups_failed', '4P100/2' );
		}
		/* Redirect back to the member form with a message that the UUIDs have been re-synced */
		\IPS\Output::i()->redirect(
			\IPS\Http\Url::internal( 'app=core&module=members&controller=members&do=edit&id=' . $member->member_id ),
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
		$tsMember = \IPS\teamspeak\Member::i();

		try
		{
			/* Get the members who have a UUID set */
			foreach ( \IPS\Db::i()->select( 's_member_id, s_uuid', 'teamspeak_member_sync' ) as $info )
			{
				try
				{
					$member = \IPS\Member::load( $info['s_member_id'] );
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
			throw $e;
			\IPS\Output::i()->error( $e->getMessage(), '4P100/3' );
		}

		/* Redirect back to the table with a message that the UUIDs have been re-synced */
		\IPS\Output::i()->redirect(
			\IPS\Http\Url::internal( 'app=teamspeak&module=members&controller=members' ), 'teamspeak_members_resynced'
		);
	}
}