<?php

namespace IPS\teamspeak\Api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _Group extends \IPS\teamspeak\Api
{
	const TYPE_TEMPLATE = 0;
	const TYPE_REGULAR = 1;
	const TYPE_SERVERQUERY = 2;

	/**
	 * Only here for auto-complete.
	 *
	 * @param \TeamSpeakAdmin $tsInstance
	 * @param bool $login
	 * @return Group
	 */
	public static function i( \TeamSpeakAdmin $tsInstance = null, $login = true )
	{
		return parent::i( $tsInstance, $login );
	}

	/**
	 * Add server group.
	 *
	 * @param string $serverGroupName
	 * @param int $serverGroupType
	 * @return bool
	 * @throws \Exception
	 */
	public function addServerGroup( $serverGroupName, $serverGroupType )
	{
		$ts = static::getInstance();
		$this->clearCache();

		return $this->getReturnValue( $ts, $ts->serverGroupAdd( $serverGroupName, $serverGroupType ), true );
	}

	/**
	 * Add channel group.
	 *
	 * @param string $channelGroupName
	 * @param int $channelGroupType
	 * @return bool
	 * @throws \Exception
	 */
	public function addChannelGroup( $channelGroupName, $channelGroupType )
	{
		$ts = static::getInstance();
		$this->clearCache();

		return $this->getReturnValue( $ts, $ts->channelGroupAdd( $channelGroupName, $channelGroupType ), true );
	}

	/**
	 * Delete given server group.
	 *
	 * @param int $serverGroupId
	 * @param int $force Force deletion (delete even if there are members in the group).
	 * @return bool
	 * @throws \Exception
	 */
	public function deleteServerGroup( $serverGroupId, $force )
	{
		$ts = static::getInstance();
		$this->clearCache();

		return $this->getReturnValue( $ts, $ts->serverGroupDelete( $serverGroupId, $force ), true );
	}

	/**
	 * Delete given channel group.
	 *
	 * @param int $channelGroupId
	 * @param int $force Force deletion (delete even if there are members in the group).
	 * @return bool
	 * @throws \Exception
	 */
	public function deleteChannelGroup( $channelGroupId, $force )
	{
		$ts = static::getInstance();
		$this->clearCache();

		return $this->getReturnValue( $ts, $ts->channelGroupDelete( $channelGroupId, $force ), true );
	}

	/**
	 * Copy given server group.
	 *
	 * @param int $sourceGroupId Group ID of the group to be copied.
	 * @param string $targetGroupName
	 * @param int $targetGroupType Type of the group.
	 * @param int $targetGroupId Target group id (0 = create new group).
	 * @return bool
	 * @throws \Exception
	 */
	public function copyServerGroup( $sourceGroupId, $targetGroupName, $targetGroupType, $targetGroupId = 0 )
	{
		$ts = static::getInstance();
		$this->clearCache();
		$copyData = $ts->serverGroupCopy( $sourceGroupId, $targetGroupId, $targetGroupName, $targetGroupType );

		return $this->getReturnValue( $ts, $copyData, true );
	}

	/**
	 * Copy given channel group.
	 *
	 * @param int $sourceGroupId Group ID of the group to be copied.
	 * @param string $targetGroupName
	 * @param int $targetGroupType Type of the group.
	 * @param int $targetGroupId Target group id (0 = create new group).
	 * @return bool
	 * @throws \Exception
	 */
	public function copyChannelGroup( $sourceGroupId, $targetGroupName, $targetGroupType, $targetGroupId = 0 )
	{
		$ts = static::getInstance();
		$this->clearCache();
		$copyData = $ts->channelGroupCopy( $sourceGroupId, $targetGroupId, $targetGroupName, $targetGroupType );

		return $this->getReturnValue( $ts, $copyData, true );
	}

	/**
	 * Add a client to a TS group.
	 *
	 * @param int $clientId Client ID, should already be confirmed to be valid.
	 * @param int $groupId Group ID of the group that is to be assigned.
	 * @return bool
	 * @throws \IPS\teamspeak\Exception\GroupNotFoundException
	 */
	public function addClientToGroup( $clientId, $groupId )
	{
		$ts = static::getInstance();

		if ( $this->isValidGroupId( $groupId ) )
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
		
		throw new \IPS\teamspeak\Exception\GroupNotFoundException;
	}

	/**
	 * Add client to more than one TS group.
	 *
	 * @param int $clientId Client ID, will be validated.
	 * @param array $groups Group IDs of the groups that are to be assigned.
	 * @return bool Succeeded?
	 * @throws \IPS\teamspeak\Exception\ClientNotFoundException
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
	 * @throws \IPS\teamspeak\Exception\ClientNotFoundException
	 * @throws \IPS\teamspeak\Exception\GroupNotFoundException
	 */
	public function addUuidToGroup( $uuid, $groupId, array $client = null )
	{
		$ts = static::getInstance();

		if ( !$client )
		{
			$client = $this->getClientFromUuid( $uuid, $ts );
		}

		return $this->addClientToGroup( $client['cldbid'], $groupId );
	}

	/**
	 * Add a member to more than one group by UUID.
	 *
	 * @param string $uuid Members UUID.
	 * @param array $groups Array of TS Group IDs.
	 * @return bool Succeeded?
	 * @throws \IPS\teamspeak\Exception\ClientNotFoundException
	 * @throws \IPS\teamspeak\Exception\GroupNotFoundException
	 */
	public function addUuidToGroups( $uuid, array $groups )
	{
		$ts = static::getInstance();
		$client = $this->getClientFromUuid( $uuid, $ts );
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
	 * @throws \IPS\teamspeak\Exception\ClientNotFoundException
	 * @throws \IPS\teamspeak\Exception\GroupNotFoundException
	 */
	public function removeUuidFromGroup( $uuid, $groupId, array $client = null )
	{
		$ts = static::getInstance();

		if ( !$client )
		{
			$client = $this->getClientFromUuid( $uuid, $ts );
		}

		if ( $this->isValidGroupId( $groupId ) )
		{
			return $this->removeClientFromGroup( $client['cldbid'], $groupId );
		}

		throw new \IPS\teamspeak\Exception\GroupNotFoundException();
	}

	/**
	 * Remove client from given groups.
	 *
	 * @param string $uuid
	 * @param array $groups
	 * @return bool Succeeded?
	 * @throws \IPS\teamspeak\Exception\ClientNotFoundException
	 * @throws \IPS\teamspeak\Exception\GroupNotFoundException
	 */
	public function removeUuidFromGroups( $uuid, array $groups )
	{
		$ts = static::getInstance();
		$client = $this->getClientFromUuid( $uuid, $ts );
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
	 * @param array $associatedGroups
	 * @return bool
	 */
	public function resyncGroupsByUuid( $uuid, array $assignGroups, array $associatedGroups )
	{
		$ts = static::getInstance();

		try
		{
			$client = $this->getClientFromUuid( $uuid, $ts );
		}
		catch( \IPS\teamspeak\Exception\ClientNotFoundException $e )
		{
			return true;
		}

		if ( \IPS\Settings::i()->teamspeak_remove_groups )
		{
			$currentGroups = $this->convertGroupsToCompare(
				$ts->getElement( 'data', $ts->serverGroupsByClientID( $client['cldbid'] ) )
			);

			$removeGroups = array_diff( $currentGroups, $assignGroups );
		}
		else
		{
			$removeGroups = array_diff( $associatedGroups, $assignGroups );
		}

		$removed = $this->removeClientFromGroups( $client['cldbid'], $removeGroups );
		$added = $this->addClientToGroups( $client['cldbid'], $assignGroups );

		return $removed && $added;
	}

	/**
	 * Get server groups.
	 *
	 * @param bool $simplified Simplify the array?
	 * @param bool $regularOnly Only include regular groups?
	 * @param bool $templateGroups Include template groups?
	 * @return array
	 */
	public function getServerGroups( $simplified = true, $regularOnly = true, $templateGroups = false )
	{
		$ts = static::getInstance();
		$serverGroups = static::getReturnValue( $ts, $ts->serverGroupList() );
		$defaultGroupIds = static::getDefaultGroupIds( $ts );

		foreach ( $serverGroups as $group )
		{
			if ( ( $group['sgid'] != $defaultGroupIds['default_server_group'] || !$regularOnly ) && ( $group['type'] == static::TYPE_REGULAR || $templateGroups ) )
			{
				$group['name'] = $group['type'] == static::TYPE_TEMPLATE ? '[T] ' . $group['name'] : $group['name'];
				$group['name'] = $group['type'] == static::TYPE_SERVERQUERY ? '[Q] ' . $group['name'] : $group['name'];
				$returnGroups[] = $group;
			}
		}

		if ( $simplified )
		{
			$returnGroups = static::simplifyGroups( $returnGroups );
		}

		return static::getCachedServerGroups( $simplified, $regularOnly, $templateGroups, $returnGroups );;
	}

	/**
	 * Get cached server groups.
	 *
	 * @param bool $simplified
	 * @param bool $regularOnly
	 * @param bool $templateGroups
	 * @param mixed $data
	 * @return mixed
	 */
	public static function getCachedServerGroups( $simplified = true, $regularOnly = true, $templateGroups = false, $data = null )
	{
		$dataStore = \IPS\Data\Store::i();
		$cacheKey = $regularOnly ? 'teamspeak_server_groups_regular' : 'teamspeak_server_groups_all';
		$cacheKey = $simplified ? $cacheKey . '_simplified' : $cacheKey;
		$cacheKey = $templateGroups ? $cacheKey . '_templates' : $cacheKey;

		/* If it is cached, return the cached data */
		if ( isset( $dataStore->$cacheKey ) && is_null( $data ) )
		{
			return $dataStore->$cacheKey;
		}

		$dataStore->$cacheKey = $data;

		return $data;
	}

	/**
	 * Get channel groups.
	 *
	 * @param bool $simplified Simplify the array (only group id and name)?
	 * @param bool $all Include template groups too?
	 * @return array
	 */
	public function getChannelGroups( $simplified = true, $all = false )
	{
		$ts = static::getInstance();
		$channelGroups = static::getReturnValue( $ts, $ts->channelGroupList() );

		foreach ( $channelGroups as $channelGroup )
		{
			if ( $channelGroup['type'] == static::TYPE_REGULAR || $all )
			{
				$channelGroup['name'] = $channelGroup['type'] == static::TYPE_TEMPLATE ? '[T] ' . $channelGroup['name'] : $channelGroup['name'];
				$returnGroups[] = $channelGroup;
			}
		}

		if ( $simplified )
		{
			$returnGroups = static::simplifyGroups( $returnGroups, true );
		}

		return static::getCachedChannelGroups( $simplified, $all, $returnGroups );
	}

	public static function getCachedChannelGroups( $simplified = true, $all = false, $data = null )
	{
		$dataStore = \IPS\Data\Store::i();

		$cacheKey = 'teamspeak_channel_groups';
		$cacheKey = $simplified ? $cacheKey . '_simplified' : $cacheKey;
		$cacheKey = $all ? $cacheKey . '_all' : $cacheKey;

		/* If it is cached, return the cached data */
		if ( isset( $dataStore->$cacheKey ) && is_null( $data ) )
		{
			return $dataStore->$cacheKey;
		}

		$dataStore->$cacheKey = $data;

		return $data;
	}

	/**
	 * Check if the given group id is valid.
	 *
	 * @param int $groupId
	 * @return bool Is it valid?
	 * @throws \IPS\teamspeak\Exception\ServerGroupException
	 */
	protected function isValidGroupId( $groupId )
	{
		$groups = $this->getServerGroups();

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
	 */
	public static function getDefaultGroupIds( \TeamSpeakAdmin $ts )
	{
		$dataStore = \IPS\Data\Store::i();

		if ( isset( $dataStore->teamspeak_default_group_ids ) )
		{
			return $dataStore->teamspeak_default_group_ids;
		}

		$server = static::getReturnValue( $ts, $ts->serverInfo() );

		$defaultGroupIds = [
			'default_server_group' => $server['virtualserver_default_server_group'],
			'default_channel_group' => $server['virtualserver_default_channel_group'],
			'default_channel_admin_group' => $server['virtualserver_default_channel_admin_group']
		];

		$dataStore->teamspeak_default_group_ids = $defaultGroupIds;

		return $defaultGroupIds;
	}

	/**
	 * Get client from UUID.
	 *
	 * @param string $uuid
	 * @param \TeamSpeakAdmin $ts TS server instance.
	 * @return array
	 * @throws \IPS\teamspeak\Exception\ClientNotFoundException
	 * @throws \Exception
	 */
	public function getClientFromUuid( $uuid, \TeamSpeakAdmin $ts )
	{
		try
		{
			$client = $this->getReturnValue( $ts, $ts->clientDbFind( $uuid, true ) );
		}
		catch ( \Exception $e )
		{
			/* If for some reason we have an invalid UUID throw a different exception so we can catch it better */
			if ( $e->getMessage() == 'ErrorID: 1281 | Message: database empty result set' )
			{
				throw new \IPS\teamspeak\Exception\ClientNotFoundException();
			}

			throw $e;
		}

		return reset( $client );
	}

	/**
	 * Clear all group caches.
	 *
	 * @return void
	 */
	protected static function clearCache()
	{
		try
		{
			$dataStore = \IPS\Data\Store::i();
			$cacheKeys[] = 'teamspeak_server_groups_regular';
			$cacheKeys[] = 'teamspeak_server_groups_regular_simplified';
			$cacheKeys[] = 'teamspeak_server_groups_regular_simplified_templates';
			$cacheKeys[] = 'teamspeak_server_groups_regular_templates';
			$cacheKeys[] = 'teamspeak_server_groups_all';
			$cacheKeys[] = 'teamspeak_server_groups_all_simplified';
			$cacheKeys[] = 'teamspeak_server_groups_all_simplified_templates';
			$cacheKeys[] = 'teamspeak_server_groups_all_templates';
			$cacheKeys[] = 'teamspeak_channel_groups';
			$cacheKeys[] = 'teamspeak_channel_groups_all';
			$cacheKeys[] = 'teamspeak_channel_groups_simplified';
			$cacheKeys[] = 'teamspeak_channel_groups_simplified_all';

			foreach ( $cacheKeys as $cacheKey )
			{
				unset( $dataStore->$cacheKey );
			}
		}
		catch ( \Exception $e ){}
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

	/**
	 * @param array $groups
	 * @return array
	 */
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