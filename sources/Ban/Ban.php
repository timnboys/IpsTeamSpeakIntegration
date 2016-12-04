<?php

namespace IPS\teamspeak;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _Ban extends \IPS\Patterns\ActiveRecord
{
	/**
	 * @brief    [ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 'b_';

	/**
	 * @brief    [ActiveRecord] ID Database Column
	 */
	public static $databaseColumnId = 'id';

	/**
	 * @brief	[ActiveRecord] Database ID Fields
	 * @note	If using this, declare a static $multitonMap = array(); in the child class to prevent duplicate loading queries
	 */
	protected static $databaseIdFields = array( 'b_member_id' );

	/**
	 * @brief    [ActiveRecord] Database table
	 * @note    This MUST be over-ridden
	 */
	public static $databaseTable = 'teamspeak_member_ban';

	/**
	 * @brief    [ActiveRecord] Multiton Store
	 * @note    This needs to be declared in any child classes as well, only declaring here for editor code-complete/error-check functionality
	 */
	protected static $multitons = array();

	/**
	 * Set Default Values (overriding $defaultValues)
	 *
	 * @return    void
	 */
	protected function setDefaultValues()
	{
		$this->date = time();
	}

	/**
	 * Convert json_encoded string to array.
	 *
	 * @return array
	 */
	public function get_ban_ids()
	{
		return json_decode( $this->_data['ban_ids'] );
	}

	/**
	 * Convert array into json_encode format.
	 *
	 * @param array $banIds
	 * @return void
	 */
	public function set_ban_ids( array $banIds )
	{
		$this->_data['ban_ids'] = json_encode( $banIds );
	}
}