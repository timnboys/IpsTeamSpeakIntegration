<?php

namespace IPS\teamspeak\Api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _Server extends \IPS\teamspeak\Api\AbstractConnection
{

	/**
	 * Deploy snapshot.
	 *
	 * @param mixed $data
	 * @return bool
	 * @throws \Exception
	 */
	public function deploySnapshot( $data )
	{
		return \IPS\teamspeak\Api\Util::getReturnValue( $this->instance, $this->instance->serverSnapshotDeploy( $data ), true );
	}

	/**
	 * Create new snapshot.
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function createSnapshot()
	{
		return \IPS\teamspeak\Api\Util::getReturnValue( $this->instance, $this->instance->serverSnapshotCreate() );
	}

	/**
	 * Get the TS server information.
	 *
	 * @return array|bool
	 */
	public function getServerInfo()
	{
		$serverInfo = $this->instance->serverInfo();

		if ( $this->instance->succeeded( $serverInfo ) )
		{
			return $this->convertServerInfo( $this->instance->getElement( 'data', $serverInfo ) );
		}

		\IPS\Log::log( \IPS\teamspeak\Api\Util::arrayToString( $this->instance->getElement( 'errors', $serverInfo ) ), 'teamspeak_server_info' );
		return false;
	}

	/**
	 * Update the server information.
	 *
	 * @param array $serverInfo
	 * @return bool
	 */
	public function updateServerInfo( array $serverInfo )
	{
		$serverInfo = $this->convertServerInfo( $serverInfo, true );

		return $this->instance->succeeded( $this->instance->serverEdit( $serverInfo ) );
	}

	/**
	 * Check if the given port is open.
	 *
	 * @param int $port
	 * @param string $host
	 * @return bool
	 */
	public static function isPortOpen( $port, $host )
	{
		try
		{
			$connection = fsockopen( $host, $port, $errno, $errStr, 6 );
		}
		catch ( \Exception $e )
		{
			return false;
		}

		if ( !is_resource( $connection ) )
		{
			return false;
		}

		fclose( $connection );
		return true;
	}

	/**
	 * Check if we have a connection to the TS server.
	 *
	 * @return bool
	 */
	public function checkConnection()
	{
		return true;
	}

	/**
	 * Convert the server information for either retrieving/saving.
	 *
	 * @param array $serverInfo
	 * @param bool $reverse Are we saving?
	 * @return array
	 */
	protected function convertServerInfo( array $serverInfo, $reverse = false )
	{
		$search = 'virtualserver_';
		$replace = 'teamspeak_';

		foreach ( $serverInfo as $key => $item )
		{
			$newKey = $reverse ? str_replace( $replace, $search, $key ) : str_replace( $search, $replace, $key );
			$serverInfo[$newKey] = $item;
			unset( $serverInfo[$key] );
		}

		return $serverInfo;
	}
}