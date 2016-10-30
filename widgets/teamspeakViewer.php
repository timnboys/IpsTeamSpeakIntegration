<?php
/**
 * @brief        teamspeakViewer Widget
 * @author        <a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright    (c) 2001 - 2016 Invision Power Services, Inc.
 * @license        http://www.invisionpower.com/legal/standards/
 * @package        IPS Community Suite
 * @subpackage    teamspeak
 * @since        18 Oct 2016
 * @version        SVN_VERSION_NUMBER
 */

namespace IPS\teamspeak\widgets;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

use IPS\Helpers\Form;
use IPS\Member;
use IPS\teamspeak\Api\Viewer;

/**
 * teamspeakViewer Widget
 */
class _teamspeakViewer extends \IPS\Widget\StaticCache
{
	/**
	 * @brief    Widget Key
	 */
	public $key = 'teamspeakViewer';

	/**
	 * @brief    App
	 */
	public $app = 'teamspeak';

	/**
	 * @brief    Plugin
	 */
	public $plugin = '';

	/**
	 * Initialise this widget
	 *
	 * @return void
	 */
	public function init()
	{
		if ( isset( $this->configuration['cacheTime'] ) )
		{
			$caches = static::getCaches( $this->key, $this->app, $this->plugin );

			foreach ( $caches as $time )
			{
				if ( $time + $this->configuration['cacheTime'] <= time() )
				{
					static::deleteCaches( $this->key, $this->app, $this->plugin );
					break;
				}
			}
		}
		parent::init();
	}

	/**
	 * Specify widget configuration
	 *
	 * @param    null|Form $form Form object
	 * @return    null|Form
	 */
	public function configuration( &$form = null )
	{
		if ( $form === null )
		{
			$form = new Form;
		}

		//		$themes = array(
		//			'default' => 'Default',
		//			'default_colored_2014' => 'Colored 2014',
		//			'default_mono_2014' => 'Mono 2014'
		//		);

		$form->add(
			new Form\Select(
				'teamspeak_viewer_groups',
				isset( $this->configuration['teamspeak_viewer_groups'] ) ? $this->configuration['teamspeak_viewer_groups'] : 'all',
				true, array(
					'options' => Member\Group::groups(),
					'parse' => 'normal',
					'multiple' => true,
					'unlimited' => 'all',
					'unlimitedLang' => 'all_groups'
				)
			)
		);
		$form->add(
			new Form\YesNo(
				'showConnectButton',
				isset( $this->configuration['showConnectButton'] ) ? $this->configuration['showConnectButton'] : 0, true
			)
		);
		$form->add(
			new Form\YesNo(
				'showGuestOnly',
				isset( $this->configuration['showGuestOnly'] ) ? $this->configuration['showGuestOnly'] : 1, true
			)
		);
		$form->add(
			new Form\Number(
				'cacheTime', isset( $this->configuration['cacheTime'] ) ? $this->configuration['cacheTime'] : 60 * 15,
				true, array( 'min' => 30 )
			)
		);
		$form->add(
			new Form\YesNo(
				'hideEmptyChannels',
				isset( $this->configuration['hideEmptyChannels'] ) ? $this->configuration['hideEmptyChannels'] : 0, true
			)
		);
		$form->add(
			new Form\YesNo(
				'hideParentChannels',
				isset( $this->configuration['hideParentChannels'] ) ? $this->configuration['hideParentChannels'] : 0,
				true
			)
		);
		//$form->add( new \IPS\Helpers\Form\Select( 'theme', isset( $this->configuration['theme'] ) ? $this->configuration['theme'] : 'default', true, array( 'options' => $themes ) ) );
		return $form;
	}

	/**
	 * Ran before saving widget configuration
	 *
	 * @param    array $values Values from form
	 * @return    array
	 */
	public function preConfig( $values )
	{
		return $values;
	}

	/**
	 * Render a widget
	 *
	 * @return    string
	 */
	public function render()
	{
		if ( isset( $this->configuration['teamspeak_viewer_groups'] ) &&
			$this->configuration['teamspeak_viewer_groups'] !== 'all' &&
			!Member::loggedIn()->inGroup( $this->configuration['teamspeak_viewer_groups'] )
		)
		{
			return "";
		}

		$hideEmptyChannels = isset( $this->configuration['hideEmptyChannels'] ) ? $this->configuration['hideEmptyChannels'] : false;
		$hideParentChannels = isset( $this->configuration['hideParentChannels'] ) ? $this->configuration['hideParentChannels'] : false;
		$login = isset( $this->configuration['showGuestOnly'] ) ? !$this->configuration['showGuestOnly'] : false;
		$showConnectButton = isset( $this->configuration['showConnectButton'] ) ? $this->configuration['showConnectButton'] : false;

		try
		{
			$viewer = Viewer::viewerInstance( $login );

			$viewer->hideEmptyChannels = $hideEmptyChannels;
			$viewer->hideParentChannels = $hideParentChannels;
			$content = $viewer->render();
		}
		catch ( \Exception $e )
		{
			return "";
		}

		//		$theme = 'default';
		//		if ( isset( $this->configuration['theme'] ) )
		//		{
		//			$theme = $this->configuration['theme'];
		//		}

		return $this->output( $content, $showConnectButton );
	}
}