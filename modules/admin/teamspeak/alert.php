<?php


namespace IPS\teamspeak\modules\admin\teamspeak;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

use IPS\Application;
use IPS\Dispatcher;
use IPS\Helpers\Form;
use IPS\Http\Url;
use IPS\Member;
use IPS\Output;
use IPS\teamspeak\Api\Alert;

/**
 * Alert
 */
class _Alert extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return    void
	 */
	public function execute()
	{
		Dispatcher::i()->checkAcpPermission( 'alert_manage' );
		parent::execute();
		Application::load( 'teamspeak' )->isConfigured();
	}

	/**
	 * ...
	 *
	 * @return    void
	 */
	protected function manage()
	{
		$form = new Form( 'alert', 'teamspeak_alert' );
		$form->add( new Form\Text( 'alert_message', null, true ) );

		if ( $values = $form->values() )
		{
			$teamspeak = Alert::i();
			$teamspeak->sendMessage( $values['alert_message'] );
			Output::i()->redirect(
				Url::internal( 'app=teamspeak&module=teamspeak&controller=alert', 'admin' ),
				'teamspeak_alert_sent'
			);
		}

		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_alert_title' );
		Output::i()->output = $form;
	}
}