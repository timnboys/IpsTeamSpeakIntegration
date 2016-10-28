<?php

namespace IPS\teamspeak\Api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

use IPS\Log;

class _Server extends \IPS\teamspeak\Api
{
	/**
	 * Only here for auto-complete.
	 *
	 * @return Server
	 */
	public static function i()
	{
		return parent::i();
	}

	/**
	 * Get the TS server information.
	 *
	 * @return array|bool
	 */
	public function getServerInfo()
	{
		$ts = static::getInstance();
		$serverInfo = $ts->serverInfo();

		if ( $ts->succeeded( $serverInfo ) )
		{
			return $this->convertServerInfo( $ts->getElement( 'data', $serverInfo ) );
		}

		Log::log( $this->arrayToString( $ts->getElement( 'errors', $serverInfo ) ), 'teamspeak_server_info' );
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
		$ts = static::getInstance();
		$serverInfo = $this->convertServerInfo( $serverInfo, true );

		return $ts->succeeded( $ts->serverEdit( $serverInfo ) );
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
		$ts = static::getInstance();
		return $ts;
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