<?php

namespace IPS\teamspeak\Api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _Ban extends \IPS\teamspeak\Api
{

	/**
	 * Only here for auto-complete.
	 *
	 * @param \TeamSpeakAdmin $tsInstance
	 * @param bool $login
	 * @return Ban
	 */
	public static function i( \TeamSpeakAdmin $tsInstance = null, $login = true )
	{
		return parent::i( $tsInstance, $login );
	}

	/**
	 * Get array containing all bans.
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function getBanList()
	{
		$ts = static::getInstance();

		try
		{
			return $this->getReturnValue( $ts, $ts->banList() );
		}
		catch ( \Exception $e )
		{
			if ( $e->getMessage() == 'ErrorID: 1281 | Message: database empty result set' )
			{
				return array();
			}

			throw $e;
		}
	}

	/**
	 * Delete given ban id.
	 *
	 * @param $banId
	 * @return bool|mixed
	 */
	public function deleteBan( $banId )
	{
		$ts = static::getInstance();

		return $this->getReturnValue( $ts, $ts->banDelete( $banId ) );
	}

	/**
	 * Delete all bans on the TS server.
	 *
	 * @return bool|mixed
	 */
	public function deleteAll()
	{
		$ts = static::getInstance();

		return $this->getReturnValue( $ts, $ts->banDeleteAll() );
	}
}