<?php

namespace IPS\teamspeak;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

use IPS\Member;
use IPS\Member\Group as IpsGroup;
use IPS\teamspeak\Api\Group;
use IPS\teamspeak\Member as TsMember;

class _Member
{
	/**
	 * Get called class.
	 *
	 * @return TsMember
	 */
	public static function i()
	{
		$classname = get_called_class();
		return new $classname;
	}

	/**
	 * Give member the groups that they should get.
	 *
	 * @param Member $member
	 * @param string $uuid
	 * @return bool
	 */
	public function addGroups( Member $member, $uuid )
	{
		$assignGroups = $this->getAssociatedTsGroups( $member );

		if ( !$assignGroups )
		{
			/* Member does not qualify for additional groups */
			return true;
		}

		$assignGroups = array_unique( $assignGroups );

		$teamspeak = Group::i();
		return $teamspeak->addUuidToGroups( $uuid, $assignGroups );
	}

	/**
	 * Remove groups from member that they gained through this APP.
	 *
	 * @param Member $member
	 * @param string $uuid
	 * @return bool
	 */
	public function removeGroups( Member $member, $uuid )
	{
		$removeGroups = $this->getAssociatedTsGroups( $member );

		if ( !$removeGroups )
		{
			/* Member does not qualify for additional groups */
			return true;
		}

		$removeGroups = array_unique( $removeGroups );

		$teamspeak = Group::i();
		return $teamspeak->removeUuidFromGroups( $uuid, $removeGroups );
	}

	/**
	 * Remove groups from member that they gained through this APP.
	 *
	 * @param Member $member
	 * @param string $uuid
	 * @return bool
	 */
	public function resyncGroups( Member $member, $uuid )
	{
		$associatedGroups = array_unique( $this->getAssociatedTsGroups( $member ) );

		$teamspeak = Group::i();
		return $teamspeak->resyncGroupsByUuid( $uuid, $associatedGroups );
	}

	/**
	 * Remove groups from member that they gained through this APP.
	 *
	 * @param Member $member
	 * @return bool
	 */
	public function resyncGroupsAllUuids( Member $member )
	{
		$associatedGroups = array_unique( $this->getAssociatedTsGroups( $member ) );
		$teamspeak = Group::i();
		$success = true;

		foreach ( $member->teamspeak_uuids as $uuid )
		{
			if ( !$teamspeak->resyncGroupsByUuid( $uuid, $associatedGroups ) )
			{
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Get TS groups that the member has access to.
	 *
	 * @param Member $member
	 * @return array|bool
	 */
	protected function getAssociatedTsGroups( Member $member )
	{
		$tsGroups = array();

		foreach ( $member->get_groups() as $groupId )
		{
			try
			{
				$group = IpsGroup::load( $groupId );
			}
			catch ( \OutOfRangeException $e )
			{
				/* Apparently some users have a groupId of 0 */
				continue;
			}

			if ( $group->teamspeak_group !== -1 )
			{
				$tsGroups[] = $group->teamspeak_group;
			}
		}

		return $tsGroups;
	}
}