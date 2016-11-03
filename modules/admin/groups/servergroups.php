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
 * servergroups
 */
class _servergroups extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return    void
	 */
	public function execute()
	{
		Dispatcher::i()->checkAcpPermission( 'servergroups_manage' );
		parent::execute();
		Application::load( 'teamspeak' )->isConfigured();
	}

	/**
	 * Show table of server groups.
	 *
	 * @return    void
	 */
	protected function manage()
	{
		/* Get server groups array */
		$tsGroup = Group::i();
		$serverGroups = $tsGroup->getServerGroups( $tsGroup->getInstance(), false, false, true );

		/* Create the table */
		$table = new Table\Custom( $serverGroups, Url::internal( 'app=teamspeak&module=groups&controller=servergroups' ) );
		$table->langPrefix = 'teamspeak_servergroup_';

		/* Column stuff */
		$table->include = array( 'sgid', 'name' );
		$table->mainColumn = 'name';

		/* Sort stuff */
		$table->sortBy = $table->sortBy ?: 'sgid';
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
				'link' => Url::internal( 'app=teamspeak&module=groups&controller=servergroups&do=add' ),
				'data' => array(
					'ipsdialog' => '',
					'ipsdialog-modal' => 'true',
					'ipsdialog-title' => Member::loggedIn()->language()->addToStack( 'teamspeak_add_servergroup_title' )
				)
			)
		);

		/* Row buttons */
		$table->rowButtons = function ( $row )
		{
			$return['edit'] = array(
				'icon' => 'pencil',
				'title' => 'edit',
				'link' => Url::internal( 'app=teamspeak&module=groups&controller=servergroups&do=edit&id=' ) .
					$row['sgid']
			);

			$return['copy'] = array(
				'icon' => 'copy',
				'title' => 'copy',
				'link' => Url::internal( 'app=teamspeak&module=groups&controller=servergroups&do=copy&id=' ) .
					$row['sgid'],
				'data' => array(
					'ipsdialog' => '',
					'ipsdialog-modal' => 'true',
					'ipsdialog-title' => Member::loggedIn()->language()->addToStack( 'teamspeak_copy_servergroup_title' )
				)
			);

			$return['delete'] = array(
				'icon' => 'times-circle',
				'title' => 'delete',
				'link' => Url::internal( 'app=teamspeak&module=groups&controller=servergroups&do=delete&id=' ) .
					$row['sgid'],
				'data' => array(
					'ipsdialog' => '',
					'ipsdialog-modal' => 'true',
					'ipsdialog-title' => Member::loggedIn()->language()->addToStack( 'teamspeak_delete_servergroup_title' )
				)
			);

			return $return;
		};

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_server_groups_title' );
		Output::i()->output = Theme::i()->getTemplate( 'global', 'core' )->block( 'title', (string) $table );
	}

	/**
	 * Add a server group.
	 *
	 * @return void
	 */
	protected function add()
	{
		/* Set Group Types */
		$types = array(
			Group::TYPE_REGULAR => 'Regular Group',
			Group::TYPE_TEMPLATE => 'Template Group',
			Group::TYPE_SERVERQUERY => 'ServerQuery Group',
		);

		/* Build form for adding a server group */
		$form = new Form;
		$form->add( new Form\Text( 'teamspeak_servergroup_name', null, true ) );
		$form->add( new Form\Select( 'teamspeak_servergroup_target_type', Group::TYPE_REGULAR, true, array( 'options' => $types ) ) );

		if ( $values = $form->values() )
		{
			try
			{
				$group = Group::i();
				$group->addServerGroup( $values['teamspeak_servergroup_name'], $values['teamspeak_servergroup_target_type'] );

				Output::i()->redirect(
					Url::internal( 'app=teamspeak&module=groups&controller=servergroups' ), 'teamspeak_servergroup_added'
				);
			}
			catch ( \Exception $e )
			{
				Output::i()->error( $e->getMessage(), '4P104/1' );
			}
		}

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_add_servergroup_title' );
		Output::i()->output = $form;
	}

	/**
	 * Edit given server group.
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
		$permissions->buildServerGroupPermissionForm( $form, $id );

		if ( $values = $form->values() )
		{
			try
			{
				$permissions->updateServerGroupPermissionsFromFormValues( $values, $id );

				Output::i()->redirect(
					Url::internal( 'app=teamspeak&module=groups&controller=servergroups' ), 'teamspeak_servergroup_edited'
				);
			}
			catch ( \Exception $e )
			{
				Output::i()->error( $e->getMessage(), '4P104/2' );
			}
		}

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_edit_servergroup_title' );
		Output::i()->output = $form;
	}

	/**
	 * Delete given server group.
	 *
	 * @return void
	 */
	protected function delete()
	{
		/* Check if we have an ID */
		$id = Request::i()->id;

		if ( !$id )
		{
			Output::i()->error( 'teamspeak_id_missing', '3P104/2' );
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
				$group->deleteServerGroup( $id, intval( $values['teamspeak_force_delete'] ) );

				Output::i()->redirect(
					Url::internal( 'app=teamspeak&module=groups&controller=servergroups' ), 'teamspeak_servergroup_deleted'
				);
			}
			catch ( \Exception $e )
			{
				Output::i()->error( $e->getMessage(), '4P104/3' );
			}
		}

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_delete_servergroup_title' );
		Output::i()->output = $form;
	}

	/**
	 *  Copy given server group.
	 *
	 * @return void
	 */
	protected function copy()
	{
		/* Check if we have an ID */
		$id = Request::i()->id;

		if ( !$id )
		{
			Output::i()->error( 'teamspeak_id_missing', '3P104/4' );
		}

		/* Get Group class */
		$group = Group::i();
		$serverGroups = $group->getServerGroups( $group->getInstance(), true, false, true );

		$serverGroups[0] = Member::loggedIn()->language()->addToStack( 'teamspeak_new_group' );
		ksort( $serverGroups, SORT_ASC );

		$types = array(
			Group::TYPE_REGULAR => 'Regular Group',
			Group::TYPE_TEMPLATE => 'Template Group',
			Group::TYPE_SERVERQUERY => 'ServerQuery Group',
		);

		$defaultName = $serverGroups[$id] . ' (Copy)';

		$form = new Form;
		$form->add( new Form\Text( 'teamspeak_servergroup_name', $defaultName, true ) );
		$form->add( new Form\Select( 'teamspeak_servergroup_target_group', 0, true, array( 'options' => $serverGroups ) ) );
		$form->add( new Form\Select( 'teamspeak_servergroup_target_type', 1, true, array( 'options' => $types ) ) );

		if ( $values = $form->values() )
		{
			try
			{
				$group->copyServerGroup( $id, $values['teamspeak_servergroup_name'], $values['teamspeak_servergroup_target_type'], $values['teamspeak_servergroup_target_group'] );

				Output::i()->redirect(
					Url::internal( 'app=teamspeak&module=groups&controller=servergroups' ), 'teamspeak_servergroup_copied'
				);
			}
			catch ( \Exception $e )
			{
				Output::i()->error( $e->getMessage(), '4P104/5' );
			}
		}

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_copy_servergroup_title' );
		Output::i()->output = $form;
	}
}