<?php

namespace IPS\teamspeak\Api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
    header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

abstract class _AbstractConnection
{
    /**
     * @var \TeamSpeakAdmin
     */
    protected $instance;

    /**
     * Set \TeamSpeakAdmin instance.
     *
     * @param bool $login
     * @param bool $outputError
     * @throws \IPS\teamspeak\Exception\ConnectionException
     */
    public function __construct( $login = true, $outputError = true )
    {
        $api = \IPS\teamspeak\Api::getInstance( $login );

        try
        {
            $this->instance = $api->getTeamspeakInstance();
        }
        catch ( \IPS\teamspeak\Exception\ConnectionException $e )
        {
            if ( $outputError )
            {
                \IPS\Output::i()->error( 'teamspeak_connection_error', '3P108/1' );
                return;
            }

            throw $e;
        }
    }
}