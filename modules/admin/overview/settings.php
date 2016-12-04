<?php

namespace IPS\teamspeak\modules\admin\overview;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
    header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * settings
 */
class _settings extends \IPS\Dispatcher\Controller
{
    /**
     * Execute
     *
     * @return	void
     */
    public function execute()
    {
        \IPS\Dispatcher::i()->checkAcpPermission( 'settings_manage' );
        parent::execute();
    }

    /**
     * Display all available settings.
     *
     * @return	void
     */
    protected function manage()
    {
        /* Build Settings Form */
        $settings = new \IPS\Helpers\Form('teamspeak_settings');

        $settings->addTab('teamspeak_basic_settings');
        $settings->add( new \IPS\Helpers\Form\Text( 'teamspeak_server_ip', \IPS\Settings::i()->teamspeak_server_ip ?: null, true ) );
        $settings->add( new \IPS\Helpers\Form\Number( 'teamspeak_virtual_port', \IPS\Settings::i()->teamspeak_virtual_port ?: null, true ) );
        $settings->add( new \IPS\Helpers\Form\Number( 'teamspeak_query_port', \IPS\Settings::i()->teamspeak_query_port ?: null, true ) );
        $settings->add( new \IPS\Helpers\Form\Number( 'teamspeak_file_transfer_port', \IPS\Settings::i()->teamspeak_file_transfer_port ?: null, true ) );
        $settings->add( new \IPS\Helpers\Form\Text( 'teamspeak_query_admin', \IPS\Settings::i()->teamspeak_query_admin ?: null, true ) );
        $settings->add( new \IPS\Helpers\Form\Password( 'teamspeak_query_password', \IPS\Settings::i()->teamspeak_query_password ?: null, true ) );
        $settings->add( new \IPS\Helpers\Form\Text( 'teamspeak_query_nickname', \IPS\Settings::i()->teamspeak_query_nickname ?: null, true ) );

        $settings->addTab('teamspeak_other_settings');
        $settings->add( new \IPS\Helpers\Form\YesNo( 'teamspeak_uuid_on_register', \IPS\Settings::i()->teamspeak_uuid_on_register ?: null, false, array( 'togglesOn' => array( 'ts_uuid_register_force' ) ) ) );
        $settings->add( new \IPS\Helpers\Form\YesNo( 'teamspeak_uuid_on_register_force', \IPS\Settings::i()->teamspeak_uuid_on_register_force ?: null, false, array(), null, null, null, 'ts_uuid_register_force' ) );
        $settings->add( new \IPS\Helpers\Form\YesNo( 'teamspeak_sync_bans', \IPS\Settings::i()->teamspeak_sync_bans ?: 0 ) );
        $settings->add( new \IPS\Helpers\Form\YesNo( 'teamspeak_remove_groups', \IPS\Settings::i()->teamspeak_remove_groups ?: 0 ) );
        $settings->add( new \IPS\Helpers\Form\YesNo( 'teamspeak_remove_unlinked_groups', \IPS\Settings::i()->teamspeak_remove_unlinked_groups ?: 0 ) );
        $settings->add( new \IPS\Helpers\Form\Number( 'teamspeak_viewer_height', \IPS\Settings::i()->teamspeak_viewer_height ?: 300, true ) );

        if ( $values = $settings->values() )
        {
            $settings->saveAsSettings();
        }

        /* Output */
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('teamspeak_settings_title');
        \IPS\Output::i()->output = $settings;
    }
}