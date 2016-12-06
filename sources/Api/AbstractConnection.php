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
     */
    public function __construct( $login = true )
    {
        $api = \IPS\teamspeak\Api::getInstance( $login );

        $this->instance = $api->getTeamspeakInstance();
    }
}