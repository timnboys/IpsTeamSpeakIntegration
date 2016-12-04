<?php

namespace IPS\teamspeak;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
    header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Member
{
    /**
     * Get called class.
     *
     * @return \IPS\teamspeak\Member
     */
    public static function i()
    {
        $classname = get_called_class();
        return new $classname;
    }

    /**
     * Give member the groups that they should get.
     *
     * @param \IPS\Member $member
     * @param string $uuid
     * @return bool
     */
    public function addGroups( \IPS\Member $member, $uuid )
    {
        $assignGroups = $this->getAssociatedTsGroups( $member );

        if ( !$assignGroups )
        {
            /* Member does not qualify for additional groups */
            return true;
        }

        $assignGroups = array_unique( $assignGroups );

        $teamspeak = new \IPS\teamspeak\Api\Group();
        return $teamspeak->addUuidToGroups( $uuid, $assignGroups );
    }

    /**
     * Remove groups from member that they gained through this APP.
     *
     * @param \IPS\Member $member
     * @param string $uuid
     * @return bool
     */
    public function removeGroups( \IPS\Member $member, $uuid )
    {
        $removeGroups = $this->getAssociatedTsGroups( $member );

        if ( !$removeGroups )
        {
            /* Member does not qualify for additional groups */
            return true;
        }

        $removeGroups = array_unique( $removeGroups );

        $teamspeak = new \IPS\teamspeak\Api\Group();
        return $teamspeak->removeUuidFromGroups( $uuid, $removeGroups );
    }

    /**
     * Remove groups from member that they gained through this APP.
     *
     * @param \IPS\Member $member
     * @param string $uuid
     * @return bool
     */
    public function resyncGroups( \IPS\Member $member, $uuid )
    {
        $associatedGroups = array_unique( $this->getAssociatedTsGroups( $member ) );

        $teamspeak = new \IPS\teamspeak\Api\Group();
        return $teamspeak->resyncGroupsByUuid( $uuid, $associatedGroups, $this->getAllAssociatedTsGroups() );
    }

    /**
     * Remove groups from member that they gained through this APP.
     *
     * @param \IPS\Member $member
     * @return bool
     */
    public function resyncGroupsAllUuids( \IPS\Member $member )
    {
        $associatedGroups = array_unique( $this->getAssociatedTsGroups( $member ) );
        $teamspeak = new \IPS\teamspeak\Api\Group();
        $success = true;

        foreach ( $member->teamspeak_uuids as $uuid )
        {
            if ( !$teamspeak->resyncGroupsByUuid( $uuid, $associatedGroups, $this->getAllAssociatedTsGroups() ) )
            {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Does given UUID exist?
     *
     * @param $uuid
     * @return bool
     */
    public function isValidUuid( $uuid )
    {
        $teamspeak = new \IPS\teamspeak\Api\Group();

        try
        {
            $teamspeak->getClientFromUuid( $uuid );
        }
        catch ( \IPS\teamspeak\Exception\ClientNotFoundException $e )
        {
            return false;
        }

        return true;
    }

    /**
     * Ban all UUIDs of given member.
     *
     * @param \IPS\Member $member
     * @param int $time
     * @param string $reason
     * @return void
     */
    public function ban( \IPS\Member $member, $time, $reason )
    {
        $teamspeak = new \IPS\teamspeak\Api\Client();
        $banIds = array();

        foreach ( $member->teamspeak_uuids as $uuid )
        {
            try
            {
                $banIds[] = $teamspeak->banByUuid( $uuid, $time, $reason );
            }
            catch ( \Exception $e ){}
        }

        /* Save ban ids */
        $tsBan = new \IPS\teamspeak\Ban;
        $tsBan->member_id = $member->member_id;
        $tsBan->ban_ids = $banIds;
        $tsBan->save();
    }

    /**
     * Ban all UUIDs of given member.
     *
     * @param \IPS\Member $member
     * @return void
     */
    public function unban( \IPS\Member $member )
    {
        $teamspeak = new \IPS\teamspeak\Api\Client();

        try
        {
            $tsBan = \IPS\teamspeak\Ban::load( $member->member_id, 'b_member_id' );
        }
        catch ( \OutOfRangeException $e )
        {
            return;
        }

        $banIds = $tsBan->ban_ids;

        foreach ( $banIds as $banId )
        {
            try
            {
                $teamspeak->unban( $banId );
            }
            catch ( \Exception $e ){}
        }

        $tsBan->delete();
    }

    /**
     * Remove all groups on the TeamSpeak server that are not linked.
     *
     * @return bool
     */
    public function syncUnlinkedUuids()
    {
        $client = new \IPS\teamspeak\Api\Client();
        $allServerUuids = $client->getAllUuids();
        $allSiteUuids = $this->getAllSyncedUuids();

        $unlinkedUuids = array_diff( $allServerUuids, $allSiteUuids );

        $group = new \IPS\teamspeak\Api\Group();
        $allGroups = array_flip( $group->getServerGroups() );
        $successful = true;

        foreach ( $unlinkedUuids as $uuid )
        {
            if ( !$group->removeUuidFromGroups( $uuid, $allGroups ) )
            {
                $successful = false;
            }
        }

        return $successful;
    }

    /**
     * Get all UUIDs that are synced (saved in the DB).
     *
     * @return array
     */
    protected function getAllSyncedUuids()
    {
        $uuids = [];

        foreach ( \IPS\Db::i()->select( 's_uuid', 'teamspeak_member_sync' ) as $uuid )
        {
            if ( !empty( $uuid ) )
            {
                $uuids[] = $uuid;
            }
        }

        return $uuids;
    }

    /**
     * Get TS groups that the member has access to.
     *
     * @param \IPS\Member $member
     * @return array|bool
     */
    protected function getAssociatedTsGroups( \IPS\Member $member )
    {
        $tsGroups = array();

        foreach ( $member->get_groups() as $groupId )
        {
            try
            {
                $group = \IPS\Member\Group::load( $groupId );
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

    /**
     * Get all linked TS groups.
     *
     * @return array
     */
    protected function getAllAssociatedTsGroups()
    {
        $tsGroups = array();

        foreach ( \IPS\Member\Group::groups( true, false ) as $group )
        {
            if ( $group->teamspeak_group !== -1 )
            {
                $tsGroups[] = $group->teamspeak_group;
            }
        }

        return $tsGroups;
    }
}