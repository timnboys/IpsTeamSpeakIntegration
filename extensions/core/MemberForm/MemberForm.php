<?php
/**
 * @brief		Admin CP Member Form
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @subpackage	TeamSpeak Integration
 * @since		30 Oct 2016
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\teamspeak\extensions\core\MemberForm;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
    header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Admin CP Member Form
 */
class _MemberForm
{
    /**
     * Action Buttons
     *
     * @param	\IPS\Member	$member	The Member
     * @return	array
     */
    public function actionButtons( $member )
    {
        return array(
            'resync_uuids'	=> array(
                'title'		=> 'teamspeak_resync_uuids',
                'icon'		=> 'refresh',
                'link'		=> \IPS\Http\Url::internal( "app=teamspeak&module=members&controller=members&do=resyncAll&id={$member->member_id}" ),
                'data'      => array( 'confirm' => '' )
            )
        );
    }

    /**
     * Process Form
     *
     * @param	\IPS\Helpers\Form		$form	The form
     * @param	\IPS\Member				$member	Existing Member
     * @return	void
     */
    public function process( &$form, $member )
    {}

    /**
     * Save
     *
     * @param	array				$values	Values from form
     * @param	\IPS\Member			$member	The member
     * @return	void
     */
    public function save( $values, &$member )
    {}
}