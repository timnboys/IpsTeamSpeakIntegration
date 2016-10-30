<?php

namespace IPS\teamspeak\Api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _Client extends \IPS\teamspeak\Api
{
	const REGULAR_CLIENT = 0;
	const QUERY_CLIENT = 1;

	/**
	 * Only here for auto-complete.
	 *
	 * @return Client
	 */
	public static function i()
	{
		return parent::i();
	}

	/**
	 * Get list of all connected clients.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getClientList()
	{
		$ts = static::getInstance();
		$clientList = $ts->clientList();

		if ( $ts->succeeded( $clientList ) )
		{
			$clientList = $ts->getElement( 'data', $clientList );

			return $this->prepareClientList( $clientList );
		}

		throw new \Exception(); //TODO
	}

	/**
	 * Kick a client from the server.
	 *
	 * @param int $clientId
	 * @param string $message
	 * @return bool
	 * @throws \Exception
	 */
	public function kick( $clientId, $message = "" )
	{
		$ts = static::getInstance();

		$kickInfo = $ts->clientKick( $clientId, "server", $message );

		if ( $ts->succeeded( $kickInfo ) )
		{
			return true;
		}

		throw new \Exception( $this->arrayToString( $ts->getElement( 'errors', $kickInfo ) ) );
		return false;
	}

	/**
	 * Poke client with given message.
	 *
	 * @param int $clientId
	 * @param string $message
	 * @return bool
	 * @throws \Exception
	 */
	public function poke( $clientId, $message )
	{
		$ts = static::getInstance();

		$pokeInfo = $ts->clientPoke( $clientId, $message );

		if ( $ts->succeeded( $pokeInfo ) )
		{
			return true;
		}

		throw new \Exception( $this->arrayToString( $ts->getElement( 'errors', $pokeInfo ) ) );
		return false;
	}

	/**
	 * Ban client from the server.
	 *
	 * @param int $clientId
	 * @param int|\IPS\DateTime $banTime
	 * @param string $reason
	 * @return bool
	 * @throws \Exception
	 */
	public function ban( $clientId, $banTime, $reason )
	{
		$ts = static::getInstance();

		if ( $banTime !== 0 )
		{
			$banTime = $banTime->getTimestamp() - time();
		}

		$banInfo = $ts->banClient( $clientId, $banTime, $reason );

		if ( $ts->succeeded( $banInfo ) )
		{
			return true;
		}

		throw new \Exception( $this->arrayToString( $ts->getElement( 'errors', $banInfo ) ) );
		return false;
	}

	/**
	 * Only return regular clients.
	 *
	 * @param array $clientList
	 * @return array
	 */
	protected function prepareClientList( array $clientList )
	{
		foreach ( $clientList as $id => $client )
		{
			if ( $client['client_type'] != static::REGULAR_CLIENT )
			{
				unset( $clientList[$id] );
			}
		}

		return $clientList;
	}
}