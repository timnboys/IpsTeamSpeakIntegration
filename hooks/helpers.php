//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
    exit;
}

class teamspeak_hook_helpers extends _HOOK_CLASS_
{
    public function get_teamspeak_uuids()
    {
        $uuids = array();

        foreach ( \IPS\Db::i()->select( 's_id, s_uuid', 'teamspeak_member_sync', array( 's_member_id=?', $this->member_id ) ) as $uuid )
        {
            $uuids[$uuid['s_id']] = $uuid['s_uuid'];
        }

        return $uuids;
    }
}