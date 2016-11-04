<?php

namespace IPS\teamspeak\modules\admin\groups;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

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
		\IPS\Dispatcher::i()->checkAcpPermission( 'servergroups_manage' );
		parent::execute();
		\IPS\Application::load( 'teamspeak' )->isConfigured();
	}

	/**
	 * Show table of server groups.
	 *
	 * @return    void
	 */
	protected function manage()
	{
		/* Get server groups array */
		$tsGroup = \IPS\teamspeak\Api\Group::i();
		$serverGroups = $tsGroup->getServerGroups( $tsGroup->getInstance(), false, false, true );

		/* Create the table */
		$table = new \IPS\Helpers\Table\Custom( $serverGroups, \IPS\Http\Url::internal( 'app=teamspeak&module=groups&controller=servergroups' ) );
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
			'name' => \IPS\Helpers\Table\SEARCH_CONTAINS_TEXT
		);

		/* Root buttons */
		$table->rootButtons = array(
			'add' => array(
				'icon' => 'plus',
				'title' => 'add',
				'link' => \IPS\Url::internal( 'app=teamspeak&module=groups&controller=servergroups&do=add' ),
				'data' => array(
					'ipsdialog' => '',
					'ipsdialog-modal' => 'true',
					'ipsdialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_add_servergroup_title' )
				)
			)
		);

		/* Row buttons */
		$table->rowButtons = function ( $row )
		{
			$return['edit'] = array(
				'icon' => 'pencil',
				'title' => 'edit',
				'link' => \IPS\Http\Url::internal( 'app=teamspeak&module=groups&controller=servergroups&do=edit&id=' ) .
					$row['sgid']
			);

			$return['copy'] = array(
				'icon' => 'copy',
				'title' => 'copy',
				'link' => \IPS\Http\Url::internal( 'app=teamspeak&module=groups&controller=servergroups&do=copy&id=' ) .
					$row['sgid'],
				'data' => array(
					'ipsdialog' => '',
					'ipsdialog-modal' => 'true',
					'ipsdialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_copy_servergroup_title' )
				)
			);

			$return['delete'] = array(
				'icon' => 'times-circle',
				'title' => 'delete',
				'link' => \IPS\Http\Url::internal( 'app=teamspeak&module=groups&controller=servergroups&do=delete&id=' ) .
					$row['sgid'],
				'data' => array(
					'ipsdialog' => '',
					'ipsdialog-modal' => 'true',
					'ipsdialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_delete_servergroup_title' )
				)
			);

			return $return;
		};

		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_server_groups_title' );
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'global', 'core' )->block( 'title', (string) $table );
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
			\IPS\teamspeak\Api\Group::TYPE_REGULAR => 'Regular Group',
			\IPS\teamspeak\Api\Group::TYPE_TEMPLATE => 'Template Group',
			\IPS\teamspeak\Api\Group::TYPE_SERVERQUERY => 'ServerQuery Group',
		);

		/* Build form for adding a server group */
		$form = new \IPS\Helpers\Form;
		$form->add( new \IPS\Helpers\Form\Text( 'teamspeak_servergroup_name', null, true ) );
		$form->add( new \IPS\Helpers\Form\Select( 'teamspeak_servergroup_target_type', \IPS\teamspeak\Api\Group::TYPE_REGULAR, true, array( 'options' => $types ) ) );

		if ( $values = $form->values() )
		{
			try
			{
				$group = \IPS\teamspeak\Api\Group::i();
				$group->addServerGroup( $values['teamspeak_servergroup_name'], $values['teamspeak_servergroup_target_type'] );

				\IPS\Output::i()->redirect(
					\IPS\Http\Url::internal( 'app=teamspeak&module=groups&controller=servergroups' ), 'teamspeak_servergroup_added'
				);
			}
			catch ( \Exception $e )
			{
				\IPS\Output::i()->error( $e->getMessage(), '4P104/1' );
			}
		}

		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_add_servergroup_title' );
		\IPS\Output::i()->output = $form;
	}

	/**
	 * Edit given server group.
	 *
	 * @return void
	 */
	protected function edit()
	{
		/* Check if we have an ID */
		$id = \IPS\Request::i()->id;

		if ( !$id )
		{
			\IPS\Output::i()->error( 'teamspeak_id_missing', '3P104/1' );
		}

		/* Get permission class */
		$permissions = \IPS\teamspeak\Api\Permission::i();

		/* Build form for editing the server group */
		$form = new Form;
		$permissions->buildServerGroupPermissionForm( $form, $id );

		if ( $values = $form->values() )
		{
			try
			{
				$permissions->updateServerGroupPermissionsFromFormValues( $values, $id );

				\IPS\Output::i()->redirect(
					\IPS\Http\Url::internal( 'app=teamspeak&module=groups&controller=servergroups' ), 'teamspeak_servergroup_edited'
				);
			}
			catch ( \Exception $e )
			{
				\IPS\Output::i()->error( $e->getMessage(), '4P104/2' );
			}
		}

		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_edit_servergroup_title' );
		\IPS\Output::i()->output = $form;
	}

	/**
	 * Delete given server group.
	 *
	 * @return void
	 */
	protected function delete()
	{
		/* Check if we have an ID */
		$id = \IPS\Request::i()->id;

		if ( !$id )
		{
			\IPS\Output::i()->error( 'teamspeak_id_missing', '3P104/2' );
		}

		/* Get Group class */
		$group = \IPS\teamspeak\Api\Group::i();

		/* Build form for editing the server group */
		$form = new \IPS\Helpers\Form;
		$form->add( new \IPS\Helpers\Form\YesNo( 'teamspeak_force_delete', 0 ) );

		if ( $values = $form->values() )
		{
			try
			{
				$group->deleteServerGroup( $id, intval( $values['teamspeak_force_delete'] ) );

				\IPS\Output::i()->redirect(
					\IPS\Http\Url::internal( 'app=teamspeak&module=groups&controller=servergroups' ), 'teamspeak_servergroup_deleted'
				);
			}
			catch ( \Exception $e )
			{
				\IPS\Output::i()->error( $e->getMessage(), '4P104/3' );
			}
		}

		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_delete_servergroup_title' );
		\IPS\Output::i()->output = $form;
	}

	/**
	 *  Copy given server group.
	 *
	 * @return void
	 */
	protected function copy()
	{
		/* Check if we have an ID */
		$id = \IPS\Request::i()->id;

		if ( !$id )
		{
			\IPS\Output::i()->error( 'teamspeak_id_missing', '3P104/4' );
		}

		/* Get Group class */
		$group = \IPS\teamspeak\Api\Group::i();
		$serverGroups = $group->getServerGroups( $group->getInstance(), true, false, true );

		$serverGroups[0] = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_new_group' );
		ksort( $serverGroups, SORT_ASC );

		$types = array(
			\IPS\teamspeak\Api\Group::TYPE_REGULAR => 'Regular Group',
			\IPS\teamspeak\Api\Group::TYPE_TEMPLATE => 'Template Group',
			\IPS\teamspeak\Api\Group::TYPE_SERVERQUERY => 'ServerQuery Group',
		);

		$defaultName = $serverGroups[$id] . ' (Copy)';

		$form = new \IPS\Helpers\Form;
		$form->add( new \IPS\Helpers\Form\Text( 'teamspeak_servergroup_name', $defaultName, true ) );
		$form->add( new \IPS\Helpers\Form\Select( 'teamspeak_servergroup_target_group', 0, true, array( 'options' => $serverGroups ) ) );
		$form->add( new \IPS\Helpers\Form\Select( 'teamspeak_servergroup_target_type', 1, true, array( 'options' => $types ) ) );

		if ( $values = $form->values() )
		{
			try
			{
				$group->copyServerGroup( $id, $values['teamspeak_servergroup_name'], $values['teamspeak_servergroup_target_type'], $values['teamspeak_servergroup_target_group'] );

				\IPS\Output::i()->redirect(
					\IPS\Http\Url::internal( 'app=teamspeak&module=groups&controller=servergroups' ), 'teamspeak_servergroup_copied'
				);
			}
			catch ( \Exception $e )
			{
				\IPS\Output::i()->error( $e->getMessage(), '4P104/5' );
			}
		}

		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_copy_servergroup_title' );
		\IPS\Output::i()->output = $form;
	}
}