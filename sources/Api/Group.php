<?php

namespace IPS\teamspeak\Api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

use IPS\Data\Store;
use IPS\teamspeak\ClientNotFoundException;
use IPS\teamspeak\Exception\ChannelGroupException;
use IPS\teamspeak\Exception\ServerException;
use IPS\teamspeak\Exception\ServerGroupException;
use IPS\teamspeak\GroupNotFoundException;

class _Group extends \IPS\teamspeak\Api
{
	const TYPE_TEMPLATE = 0;
	const TYPE_REGULAR = 1;
	const TYPE_SERVERQUERY = 2;

	/**
	 * Only here for auto-complete.
	 *
	 * @return Group
	 */
	public static function i()
	{
		return parent::i();
	}

	/**
	 * Add a client to a TS group.
	 *
	 * @param int $clientId Client ID, should already be confirmed to be valid.
	 * @param int $groupId Group ID of the group that is to be assigned.
	 * @return bool
	 * @throws GroupNotFoundException
	 */
	public function addClientToGroup( $clientId, $groupId )
	{
		$ts = static::getInstance();

		if ( $this->isValidGroupId( $groupId, $ts ) )
		{
			$temp = $ts->serverGroupAddClient( $groupId, $clientId );
			$success = $ts->succeeded( $temp );

			if ( !$success )
			{
				$errors = $ts->getElement( 'errors', $temp );

				if ( $this->arrayToString( $errors ) == 'ErrorID: 2561 | Message: duplicate entry' )
				{
					/* If member already part of group, indicate as succeeded */
					$success = true;
				}
			}

			return $success;
		}

		throw new GroupNotFoundException;
	}

	/**
	 * Add client to more than one TS group.
	 *
	 * @param int $clientId Client ID, will be validated.
	 * @param array $groups Group IDs of the groups that are to be assigned.
	 * @return bool Succeeded?
	 * @throws ClientNotFoundException
	 */
	public function addClientToGroups( $clientId, array $groups )
	{
		$success = true;

		foreach ( $groups as $groupId )
		{
			if ( !$this->addClientToGroup( $clientId, $groupId ) )
			{
				/* If at least one of the groups fail to assign, flag as failed */
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Add a member to a group by UUID.
	 *
	 * @param string $uuid Members UUID.
	 * @param int $groupId TS Group ID.
	 * @param array|null $client
	 * @return bool Succeeded?
	 * @throws ClientNotFoundException
	 * @throws GroupNotFoundException
	 */
	public function addUuidToGroup( $uuid, $groupId, array $client = null )
	{
		$ts = static::getInstance();

		if ( !$client )
		{
			$client = $this->getClientFromUuuid( $uuid, $ts );
		}

		return $this->addClientToGroup( $client['cldbid'], $groupId );
	}

	/**
	 * Add a member to more than one group by UUID.
	 *
	 * @param string $uuid Members UUID.
	 * @param array $groups Array of TS Group IDs.
	 * @return bool Succeeded?
	 * @throws ClientNotFoundException
	 * @throws GroupNotFoundException
	 */
	public function addUuidToGroups( $uuid, array $groups )
	{
		$ts = static::getInstance();
		$client = $this->getClientFromUuuid( $uuid, $ts );
		$success = true;

		foreach ( $groups as $groupId )
		{
			if ( !$this->addUuidToGroup( $uuid, $groupId, $client ) )
			{
				/* If at least one of the groups fail to assign, flag as failed */
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Remove client from given group.
	 *
	 * @param int $groupId TS Group ID.
	 * @param int $clientId Client ID, should already be confirmed to be valid
	 * @return bool Succeeded?
	 */
	public function removeClientFromGroup( $clientId, $groupId )
	{
		$ts = static::getInstance();
		$temp = $ts->serverGroupDeleteClient( $groupId, $clientId );
		$success = $ts->succeeded( $temp );

		if ( !$success )
		{
			$errors = $ts->getElement( 'errors', $temp );

			if ( $this->arrayToString( $errors ) == 'ErrorID: 2563 | Message: empty result set' )
			{
				/* If member was not part of the group, indicate as succeeded */
				$success = true;
			}
		}

		return $success;
	}

	/**
	 * Remove client from given groups.
	 *
	 * @param int $clientId Client ID, should already be confirmed to be valid
	 * @param array $groups
	 * @return bool Succeeded?
	 * @internal param int $groupId TS Group ID.
	 */
	public function removeClientFromGroups( $clientId, array $groups )
	{
		$success = true;

		foreach ( $groups as $groupId )
		{
			if ( !$this->removeClientFromGroup( $clientId, $groupId ) )
			{
				/* If at least one of the groups fail to assign, flag as failed */
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Remove client from given group.
	 *
	 * @param string $uuid Members UUID.
	 * @param int $groupId TS Group ID.
	 * @param array|null $client
	 * @return bool Succeeded?
	 * @throws ClientNotFoundException
	 * @throws GroupNotFoundException
	 */
	public function removeUuidFromGroup( $uuid, $groupId, array $client = null )
	{
		$ts = static::getInstance();

		if ( !$client )
		{
			$client = $this->getClientFromUuuid( $uuid, $ts );
		}

		if ( $this->isValidGroupId( $groupId, $ts ) )
		{
			return $this->removeClientFromGroup( $client['cldbid'], $groupId );
		}

		throw new GroupNotFoundException();
	}

	/**
	 * Remove client from given groups.
	 *
	 * @param string $uuid
	 * @param array $groups
	 * @return bool Succeeded?
	 * @throws ClientNotFoundException
	 * @throws GroupNotFoundException
	 */
	public function removeUuidFromGroups( $uuid, array $groups )
	{
		$ts = static::getInstance();
		$client = $this->getClientFromUuuid( $uuid, $ts );
		$success = true;

		foreach ( $groups as $groupId )
		{
			if ( !$this->removeUuidFromGroup( $uuid, $groupId, $client ) )
			{
				/* If at least one of the groups fail to be removed, flag as failed */
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Re-sync groups.
	 *
	 * @param string $uuid
	 * @param array $assignGroups
	 * @return bool
	 * @throws ClientNotFoundException
	 */
	public function resyncGroupsByUuid( $uuid, array $assignGroups )
	{
		$ts = static::getInstance();
		$client = $this->getClientFromUuuid( $uuid, $ts );

		$currentGroups = $this->convertGroupsToCompare(
			$ts->getElement( 'data', $ts->serverGroupsByClientID( $client['cldbid'] ) )
		);

		$removeGroups = array_diff( $currentGroups, $assignGroups );

		$removed = $this->removeClientFromGroups( $client['cldbid'], $removeGroups );
		$added = $this->addClientToGroups( $client['cldbid'], $assignGroups );

		return $removed && $added;
	}

	/**
	 * Get server groups.
	 *
	 * @param \TeamSpeakAdmin $ts
	 * @param bool $regularOnly Only include regular groups?
	 * @return array
	 * @throws ServerException
	 * @throws ServerGroupException
	 */
	public static function getServerGroups( \TeamSpeakAdmin $ts = null, $regularOnly = true )
	{
		$dataStore = Store::i();
		$cacheKey = $regularOnly ? 'teamspeak_server_groups_regular' : 'teamspeak_server_groups_all';

		/* If it is cached, return the cached data */
		if ( isset( $dataStore->$cacheKey ) )
		{
			return $dataStore->$cacheKey;
		}

		if ( is_null( $ts ) )
		{
			$ts = static::i()->getInstance();
		}

		$serverGroups = $ts->serverGroupList();
		$defaultGroupIds = static::getDefaultGroupIds( $ts );

		if ( $ts->succeeded( $serverGroups ) )
		{
			$groups = $ts->getElement( 'data', $serverGroups );

			foreach ( $groups as $group )
			{
				if ( ( $group['sgid'] != $defaultGroupIds['default_server_group'] || !$regularOnly ) &&
					$group['type'] == static::TYPE_REGULAR
				)
				{
					$returnGroups[] = $group;
				}
			}

			$returnGroups = static::simplifyGroups( $returnGroups );
			$dataStore->$cacheKey = $returnGroups;

			return $returnGroups;
		}

		throw new ServerGroupException();
	}

	/**
	 * Get channel groups.
	 *
	 * @param \TeamSpeakAdmin $ts
	 * @return array
	 * @throws ChannelGroupException
	 */
	public static function getChannelGroups( \TeamSpeakAdmin $ts )
	{
		$dataStore = Store::i();

		/* If it is cached, return the cached data */
		if ( isset( $dataStore->teamspeak_channel_groups ) )
		{
			return $dataStore->teamspeak_channel_groups;
		}

		$channelGroups = $ts->channelGroupList();

		if ( $ts->succeeded( $channelGroups ) )
		{
			$channelGroups = $ts->getElement( 'data', $channelGroups );

			foreach ( $channelGroups as $channelGroup )
			{

				if ( $channelGroup['type'] == static::TYPE_REGULAR )
				{
					$returnGroups[] = $channelGroup;
				}
			}

			$returnGroups = static::simplifyGroups( $returnGroups, true );
			$dataStore->teamspeak_channel_groups = $returnGroups;

			return $returnGroups;
		}

		throw new ChannelGroupException();
	}

	/**
	 * Check if the given group id is valid.
	 *
	 * @param int $groupId
	 * @param \TeamSpeakAdmin|null $ts
	 * @return bool Is it valid?
	 * @throws ServerGroupException
	 */
	protected function isValidGroupId( $groupId, $ts = null )
	{
		$groups = $this->getServerGroups( $ts );

		if ( array_key_exists( $groupId, $groups ) )
		{
			return true;
		}

		return false;
	}

	/**
	 * Get default group IDs.
	 *
	 * @param \TeamSpeakAdmin $ts
	 * @return array
	 * @throws ServerException
	 */
	public static function getDefaultGroupIds( \TeamSpeakAdmin $ts )
	{
		$dataStore = Store::i();

		if ( isset( $dataStore->teamspeak_default_group_ids ) )
		{
			return $dataStore->teamspeak_default_group_ids;
		}

		$server = $ts->serverInfo();

		if ( $ts->succeeded( $server ) )
		{
			$server = $ts->getElement( 'data', $server );
			$defaultGroupIds = [
				'default_server_group' => $server['virtualserver_default_server_group'],
				'default_channel_group' => $server['virtualserver_default_channel_group'],
				'default_channel_admin_group' => $server['virtualserver_default_channel_admin_group']
			];

			$dataStore->teamspeak_default_group_ids = $defaultGroupIds;

			return $defaultGroupIds;
		}

		throw new ServerException();
	}

	/**
	 * Get client from UUID.
	 *
	 * @param string $uuid
	 * @param \TeamSpeakAdmin $ts TS server instance.
	 * @return array
	 * @throws ClientNotFoundException
	 */
	protected function getClientFromUuuid( $uuid, \TeamSpeakAdmin $ts )
	{
		$client = $ts->clientDbFind( $uuid, true );

		if ( $ts->succeeded( $client ) )
		{
			$client = $ts->getElement( 'data', $client );
			return reset( $client );
		}

		throw new ClientNotFoundException();
	}

	/**
	 * Simplify the groups array that is returned by the API to only include the group id and name.
	 *
	 * @param array $groups
	 * @param bool $channelGroups
	 * @return array
	 */
	protected static function simplifyGroups( array $groups, $channelGroups = false )
	{
		$returnGroups = array();

		foreach ( $groups as $group )
		{
			$key = $channelGroups ? 'cgid' : 'sgid';
			$returnGroups[$group[$key]] = $group['name'];
		}

		return $returnGroups;
	}

	protected static function convertGroupsToCompare( array $groups )
	{
		$returnGroups = array();

		foreach ( $groups as $group )
		{
			$returnGroups[] = $group['sgid'];
		}

		return $returnGroups;
	}
}