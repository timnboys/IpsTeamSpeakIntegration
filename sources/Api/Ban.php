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
	 * @return Ban
	 */
	public static function i()
	{
		return parent::i();
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

		return $this->getReturnValue( $ts, $ts->banList() );
	}

	public function deleteBan( $banId )
	{
		$ts = static::getInstance();

		return $this->getReturnValue( $ts, $ts->banDelete( $banId ) );
	}

	public function deleteAll()
	{
		$ts = static::getInstance();

		return $this->getReturnValue( $ts, $ts->banDeleteAll() );
	}
}