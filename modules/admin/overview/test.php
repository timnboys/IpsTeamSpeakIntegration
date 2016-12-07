<?php

namespace IPS\teamspeak\modules\admin\overview;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
    header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * test
 */
class _test extends \IPS\Dispatcher\Controller
{
    /**
     * Execute
     *
     * @return    void
     */
    public function execute()
    {
        \IPS\Dispatcher::i()->checkAcpPermission( 'test_manage' );
        parent::execute();
        \IPS\Application::load( 'teamspeak' )->isConfigured();
    }

    /**
     * Test connection and return the results.
     *
     * @return    void
     */
    protected function manage()
    {
        $ports = [
            (int) \IPS\Settings::i()->teamspeak_query_port => false,
            (int) \IPS\Settings::i()->teamspeak_file_transfer_port => false,
        ];

        /* Check if needed ports are open */
        foreach ( $ports as $port => &$success )
        {
            if ( \IPS\teamspeak\Api\Server::isPortOpen( $port, \IPS\Settings::i()->teamspeak_server_ip ) )
            {
                $success = true;
                continue;
            }

            $success = false;
        }

        try
        {
            $server = new \IPS\teamspeak\Api\Server();
            $ts = $server->checkConnection();
        }
        catch ( \Exception $e )
        {
            $ts = null;
        }

        /* Output */
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_test_title' );
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'test' )->test( $ts, $ports );
    }
}