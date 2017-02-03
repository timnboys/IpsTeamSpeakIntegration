<?php

$lang = array(

    /* !Admin */
    '__app_teamspeak'	=> "TeamSpeak Integration",

    /* !Menu */
    'menutab__teamspeak' => "TeamSpeak",
    'menutab__teamspeak_icon' => "phone",
    'menu__teamspeak_overview' => "Overview",
    'menu__teamspeak_overview_test' => "Test Connection",
    'menu__teamspeak_overview_settings' => "Settings",
    'menu__teamspeak_members' => "TeamSpeak Members",
    'menu__teamspeak_members_members' => 'UUIDs',
    'menu__teamspeak_teamspeak' => "TeamSpeak",
    'menu__teamspeak_teamspeak_server' => "Server",
    'menu__teamspeak_teamspeak_alert' => "Alert",
    'menu__teamspeak_teamspeak_clients' => "Clients",
    'menu__teamspeak_groups' => "Groups",
    'menu__teamspeak_groups_servergroups' => "Server Groups",
    'menu__teamspeak_groups_channelgroups' => "Channel Groups",
    'menu__teamspeak_teamspeak_snapshot' => "Snapshots",
    'menu__teamspeak_teamspeak_bans' => "Bans",

    /* !Titles */
    'teamspeak_test_title' => "Test TeamSpeak",
    'teamspeak_settings_title' => "TeamSpeak Settings",
    'teamspeak_members_title' => "Member UUIDs",
    'teamspeak_alert_title' => "TeamSpeak Alert",
    'teamspeak_clients_title' => "TeamSpeak Clients",
    'teamspeak_server_groups_title' => "Server Groups",
    'teamspeak_add_servergroup_title' => "Add Server Group",
    'teamspeak_edit_servergroup_title' => "Edit Server Group",
    'teamspeak_delete_servergroup_title' => "Delete Server Group",
    'teamspeak_copy_servergroup_title' => "Copy Server Group",
    'teamspeak_masspoke_title' => "Mass Poke",
    'teamspeak_channel_groups_title' => "Channel Groups",
    'teamspeak_edit_channelgroup_title' => "Edit Channel Group",
    'teamspeak_add_channelgroup_title' => "Add Channel Group",
    'teamspeak_delete_channelgroup_title' => "Delete Channel Group",
    'teamspeak_copy_channelgroup_title' => "Copy Channel Group",
    'teamspeak_snapshot_title' => "Server Snapshots",
    'teamspeak_bans_title' => "Bans",

    /* UUID Table */
    'teamspeak_resync_all' => "Re-sync all",
    'teamspeak_members_resynced' => "Re-synced all members",
    'teamspeak_table_s_member_id' => "Member",
    'teamspeak_table_s_uuid' => "UUID",
    'teamspeak_table_s_date' => "Date",
    'teamspeak_table_name' => "Member",
    'resync' => "Resync",
    'teamspeak_member_resynced' => "Member has been re-synced successfully",
    'teamspeak_member_deleted' => "UUID has been unlinked successfully",

    /* Alert Form */
    'alert_message' => "Message",
    'teamspeak_alert' => "Alert",
    'teamspeak_alert_sent' => "Alert sent",

    /* Settings form */
    'teamspeak_basic_settings' => "Basic",
    'teamspeak_other_settings' => "Other",
    'teamspeak_server_ip' => "TeamSpeak IP/Host",
    'teamspeak_server_ip_desc' => "Your TeamSpeak server IP address or hostname.",
    'teamspeak_virtual_port' => "TeamSpeak Server Port",
    'teamspeak_virtual_port_desc' => "Your TeamSpeak Virtual Port, default is 9987.",
    'teamspeak_query_port' => "TeamSpeak Query Port",
    'teamspeak_query_port_desc' => "Your TeamSpeak Query Port, in most cases this is 10011. If unsure, ask your TeamSpeak host.",
    'teamspeak_file_transfer_port' => "TeamSpeak File Transfer Port",
    'teamspeak_file_transfer_port_desc' => "Your TeamSpeak File Transfer, in most cases this is 30033. If unsure, ask your TeamSpeak host",
    'teamspeak_query_admin' => "TeamSpeak QueryAdmin Name",
    'teamspeak_query_admin_desc' => "Your TeamSpeak Query Admin username, usually serveradmin.",
    'teamspeak_query_password' => "TeamSpeak QueryAdmin Password",
    'teamspeak_query_password_desc' => "Your TeamSpeak Query Admin password.",
    'teamspeak_query_nickname' => "TeamSpeak Nickname",
    'teamspeak_query_nickname_desc' => "Nickname for this APP, will be shown on the TS when automated actions take place.",
    'teamspeak_uuid_on_register' => "Show UUID on register form?",
    'teamspeak_uuid_on_register_desc' => "Display a textbox on register form for new members to enter their UUID?",
    'teamspeak_uuid_on_register_force' => "Require UUID on register form?",
    'teamspeak_uuid_on_register_force_desc' => "Make the UUID textbox on the register form required?",
    'teamspeak_sync_bans' => "Sync bans?",
    'teamspeak_sync_bans_desc' => "If a member gets banned on the forum, this member will automatically be banned from the TeamSpeak.",
    'teamspeak_remove_groups' => "Remove additional TS groups?",
    'teamspeak_remove_groups_desc' => "If enabled, additional (non-linked) groups will be removed otherwise they will be ignored.",
    'teamspeak_remove_unlinked_groups' => "Remove Groups from unlinked UUIDs?",
    'teamspeak_remove_unlinked_groups_desc' => "Remove all groups from UUIDs that are not linked on the site?",
    'teamspeak_viewer_height' => "Viewer height",
    'teamspeak_viewer_height_desc' => "Viewer height in pixel",

    /* Server information form */
    'teamspeak_server_title' => "Edit TS Server",
    'teamspeak_server' => "Server",
    'teamspeak_name' => "Server Name",
    'teamspeak_welcomemessage' => "Welcome Message",
    'teamspeak_maxclients' => "Max. Clients",
    'teamspeak_reserved_slots' => "Reserved Slots",
    'teamspeak_transfer' => "Transfer",
    'teamspeak_download' => "Download",
    'teamspeak_max_download_total_bandwidth' => "Total Bandwidth",
    'teamspeak_max_download_total_bandwidth_desc' => "In Bytes.",
    'teamspeak_download_quota' => "Quota",
    'teamspeak_download_quota_desc' => "In Bytes.",
    'teamspeak_upload' => "Upload",
    'teamspeak_max_upload_total_bandwidth' => "Total Bandwidth",
    'teamspeak_max_upload_total_bandwidth_desc' => "In Bytes.",
    'teamspeak_upload_quota' => "Quota",
    'teamspeak_upload_quota_desc' => "In Bytes.",
    'teamspeak_anti_flood' => "Anti Flood",
    'teamspeak_antiflood_points_needed_ip_block' => "Points for IP Block",
    'teamspeak_antiflood_points_tick_reduce' => "Decrease for being good",
    'teamspeak_antiflood_points_needed_command_block' => "Points for Command Block",
    'teamspeak_security' => "Security",
    'teamspeak_needed_identity_security_level' => "Needed Security Level",
    'teamspeak_other' => "Other",
    'teamspeak_default_server_group' => "Default Server Group",
    'teamspeak_default_channel_group' => "Default Channel Group",
    'teamspeak_default_channel_admin_group' => "Default Channel Admin Group",

    /* Clients table */
    'teamspeak_client_nickname' => "Nickname",
    'teamspeak_clid' => "Client ID",
    'teamspeak_kick' => "Kick",
    'teamspeak_poke' => "Poke",
    'teamspeak_ban' => "Ban",
    'teamspeak_client_poked' => "Client has been poked",
    'teamspeak_client_kicked' => "Client has been kicked",
    'teamspeak_client_banned' => "Client has been banned",
    'teamspeak_masspoke' => "Mass Poke",

    /* Kick Form */
    'teamspeak_kick_title' => "Kick Client",
    'teamspeak_kick_message' => "Kick Message",

    /* Poke Form */
    'teamspeak_poke_title' => "Poke Client",
    'teamspeak_poke_message' => "Poke Message",

    /* Mass Poke Form */
    'teamspeak_poke_groups' => 'Groups to poke',
    'teamspeak_poke_groups_desc' => 'Clients in the selected groups will be poked.',
    'teamspeak_client_masspoked' => "Clients have been poked",

    /* Ban Form */
    'teamspeak_ban_title' => "Ban Client",
    'teamspeak_ban_message' => "Ban Reason",
    'teamspeak_ban_date' => "Ban until",
    'teamspeak_indefinite' => "Permanently",

    /* Member Form */
    'teamspeak_resync_uuids' => "Re-sync TeamSpeak Groups",

    /* !Group Form */
    'group__teamspeak_ServerGroups' => "TeamSpeak",
    'teamspeak_group' => "TeamSpeak Group",
    'teamspeak_group_desc' => "Which TS Group should members that are in this group get?",
    'teamspeak_require_uuid' => "Force UUID?",
    'teamspeak_require_uuid_desc' => "Members of this group will be forced to enter at least one UUID.",

    /* !Restrictions */
    'r__server' => "Server",
    'r__server_manage' => "Can edit server information?",
    'r__alert' => "Alert",
    'r__alert_manage' => "Can send global alert to the server?",
    'r__test' => "Test",
    'r__test_manage' => "Can test connection?",
    'r__servergroups' => "Server Groups",
    'r__servergroups_manage' => "Can manage server groups?",
    'r__channelgroups' => "Channel Groups",
    'r__channelgroups_manage' => "Can manage channel groups?",
    'r__members_manage' => "Can manage UUIDs?",
    'r__clients' => "Clients",
    'r__clients_manage' => "Can manage clients?",
    'r__snapshot' => "Snapshot",
    'r__snapshot_manage' => "Can manage Snapshots?",
    'r__bans' => "Bans",
    'r__bans_manage' => "Can manage Bans?",

    /* !Errors */
    'teamspeak_id_missing' => "ID parameter is missing!",
    'teamspeak_resync_groups_failed' => "Re-syncing the groups failed!",
    'teamspeak_serverinfo_error' => "There was an error loading the server information, please check the system logs for more information!",
    'teamspeak_update_server_failed' => "There was an error updating the server information, please check the system logs for more information!",
    'teamspeak_connection_error' => "Connection to the TeamSpeak server failed! Check the system/error logs for more information.",

    /* !Server Group Add Form */
    'teamspeak_servergroup_added' => "Created new server group",

    /* !Server Group Edit Form */
    'teamspeak_servergroup_sgid' => "Group ID",
    'teamspeak_servergroup_name' => "Group Name",
    'teamspeak_servergroup_edited' => "Server Group has been edited",
    'description' => "Description",
    'value' => "Value",
    'skip' => "Skip",
    'negated' => "Negated",
    'grant' => "Grant",

    /* !Server Group Delete Form */
    'teamspeak_force_delete' => "Force Deletion?",
    'teamspeak_force_delete_desc' => "If enabled, group will be deleted even if it still has members!",
    'teamspeak_servergroup_deleted' => "Server Group has been deleted",

    /* !Server Group Copy Form */
    'teamspeak_servergroup_name' => "Name",
    'teamspeak_servergroup_target_group' => "Target Group",
    'teamspeak_new_group' => "New Group",
    'teamspeak_servergroup_target_type' => "Type",
    'teamspeak_servergroup_copied' => "Server Group has been copied",

    /* Channel Group Table */
    'teamspeak_channelgroup_cgid' => "Group ID",
    'teamspeak_channelgroup_name' => "Name",

    /* !Channel Group Add Form */
    'teamspeak_channelgroup_target_type' => "Type",
    'teamspeak_channelgroup_added' => "Created new channel group",

    /* !Channel Group Edit Form */
    'teamspeak_channelgroup_edited' => "Channel Group has been edited",

    /* !Channel Group Copy Form */
    'teamspeak_channelgroup_target_group' => "Target Group",
    'teamspeak_channelgroup_copied' => "Channel Group has been copied",

    /* !Channel Group Delete Form */
    'teamspeak_channelgroup_deleted' => "Channel Group has been deleted",

    /* Snapshot Table */
    'teamspeak_s_id' => "Snapshot ID",
    'teamspeak_s_name' => "Name",
    'teamspeak_s_date' => "Created",
    'teamspeak_snapshot_create' => "Create New Snapshot",
    'teamspeak_deploy' => "Deploy",
    'teamspeak_snapshot_name' => "Name",
    'teamspeak_snapshot_name_desc' => "Give the snapshot a unique name.",
    'teamspeak_snapshot_created' => "Snapshot has been created",
    'teamspeak_snapshot_deleted' => "Snapshot has been deleted",
    'teamspeak_snapshot_deployed' => "Snapshot has been deployed",

    /* Ban Table */
    'teamspeak_banid' => "Ban ID",
    'teamspeak_ip' => "IP",
    'teamspeak_uid' => "UUID",
    'teamspeak_lastnickname' => "Last Nickname",
    'teamspeak_created' => "Banned at",
    'teamspeak_duration' => "Unban at",
    'teamspeak_invokername' => "Invoker Name",
    'teamspeak_reason' => "Reason",
    'teamspeak_bans_delete_all' => "Unban all",
    'teamspeak_unban' => "Unban",
    'teamspeak_deleted_ban' => "Unbanned",
    'teamspeak_deleted_all_bans' => "Unbanned everyone",

    /* !Front */

    /* !Navigation */
    'frontnavigation_teamspeak' => "TeamSpeak Sync",
    'module__teamspeak_teamspeak' => "TeamSpeak Sync",
    'page__teamspeak' => "TeamSpeak",

    /* Register form */
    'teamspeak_uuid' => "TeamSpeak UUID",
    'teamspeak_uuid_desc' => "Press CTRL+I on TeamSpeak to get your UUID.<br>If using TeamSpeak 3.1.0.1 or higher, you additionally have to click on the 'Go Advanced' link.",

    /* Sync table */
    'teamspeak_no_uuids' => "You have no UUIDs linked",
    'teamspeak_add_uuid' => "Add UUID",
    'teamspeak_uuids' => "TeamSpeak UUIDs",
    'teamspeak_unlink_uuid' => "Unlink UUID",
    'teamspeak_added_uuid' => "Added UUID",
    'teamspeak_removed_uuid' => "Unlinked UUID",
    'teamspeak_you_are_forced_to_enter_one_uuid' => "The administrator requires you to link at least one account",

    /* Add UUID Form */
    's_uuid' => "UUID",
    's_uuid_desc' => "Press CTRL+I on TeamSpeak to get your UUID.<br>If using TeamSpeak 3.1.0.1 or higher, you additionally have to click on the 'Go Advanced' link.",

    'teamspeak_uuid_setting' => "TeamSpeak",

    /* Block manager Viewer */
    'teamspeak_viewer' => "TeamSpeak Viewer",
    'block_teamspeakViewer' => "Viewer",
    'block_teamspeakViewer_desc' => "Shows your TeamSpeak server and lets members connect to it through clicking on a channel.",

    /* Front Title */
    'teamspeak_front' => "TeamSpeak",

    /* Viewer Setting Form */
    'teamspeak_viewer_groups' => "Allowed Groups",
    'teamspeak_viewer_groups_desc' => "Which groups are allowed to see the TeamSpeak Viewer?",
    'cacheTime' => "Cache Time",
    'showConnectButton' => "Display connect button?",
    'showConnectButton_desc' => "If enabled there will be an additional button which you can click to connect to the TS server.",
    'showGuestOnly' => "Only show channels that are visible to guests?",
    'showGuestOnly_desc' => "If turned off, it will show all clients even if they are in channels that require a high subscribe power (which guests do not have).",
    'cacheTime_desc' => "In Seconds.",
    'hideEmptyChannels' => "Hide empty Channels?",
    'hideParentChannels' => "Hide Parent Channels?",
);
