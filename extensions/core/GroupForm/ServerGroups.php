<?php
/**
 * @brief        Admin CP Group Form
 * @author        <a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright    (c) 2001 - 2016 Invision Power Services, Inc.
 * @license        http://www.invisionpower.com/legal/standards/
 * @package        IPS Community Suite
 * @subpackage    TeamSpeak Integration
 * @since        20 Oct 2016
 * @version        SVN_VERSION_NUMBER
 */

namespace IPS\teamspeak\extensions\core\GroupForm;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Admin CP Group Form
 */
class _ServerGroups
{
	/**
	 * Process Form
	 *
	 * @param    \IPS\Helpers\Form $form The form
	 * @param    \IPS\Member\Group $group Existing Group
	 * @return    void
	 */
	public function process( &$form, $group )
	{
		if ( !\IPS\Application::load( 'teamspeak' )->isConfigured( true ) )
		{
			$form->add(
				new \IPS\Helpers\Form\TextArea(
					'teamspeak_error',
					'Please configure the teamspeak app before trying to use this.',
					false, array( 'disabled' => true )
				)
			);

			return;
		}

		try
		{
			$groups = \IPS\teamspeak\Api\Group::getCachedServerGroups();

			if ( $groups === null )
			{
				$groupClass = new \IPS\teamspeak\Api\Group();
				$groups = $groupClass->getServerGroups();
			}

			$groups[-1] = 'None';

			$options['options'] = $groups;
			$form->add(
				new \IPS\Helpers\Form\Select(
					'teamspeak_group', isset( $group->teamspeak_group ) ? $group->teamspeak_group : -1, true, $options
				)
			);
			$form->add(
				new \IPS\Helpers\Form\YesNo(
					'teamspeak_require_uuid',
					isset( $group->teamspeak_require_uuid ) ? $group->teamspeak_require_uuid : 0, false
				)
			);
		}
		catch ( \Exception $e )
		{
			/* Connection error to the server */
			$form->add(
				new \IPS\Helpers\Form\TextArea(
					'teamspeak_error',
					'Connection to the TeamSpeak server failed, please check the error logs for more information.',
					false, array( 'disabled' => true )
				)
			);
		}
	}

	/**
	 * Save
	 *
	 * @param    array $values Values from form
	 * @param    \IPS\Member\Group $group The group
	 * @return    void
	 */
	public function save( $values, &$group )
	{
		$group->teamspeak_group = $values['teamspeak_group'];
		$group->teamspeak_require_uuid = $values['teamspeak_require_uuid'];
	}
}