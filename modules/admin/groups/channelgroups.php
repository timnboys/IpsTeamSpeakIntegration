<?php

namespace IPS\teamspeak\modules\admin\groups;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
    header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

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
        \IPS\Dispatcher::i()->checkAcpPermission( 'channelgroups_manage' );
        parent::execute();
        \IPS\Application::load( 'teamspeak' )->isConfigured();
    }

    /**
     * Show table of channel groups.
     *
     * @return	void
     */
    protected function manage()
    {
        /* Get channel groups */
        $channelGroups = \IPS\teamspeak\Api\Group::getCachedChannelGroups( false, true );

        if ( $channelGroups === null )
        {
            $groupClass = new \IPS\teamspeak\Api\Group();
            $channelGroups = $groupClass->getChannelGroups( false, true );
        }

        /* Create the table */
        $table = new \IPS\Helpers\Table\Custom( $channelGroups, \IPS\Http\Url::internal( 'app=teamspeak&module=groups&controller=channelgroups' ) );
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
            'name' => \IPS\Helpers\Table\SEARCH_CONTAINS_TEXT
        );

        /* Root buttons */
        $table->rootButtons = array(
            'add' => array(
                'icon' => 'plus',
                'title' => 'add',
                'link' => \IPS\Http\Url::internal( 'app=teamspeak&module=groups&controller=channelgroups&do=add' ),
                'data' => array(
                    'ipsdialog' => '',
                    'ipsdialog-modal' => 'true',
                    'ipsdialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_add_channelgroup_title' )
                )
            )
        );

        /* Row buttons */
        $table->rowButtons = function ( $row )
        {
            $return['edit'] = array(
                'icon' => 'pencil',
                'title' => 'edit',
                'link' => \IPS\Http\Url::internal( 'app=teamspeak&module=groups&controller=channelgroups&do=edit&id=' ) .
                    $row['cgid']
            );

            $return['copy'] = array(
                'icon' => 'copy',
                'title' => 'copy',
                'link' => \IPS\Http\Url::internal( 'app=teamspeak&module=groups&controller=channelgroups&do=copy&id=' ) .
                    $row['cgid'],
                'data' => array(
                    'ipsdialog' => '',
                    'ipsdialog-modal' => 'true',
                    'ipsdialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_copy_channelgroup_title' )
                )
            );

            $return['delete'] = array(
                'icon' => 'times-circle',
                'title' => 'delete',
                'link' => \IPS\Http\Url::internal( 'app=teamspeak&module=groups&controller=channelgroups&do=delete&id=' ) .
                    $row['cgid'],
                'data' => array(
                    'ipsdialog' => '',
                    'ipsdialog-modal' => 'true',
                    'ipsdialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_delete_channelgroup_title' )
                )
            );

            return $return;
        };

        /* Display */
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_channel_groups_title' );
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'global', 'core' )->block( 'title', (string) $table );
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
            \IPS\teamspeak\Api\Group::TYPE_REGULAR => 'Regular Group',
            \IPS\teamspeak\Api\Group::TYPE_TEMPLATE => 'Template Group',
        );

        /* Build form for adding a channel group */
        $form = new \IPS\Helpers\Form;
        $form->add( new \IPS\Helpers\Form\Text( 'teamspeak_channelgroup_name', null, true ) );
        $form->add( new \IPS\Helpers\Form\Select( 'teamspeak_channelgroup_target_type', \IPS\teamspeak\Api\Group::TYPE_REGULAR, true, array( 'options' => $types ) ) );

        if ( $values = $form->values() )
        {
            try
            {
                $group = new \IPS\teamspeak\Api\Group();
                $group->addChannelGroup( $values['teamspeak_channelgroup_name'], $values['teamspeak_channelgroup_target_type'] );

                \IPS\Output::i()->redirect(
                    \IPS\Http\Url::internal( 'app=teamspeak&module=groups&controller=channelgroups' ), 'teamspeak_channelgroup_added'
                );
            }
            catch ( \Exception $e )
            {
                \IPS\Output::i()->error( $e->getMessage(), '4P105/1' );
            }
        }

        /* Display */
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_add_channelgroup_title' );
        \IPS\Output::i()->output = $form;
    }

    /**
     * Delete given channel group.
     *
     * @return void
     */
    protected function delete()
    {
        /* Check if we have an ID */
        $id = \IPS\Request::i()->id;

        if ( !$id )
        {
            \IPS\Output::i()->error( 'teamspeak_id_missing', '3P105/1' );
        }

        /* Get Group class */
        $group = new \IPS\teamspeak\Api\Group();

        /* Build form for editing the server group */
        $form = new \IPS\Helpers\Form;
        $form->add( new \IPS\Helpers\Form\YesNo( 'teamspeak_force_delete', 0 ) );

        if ( $values = $form->values() )
        {
            try
            {
                $group->deleteChannelGroup( $id, (int) $values['teamspeak_force_delete'] );

                \IPS\Output::i()->redirect(
                    \IPS\Http\Url::internal( 'app=teamspeak&module=groups&controller=channelgroups' ), 'teamspeak_channelgroup_deleted'
                );
            }
            catch ( \Exception $e )
            {
                \IPS\Output::i()->error( $e->getMessage(), '4P105/2' );
            }
        }

        /* Display */
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_delete_channelgroup_title' );
        \IPS\Output::i()->output = $form;
    }

    /**
     *  Copy given channel group.
     *
     * @return void
     */
    protected function copy()
    {
        /* Check if we have an ID */
        $id = \IPS\Request::i()->id;

        if ( !$id )
        {
            \IPS\Output::i()->error( 'teamspeak_id_missing', '3P105/2' );
        }

        /* Get Group class */
        $groupClass = new \IPS\teamspeak\Api\Group();
        $channelGroups = $groupClass->getChannelGroups( true, true );

        $channelGroups[0] = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_new_group' );
        ksort( $channelGroups, SORT_ASC );

        $types = array(
            \IPS\teamspeak\Api\Group::TYPE_REGULAR => 'Regular Group',
            \IPS\teamspeak\Api\Group::TYPE_TEMPLATE => 'Template Group',
        );

        $defaultName = $channelGroups[$id] . ' (Copy)';

        $form = new \IPS\Helpers\Form;
        $form->add( new \IPS\Helpers\Form\Text( 'teamspeak_channelgroup_name', $defaultName, true ) );
        $form->add( new \IPS\Helpers\Form\Select( 'teamspeak_channelgroup_target_group', 0, true, array( 'options' => $channelGroups ) ) );
        $form->add( new \IPS\Helpers\Form\Select( 'teamspeak_channelgroup_target_type', 1, true, array( 'options' => $types ) ) );

        if ( $values = $form->values() )
        {
            try
            {
                $groupClass->copyChannelGroup( $id, $values['teamspeak_channelgroup_name'], $values['teamspeak_channelgroup_target_type'], $values['teamspeak_channelgroup_target_group'] );

                \IPS\Output::i()->redirect(
                    \IPS\Http\Url::internal( 'app=teamspeak&module=groups&controller=channelgroups' ), 'teamspeak_channelgroup_copied'
                );
            }
            catch ( \Exception $e )
            {
                \IPS\Output::i()->error( $e->getMessage(), '4P105/3' );
            }
        }

        /* Display */
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_copy_channelgroup_title' );
        \IPS\Output::i()->output = $form;
    }

    /**
     * Edit given channel group.
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
        $permissions = new \IPS\teamspeak\Api\Permission();

        /* Build form for editing the server group */
        $form = new \IPS\Helpers\Form;
        $permissions->buildChannelGroupPermissionForm( $form, $id );

        if ( $values = $form->values() )
        {
            try
            {
                $permissions->updateChannelGroupPermissionsFromFormValues( $values, $id );

                \IPS\Output::i()->redirect(
                    \IPS\Http\Url::internal( 'app=teamspeak&module=groups&controller=channelgroups' ), 'teamspeak_channelgroup_edited'
                );
            }
            catch ( \Exception $e )
            {
                \IPS\Output::i()->error( $e->getMessage(), '4P105/4' );
            }
        }

        /* Display */
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_edit_channelgroup_title' );
        \IPS\Output::i()->output = $form;
    }
}