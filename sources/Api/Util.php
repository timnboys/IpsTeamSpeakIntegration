<?php

namespace IPS\teamspeak\Api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _Util
{
	public function dummy()
	{}

	/**
	 * Extract the required data from the array that we get from \TeamSpeakAdmin.
	 *
	 * @param \TeamSpeakAdmin $ts
	 * @param array $data
	 * @param bool $bool Only check if it succeeded (no data required)?
	 * @return bool|mixed
	 * @throws \Exception
	 */
	public static function getReturnValue( \TeamSpeakAdmin $ts, array $data, $bool = false )
	{
		if ( $ts->succeeded( $data ) )
		{
			if ( !$bool )
			{
				return $ts->getElement( 'data', $data );
			}

			return true;
		}

		throw new \Exception( static::arrayToString( $ts->getElement( 'errors', $data ) ) );
	}

	/**
	 * Convert the errors array (from \TeamSpeakAdmin) into a string for logging.
	 *
	 * @param array $errors
	 * @return string
	 */
	public static function arrayToString( array $errors )
	{
		$string = '';

		foreach ( $errors as $error )
		{
			$string .= $error . ' ';
		}

		return trim( $string );
	}
}