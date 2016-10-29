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
	/**
	 * Only here for auto-complete.
	 *
	 * @return Client
	 */
	public static function i()
	{
		return parent::i();
	}
}