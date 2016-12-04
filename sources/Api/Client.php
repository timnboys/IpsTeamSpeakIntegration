<?php

namespace IPS\teamspeak\Api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _Client extends \IPS\teamspeak\Api\AbstractConnection
{
	const REGULAR_CLIENT = 0;
	const QUERY_CLIENT = 1;

	/**
	 * Get list of all connected clients.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getClientList()
	{
		$clientList = \IPS\teamspeak\Api\Util::getReturnValue( $this->instance, $this->instance->clientList() );

		return $this->prepareClientList( $clientList );
	}

	/**
	 * Kick a client from the server.
	 *
	 * @param int $clientId
	 * @param string $message
	 * @return bool
	 * @throws \Exception
	 */
	public function kick( $clientId, $message = '' )
	{
		$kickInfo = $this->instance->clientKick( $clientId, 'server', $message );

		return \IPS\teamspeak\Api\Util::getReturnValue( $this->instance, $kickInfo, true );
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
		$pokeInfo = $this->instance->clientPoke( $clientId, $message );

		return \IPS\teamspeak\Api\Util::getReturnValue( $this->instance, $pokeInfo, true );
	}

	/**
	 * Mass poke clients with given message.
	 *
	 * @param string $message
	 * @param int|array $groups
	 * @return bool
	 * @throws \Exception
	 */
	public function masspoke( $message, $groups )
	{
		$clients = \IPS\teamspeak\Api\Util::getReturnValue( $this->instance, $this->instance->clientList( '-groups' ) );

		foreach ( $clients as $client )
		{
			/* Skip non-regular clients */
			if ( $client['client_type'] != static::REGULAR_CLIENT )
			{
				continue;
			}

			$clientGroups = explode( ',', $client['client_servergroups'] );

			if ( $groups == -1 || ( is_array( $groups ) && !empty( array_intersect( $groups, $clientGroups ) ) ) )
			{
				$this->instance->clientPoke( $client['clid'], $message );
			}
		}

		return true;
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
		if ( $banTime !== 0 )
		{
			$banTime = $banTime->getTimestamp() - time();
		}

		$banInfo = $this->instance->banClient( $clientId, $banTime, $reason );

		return \IPS\teamspeak\Api\Util::getReturnValue( $this->instance, $banInfo, true );
	}

	/**
	 * Ban given UUID from the server for given time.
	 *
	 * @param string $uuid
	 * @param int $time
	 * @param string $reason
	 * @return int Ban ID.
	 * @throws \Exception
	 */
	public function banByUuid( $uuid, $time, $reason )
	{
		$banInfo = \IPS\teamspeak\Api\Util::getReturnValue( $this->instance, $this->instance->banAddByUid( $uuid, $time, $reason ) );

		return (int) $banInfo['banid'];
	}

	/**
	 * Unban given ban id.
	 *
	 * @param int $banId
	 * @return void
	 */
	public function unban( $banId )
	{
		$this->instance->banDelete( $banId );
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