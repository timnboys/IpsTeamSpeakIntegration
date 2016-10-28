<?php
/**
 * @brief        Member Sync
 * @author        <a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright    (c) 2001 - 2016 Invision Power Services, Inc.
 * @license        http://www.invisionpower.com/legal/standards/
 * @package        IPS Community Suite
 * @subpackage    TeamSpeak Integration
 * @since        20 Oct 2016
 * @version        SVN_VERSION_NUMBER
 */

namespace IPS\teamspeak\extensions\core\MemberSync;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

use IPS\Db;
use IPS\teamspeak\Member;

/**
 * Member Sync
 */
class _MemberSync
{
	/**
	 * Member account has been updated
	 *
	 * @param    $member        \IPS\Member    Member updating profile
	 * @param    $changes    array        The changes
	 * @return    void
	 */
	public function onProfileUpdate( $member, $changes )
	{
		//TODO
	}

	/**
	 * Member is flagged as spammer
	 *
	 * @param    $member    \IPS\Member    The member
	 * @return    void
	 */
	public function onSetAsSpammer( $member )
	{
		//TODO
	}

	/**
	 * Member is unflagged as spammer
	 *
	 * @param    $member    \IPS\Member    The member
	 * @return    void
	 */
	public function onUnSetAsSpammer( $member )
	{
		//TODO
	}

	/**
	 * Member is merged with another member
	 *
	 * @param    \IPS\Member $member Member being kept
	 * @param    \IPS\Member $member2 Member being removed
	 * @return    void
	 */
	public function onMerge( $member, $member2 )
	{
		$set = [
			's_member_id' => $member->member_id
		];
		$where = [
			's_member_id=?',
			$member2->member_id
		];

		$tsMember = Member::i();

		foreach ( Db::i()->select( 's_uuid', 'teamspeak_member_sync', array( 's_member_id=?', $member2->member_id ) ) as $uuid )
		{
			$tsMember->removeGroups( $member2, $uuid );
			$tsMember->addGroups( $member, $uuid );
		}

		Db::i()->update( 'teamspeak_member_sync', $set, $where );
	}

	/**
	 * Member is deleted
	 *
	 * @param    $member    \IPS\Member    The member
	 * @return    void
	 */
	public function onDelete( $member )
	{
		$tsMember = Member::i();

		foreach ( Db::i()->select( 's_uuid', 'teamspeak_member_sync', array( 's_member_id=?', $member->member_id ) ) as $uuid )
		{
			$tsMember->removeGroups( $member, $uuid );
		}

		Db::i()->delete(
			'teamspeak_member_sync', [
			's_member_id=?',
			$member->member_id
		]
		);
	}
}