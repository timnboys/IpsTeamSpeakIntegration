//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class teamspeak_hook_user_dropdown_menu extends _HOOK_CLASS_
{

/* !Hook Data - DO NOT REMOVE */
public static function hookData() {
 return array_merge_recursive( array (
  'userBar' => 
  array (
    0 => 
    array (
      'selector' => '#elUserLink_menu > li.ipsMenu_item[data-menuitem=\'ignoredUsers\']',
      'type' => 'add_after',
      'content' => '<li class="ipsMenu_item" data-menuitem="tsSyncPage">
<a href="{url="app=teamspeak&module=teamspeak&controller=membersync" seoTemplate="teamspeak_member_sync"}">
{lang="frontnavigation_teamspeak"}
</a>
</li>',
    ),
  ),
), parent::hookData() );
}
/* End Hook Data */


}
