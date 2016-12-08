<?php

namespace IPS\teamspeak;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
    header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

require_once( \IPS\ROOT_PATH . '/applications/teamspeak/sources/3rd_party/TeamSpeakAdmin.php' );

class _Api
{
    /**
     * @var static
     */
    protected static $instance;

    /**
     * @var \TeamSpeakAdmin
     */
    protected $tsInstance;

    protected $settings;

    /**
     * Builds up the connection to the TS server.
     *
     * @param bool $login
     */
    public function __construct( $login )
    {
        $this->settings = \IPS\Settings::i();

        $this->tsInstance = $this->createTeamspeakInstance( $login );
    }

    /**
     * Get instance of this class.
     *
     * @param bool $login
     * @return static
     */
    public static function getInstance( $login )
    {
        if ( static::$instance === null )
        {
            static::$instance = new static( $login );
        }

        return static::$instance;
    }

    /**
     * Get \TeamSpeakAdmin instance or throw exception if (for some reason) it is not available.
     *
     * @return \TeamSpeakAdmin
     * @throws \IPS\teamspeak\Exception\ConnectionException
     */
    public function getTeamspeakInstance()
    {
        if ( $this->tsInstance instanceof \TeamSpeakAdmin )
        {
            return $this->tsInstance;
        }

        throw new \IPS\teamspeak\Exception\ConnectionException();
    }

    /**
     * Create TeamSpeak Instance.
     *
     * @param bool $login
     * @return \TeamSpeakAdmin
     */
    protected function createTeamspeakInstance( $login )
    {
        $config = [
            'host' => $this->settings->teamspeak_server_ip,
            'username' => $this->settings->teamspeak_query_admin,
            'password' => $this->settings->teamspeak_query_password,
            'query_port' => $this->settings->teamspeak_query_port,
        ];

        try
        {
            return $this->connect( $config['host'], $config['query_port'], $config['username'], $config['password'], $login );
        }
        catch ( \IPS\teamspeak\Exception\ConnectionException $e )
        {
            \IPS\Log::log( $e, 'teamspeak_connect' );
        }
        catch ( \Exception $e )
        {
            \IPS\Log::log( $e, 'teamspeak_connect_2' );
        }

        return null;
    }

    /**
     * Connect to the TS server.
     *
     * @param string $host Hostname/IP
     * @param int $qPort Query Port
     * @param string $username Server admin name
     * @param string $password Server admin password
     * @param bool $login
     * @param int $timeout Connection timeout
     * @return \TeamSpeakAdmin
     * @throws \IPS\teamspeak\Exception\ConnectionException
     */
    protected function connect( $host, $qPort, $username, $password, $login = true, $timeout = 2 )
    {
        $ts = new \TeamSpeakAdmin( $host, $qPort, $timeout );

        if ( $ts->succeeded( $e = $ts->connect() ) )
        {
            if ( $login )
            {
                if ( !$ts->succeeded( $e = $ts->login( $username, $password ) ) )
                {
                    throw new \IPS\teamspeak\Exception\ConnectionException( \IPS\teamspeak\Api\Util::arrayToString( $e['errors'] ) );
                }
            }

            if ( $ts->succeeded( $e = $ts->selectServer( $this->settings->teamspeak_virtual_port ) ) )
            {
                if ( !$login )
                {
                    return $ts;
                }

                if ( $ts->succeeded( $e = $ts->setName( $this->settings->teamspeak_query_nickname ?: mt_rand( 10, 1000 ) ) ) )
                {
                    return $ts;
                }
            }
        }

        throw new \IPS\teamspeak\Exception\ConnectionException( \IPS\teamspeak\Api\Util::arrayToString( $e['errors'] ) );
    }

    protected function __clone()
    {

    }
}