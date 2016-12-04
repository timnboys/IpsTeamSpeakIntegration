<?php

namespace IPS\teamspeak\modules\admin\teamspeak;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
    header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * snapshot
 */
class _snapshot extends \IPS\Dispatcher\Controller
{	
    /**
     * Execute
     *
     * @return	void
     */
    public function execute()
    {
        \IPS\Dispatcher::i()->checkAcpPermission( 'snapshot_manage' );
        parent::execute();
        \IPS\Application::load( 'teamspeak' )->isConfigured();
    }

    /**
     * Manage
     *
     * @return	void
     */
    protected function manage()
    {
        /* Create the table */
        $table = new \IPS\Helpers\Table\Db( 'teamspeak_server_snapshots', \IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=snapshot' ) );
        $table->langPrefix = 'teamspeak_';

        /* Column stuff */
        $table->include = array( 's_id', 's_name', 's_date' );
        $table->mainColumn = 's_id';

        /* Sort stuff */
        $table->sortBy = $table->sortBy ?: 's_id';
        $table->sortDirection = $table->sortDirection ?: 'asc';

        /* Search */
        $table->quickSearch = 's_name';
        $table->advancedSearch = array(
            's_name' => \IPS\Helpers\Table\SEARCH_CONTAINS_TEXT
        );

        /* Formatters */
        $table->parsers = array(
            's_date' => function ( $val, $row )
            {
                $date = \IPS\DateTime::ts( $val );

                return $date->localeDate();
            },
        );

        /* Root buttons */
        $table->rootButtons = array(
            'add' => array(
                'icon' => 'plus',
                'title' => 'teamspeak_snapshot_create',
                'link' => \IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=snapshot&do=add' ),
                'data' => array(
                    'ipsdialog' => '',
                    'ipsdialog-modal' => 'true',
                    'ipsdialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_snapshot_create' )
                )
            )
        );

        /* Row buttons */
        $table->rowButtons = function ( $row )
        {
            $return['deploy'] = array(
                'icon' => 'upload',
                'title' => 'teamspeak_deploy',
                'link' => \IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=snapshot&do=deploy&id=' ) .
                    $row['s_id'],
                'data' => array( 'confirm' => '' )
            );

            $return['delete'] = array(
                'icon' => 'times-circle',
                'title' => 'delete',
                'link' => \IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=snapshot&do=delete&id=' ) .
                    $row['s_id'],
                'data' => array( 'confirm' => '' )
            );

            return $return;
        };

        /* Display */
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_snapshot_title' );
        \IPS\Output::i()->output	= \IPS\Theme::i()->getTemplate( 'global', 'core' )->block( 'title', (string) $table );
    }

    /**
     * Create new snapshot.
     *
     * @return void
     */
    protected function add()
    {
        /* Build form */
        $form = new \IPS\Helpers\Form;
        $form->add( new \IPS\Helpers\Form\Text( 'teamspeak_snapshot_name', null, true ) );

        if ( $values = $form->values() )
        {
            try
            {
                $server = new \IPS\teamspeak\Api\Server();
                $snapshotData = $server->createSnapshot();

                if ( !empty( $snapshotData ) )
                {
                    $snapshot = new \IPS\teamspeak\Snapshot();
                    $snapshot->name = $values['teamspeak_snapshot_name'];
                    $snapshot->data = $snapshotData;
                    $snapshot->save();
                }
            }
            catch ( \Exception $e )
            {
                \IPS\Output::i()->error( $e->getMessage(), '4P106/1' );
            }

            \IPS\Output::i()->redirect(
                \IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=snapshot' ), 'teamspeak_snapshot_created'
            );
        }

        /* Display */
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_snapshot_create' );
        \IPS\Output::i()->output = $form;
    }

    /**
     * Delete snapshot.
     *
     * @return void
     */
    protected function delete()
    {
        /* Check if we have an ID */
        $id = \IPS\Request::i()->id;

        if ( !$id )
        {
            \IPS\Output::i()->error( 'teamspeak_id_missing', '3P106/1' );
        }

        $snapshot = \IPS\teamspeak\Snapshot::load( $id );
        $snapshot->delete();

        \IPS\Output::i()->redirect(
            \IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=snapshot' ), 'teamspeak_snapshot_deleted'
        );
    }

    /**
     * Deploy snapshot.
     *
     * @return void
     */
    protected function deploy()
    {
        /* Check if we have an ID */
        $id = \IPS\Request::i()->id;

        if ( !$id )
        {
            \IPS\Output::i()->error( 'teamspeak_id_missing', '3P106/2' );
        }

        try
        {
            $server = new \IPS\teamspeak\Api\Server();
            $snapshot = \IPS\teamspeak\Snapshot::load( $id );
            $data = $snapshot->data;
            $server->deploySnapshot( $data );
        }
        catch ( \Exception $e )
        {
            \IPS\Output::i()->error( $e->getMessage(), '4P106/2' );
        }

        \IPS\Output::i()->redirect(
            \IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=snapshot' ), 'teamspeak_snapshot_deployed'
        );
    }
}