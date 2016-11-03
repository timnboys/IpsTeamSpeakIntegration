<?php

namespace IPS\teamspeak\modules\admin\groups;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

use IPS\Application;
use IPS\Dispatcher;
use IPS\Helpers\Form;
use IPS\Helpers\Table;
use IPS\Http\Url;
use IPS\Member;
use IPS\Output;
use IPS\Request;
use IPS\teamspeak\Api\Group;
use IPS\teamspeak\Api\Permission;
use IPS\Theme;

/**
 * channelgroups
 */
class _channelgroups extends \IPS\Dispatcher\Controller
{	
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		Dispatcher::i()->checkAcpPermission( 'channelgroups_manage' );
		parent::execute();
		Application::load( 'teamspeak' )->isConfigured();
	}
	
	/**
	 * Show table of channel groups.
	 *
	 * @return	void
	 */
	protected function manage()
	{
		/* Get channel groups */
		$tsGroup = Group::i();
		$channelGroups = $tsGroup->getChannelGroups( $tsGroup->getInstance(), false, true );

		/* Create the table */
		$table = new Table\Custom( $channelGroups, Url::internal( 'app=teamspeak&module=groups&controller=channelgroups' ) );
		$table->langPrefix = 'teamspeak_channelgroup_';

		/* Column stuff */
		$table->include = array( 'cgid', 'name' );
		$table->mainColumn = 'name';

		/* Sort stuff */
		$table->sortBy = $table->sortBy ?: 'cgid';
		$table->sortDirection = $table->sortDirection ?: 'asc';

		/* Search */
		$table->quickSearch = 'name';
		$table->advancedSearch = array(
			'name' => Table\SEARCH_CONTAINS_TEXT
		);

		/* Root buttons */
		$table->rootButtons = array(
			'add' => array(
				'icon' => 'plus',
				'title' => 'add',
				'link' => Url::internal( 'app=teamspeak&module=groups&controller=channelgroups&do=add' ),
				'data' => array(
					'ipsdialog' => '',
					'ipsdialog-modal' => 'true',
					'ipsdialog-title' => Member::loggedIn()->language()->addToStack( 'teamspeak_add_channelgroup_title' )
				)
			)
		);

		/* Row buttons */
		$table->rowButtons = function ( $row )
		{
			$return['edit'] = array(
				'icon' => 'pencil',
				'title' => 'edit',
				'link' => Url::internal( 'app=teamspeak&module=groups&controller=channelgroups&do=edit&id=' ) .
					$row['cgid']
			);

			$return['copy'] = array(
				'icon' => 'copy',
				'title' => 'copy',
				'link' => Url::internal( 'app=teamspeak&module=groups&controller=channelgroups&do=copy&id=' ) .
					$row['cgid'],
				'data' => array(
					'ipsdialog' => '',
					'ipsdialog-modal' => 'true',
					'ipsdialog-title' => Member::loggedIn()->language()->addToStack( 'teamspeak_copy_channelgroup_title' )
				)
			);

			$return['delete'] = array(
				'icon' => 'times-circle',
				'title' => 'delete',
				'link' => Url::internal( 'app=teamspeak&module=groups&controller=channelgroups&do=delete&id=' ) .
					$row['cgid'],
				'data' => array(
					'ipsdialog' => '',
					'ipsdialog-modal' => 'true',
					'ipsdialog-title' => Member::loggedIn()->language()->addToStack( 'teamspeak_delete_channelgroup_title' )
				)
			);

			return $return;
		};

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_channel_groups_title' );
		Output::i()->output	= Theme::i()->getTemplate( 'global', 'core' )->block( 'title', (string) $table );
	}

	/**
	 * Add a channel group.
	 *
	 * @return void
	 */
	protected function add()
	{
		/* Set Group Types */
		$types = array(
			Group::TYPE_REGULAR => 'Regular Group',
			Group::TYPE_TEMPLATE => 'Template Group',
		);

		/* Build form for adding a channel group */
		$form = new Form;
		$form->add( new Form\Text( 'teamspeak_channelgroup_name', null, true ) );
		$form->add( new Form\Select( 'teamspeak_channelgroup_target_type', Group::TYPE_REGULAR, true, array( 'options' => $types ) ) );

		if ( $values = $form->values() )
		{
			try
			{
				$group = Group::i();
				$group->addChannelGroup( $values['teamspeak_channelgroup_name'], $values['teamspeak_channelgroup_target_type'] );

				Output::i()->redirect(
					Url::internal( 'app=teamspeak&module=groups&controller=channelgroups' ), 'teamspeak_channelgroup_added'
				);
			}
			catch ( \Exception $e )
			{
				Output::i()->error( $e->getMessage(), '4P105/1' );
			}
		}

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_add_channelgroup_title' );
		Output::i()->output = $form;
	}

	/**
	 * Delete given channel group.
	 *
	 * @return void
	 */
	protected function delete()
	{
		/* Check if we have an ID */
		$id = Request::i()->id;

		if ( !$id )
		{
			Output::i()->error( 'teamspeak_id_missing', '3P105/1' );
		}

		/* Get Group class */
		$group = Group::i();

		/* Build form for editing the server group */
		$form = new Form;
		$form->add( new Form\YesNo( 'teamspeak_force_delete', 0 ) );

		if ( $values = $form->values() )
		{
			try
			{
				$group->deleteChannelGroup( $id, intval( $values['teamspeak_force_delete'] ) );

				Output::i()->redirect(
					Url::internal( 'app=teamspeak&module=groups&controller=channelgroups' ), 'teamspeak_channelgroup_deleted'
				);
			}
			catch ( \Exception $e )
			{
				Output::i()->error( $e->getMessage(), '4P105/2' );
			}
		}

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_delete_channelgroup_title' );
		Output::i()->output = $form;
	}

	/**
	 *  Copy given channel group.
	 *
	 * @return void
	 */
	protected function copy()
	{
		/* Check if we have an ID */
		$id = Request::i()->id;

		if ( !$id )
		{
			Output::i()->error( 'teamspeak_id_missing', '3P105/2' );
		}

		/* Get Group class */
		$group = Group::i();
		$channelGroups = $group->getChannelGroups( $group->getInstance(), true, true );

		$channelGroups[0] = Member::loggedIn()->language()->addToStack( 'teamspeak_new_group' );
		ksort( $channelGroups, SORT_ASC );

		$types = array(
			Group::TYPE_REGULAR => 'Regular Group',
			Group::TYPE_TEMPLATE => 'Template Group',
		);

		$defaultName = $channelGroups[$id] . ' (Copy)';

		$form = new Form;
		$form->add( new Form\Text( 'teamspeak_channelgroup_name', $defaultName, true ) );
		$form->add( new Form\Select( 'teamspeak_channelgroup_target_group', 0, true, array( 'options' => $channelGroups ) ) );
		$form->add( new Form\Select( 'teamspeak_channelgroup_target_type', 1, true, array( 'options' => $types ) ) );

		if ( $values = $form->values() )
		{
			try
			{
				$group->copyChannelGroup( $id, $values['teamspeak_channelgroup_name'], $values['teamspeak_channelgroup_target_type'], $values['teamspeak_channelgroup_target_group'] );

				Output::i()->redirect(
					Url::internal( 'app=teamspeak&module=groups&controller=channelgroups' ), 'teamspeak_channelgroup_copied'
				);
			}
			catch ( \Exception $e )
			{
				Output::i()->error( $e->getMessage(), '4P105/3' );
			}
		}

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_copy_channelgroup_title' );
		Output::i()->output = $form;
	}

	/**
	 * Edit given channel group.
	 *
	 * @return void
	 */
	protected function edit()
	{
		/* Check if we have an ID */
		$id = Request::i()->id;

		if ( !$id )
		{
			Output::i()->error( 'teamspeak_id_missing', '3P104/1' );
		}

		/* Get permission class */
		$permissions = Permission::i();

		/* Build form for editing the server group */
		$form = new Form;
		$permissions->buildChannelGroupPermissionForm( $form, $id );

		if ( $values = $form->values() )
		{
			try
			{
				$permissions->updateChannelGroupPermissionsFromFormValues( $values, $id );

				Output::i()->redirect(
					Url::internal( 'app=teamspeak&module=groups&controller=channelgroups' ), 'teamspeak_channelgroup_edited'
				);
			}
			catch ( \Exception $e )
			{
				Output::i()->error( $e->getMessage(), '4P105/4' );
			}
		}

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_edit_channelgroup_title' );
		Output::i()->output = $form;
	}
}