<?php
/**
 * @brief		Front Navigation Extension: TeamSpeakTab
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @subpackage	TeamSpeak Integration
 * @since		21 Oct 2016
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\teamspeak\extensions\core\FrontNavigation;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
    header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Front Navigation Extension: TeamSpeakTab
 */
class _TeamSpeakTab extends \IPS\core\FrontNavigation\FrontNavigationAbstract
{
    /**
     * Get Type Title which will display in the AdminCP Menu Manager
     *
     * @return	string
     */
    public static function typeTitle()
    {
        return \IPS\Member::loggedIn()->language()->addToStack('frontnavigation_teamspeak');
    }

    /**
     * Can this item be used at all?
     * For example, if this will link to a particular feature which has been diabled, it should
     * not be available, even if the user has permission
     *
     * @return	bool
     */
    public static function isEnabled()
    {
        return TRUE;
    }

    /**
     * Can the currently logged in user access the content this item links to?
     *
     * @return	bool
     */
    public function canAccessContent()
    {
        return (bool) \IPS\Member::loggedIn()->member_id;
    }

    /**
     * Get Title
     *
     * @return	string
     */
    public function title()
    {
        return \IPS\Member::loggedIn()->language()->addToStack('frontnavigation_teamspeak');
    }

    /**
     * Get Link
     *
     * @return	\IPS\Http\Url
     */
    public function link()
    {
        return \IPS\Http\Url::internal( "app=teamspeak&module=teamspeak&controller=membersync", 'front', 'teamspeak_member_sync' );
    }

    /**
     * Is Active?
     *
     * @return	bool
     */
    public function active()
    {
        return \IPS\Dispatcher::i()->application->directory === 'teamspeak';
    }

    /**
     * Children
     *
     * @param	bool	$noStore	If true, will skip datastore and get from DB (used for ACP preview)
     * @return	array
     */
    public function children( $noStore=FALSE )
    {
        return NULL;
    }
}