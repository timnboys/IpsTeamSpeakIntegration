<?php

namespace IPS\teamspeak;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _Uuid extends \IPS\Patterns\ActiveRecord
{
	/**
	 * @brief    [ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 's_';

	/**
	 * @brief    [ActiveRecord] ID Database Column
	 */
	public static $databaseColumnId = 'id';

	/**
	 * @brief	[ActiveRecord] Database ID Fields
	 */
	protected static $databaseIdFields = array( 's_uuid' );

	/**
	 * @brief    [ActiveRecord] Database table
	 * @note    This MUST be over-ridden
	 */
	public static $databaseTable = 'teamspeak_member_sync';

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
	 * Delete Record
	 *
	 * @return    void
	 */
	public function delete()
	{
		$member = \IPS\Member::load( $this->member_id );

		try
		{
			$teamspeak = \IPS\teamspeak\Member::i();

			if ( !$teamspeak->removeGroups( $member, $this->uuid ) )
			{
				\IPS\Output::i()->error( 'teamspeak_could_not_remove_groups', '4P102/1' );
			}
		}
		catch ( \IPS\teamspeak\Exception\ClientNotFoundException $e )
		{
			/* If client does not exist, ignore and just remove the entry from the database */
		}


		parent::delete();
	}
}