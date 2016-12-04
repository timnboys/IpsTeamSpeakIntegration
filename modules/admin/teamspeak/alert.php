<?php

namespace IPS\teamspeak\modules\admin\teamspeak;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

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
		\IPS\Dispatcher::i()->checkAcpPermission( 'alert_manage' );
		parent::execute();
		\IPS\Application::load( 'teamspeak' )->isConfigured();
	}

	/**
	 * Show form to send a global alert to the TeamSpeak server.
	 *
	 * @return    void
	 */
	protected function manage()
	{
		/* Build alert form */
		$form = new \IPS\Helpers\Form( 'alert', 'teamspeak_alert' );
		$form->add( new \IPS\Helpers\Form\Text( 'alert_message', null, true ) );

		if ( $values = $form->values() )
		{
			$teamspeak = new \IPS\teamspeak\Api\Alert();
			$teamspeak->sendMessage( $values['alert_message'] );
			\IPS\Output::i()->redirect(
				\IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=alert', 'admin' ),
				'teamspeak_alert_sent'
			);
		}

		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_alert_title' );
		\IPS\Output::i()->output = $form;
	}
}