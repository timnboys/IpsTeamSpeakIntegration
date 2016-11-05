<?php

namespace IPS\teamspeak;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

require_once( \IPS\ROOT_PATH . '/applications/teamspeak/sources/3rd_party/TeamSpeakAdmin.php' );

abstract class _Api
{
	/**
	 * @var \TeamSpeakAdmin
	 */
	protected $instance = null;

	protected $settings;

	/**
	 * Builds up the connection to the TS server.
	 * @param bool $login
	 */
	public function __construct( $login = true )
	{
		$this->settings = \IPS\Settings::i();

		try
		{
			if ( $this->instance === null )
			{
				$config = [
					'host' => $this->settings->teamspeak_server_ip,
					'username' => $this->settings->teamspeak_query_admin,
					'password' => $this->settings->teamspeak_query_password,
					'query_port' => $this->settings->teamspeak_query_port,
				];

				$this->instance = $this->connect( $config['host'], $config['query_port'], $config['username'], $config['password'], $login );
			}
		}
		catch ( \IPS\teamspeak\Exception\ConnectionException $e )
		{
			\IPS\Log::log( $e, 'teamspeak_connect' );
		}
		catch ( \Exception $e )
		{
			\IPS\Log::log( $e, 'teamspeak_connect_2' );
		}
	}

	/**
	 * Unset the instance after we are done with it.
	 */
	public function __destruct()
	{
		$this->logout();
		$this->instance = null;
	}

	/**
	 * Get the TS server instance.
	 *
	 * @return \TeamSpeakAdmin
	 */
	public function getInstance()
	{
		return $this->instance;
	}

	/**
	 * Return called class.
	 *
	 * @return mixed
	 */
	public static function i()
	{
		$classname = get_called_class();
		return new $classname;
	}

	/**
	 * Logout from the TS server.
	 */
	public function logout()
	{
		$this->instance->logout();
	}

	/**
	 * Connect to the TS server.
	 *
	 * @param $host Hostname/IP
	 * @param $qPort Query Port
	 * @param $username Server admin name
	 * @param $password Server admin password
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
					throw new \IPS\teamspeak\Exception\ConnectionException( $this->arrayToString( $e['errors'] ) );
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

		throw new \IPS\teamspeak\Exception\ConnectionException( $this->arrayToString( $e['errors'] ) );
	}

	protected function getReturnValue( \TeamSpeakAdmin $ts, array $data, $bool = false )
	{
		if ( $ts->succeeded( $data ) )
		{
			if ( !$bool )
			{
				return $ts->getElement( 'data', $data );
			}

			return true;
		}

		throw new \Exception( $this->arrayToString( $ts->getElement( 'errors', $data ) ) );
	}

	/**
	 * Convert the errors array (from \TeamSpeakAdmin) into a string for logging.
	 *
	 * @param array $errors
	 * @return string
	 */
	protected static function arrayToString( array $errors )
	{
		$string = '';

		foreach ( $errors as $error )
		{
			$string .= $error . ' ';
		}

		return trim( $string );
	}
}