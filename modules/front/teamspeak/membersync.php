<?php

namespace IPS\teamspeak\modules\front\teamspeak;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

use IPS\Db;
use IPS\Helpers\Form;
use IPS\Http\Url;
use IPS\Member;
use IPS\Output;
use IPS\Request;
use IPS\Session;
use IPS\teamspeak\Member as TsMember;
use IPS\teamspeak\Uuid;
use IPS\Theme;

/**
 * member_sync
 */
class _membersync extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return    void
	 */
	public function execute()
	{
		parent::execute();

		/* Prevent Guests from accessing this page */
		if ( !Member::loggedIn()->member_id )
		{
			Output::i()->error( 'no_module_permission_guest', '2P100/1', 403 );
		}
	}

	/**
	 * Display table containing all UUIDs that have been registered by this member.
	 *
	 * @return    void
	 */
	protected function manage()
	{
		$uuids = Member::loggedIn()->teamspeak_uuids;
		$forced = (bool) Request::i()->forced;

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak_front' );
		Output::i()->output = Theme::i()->getTemplate( 'tables', 'teamspeak' )->syncTable( $uuids, $forced );
	}

	/**
	 * Link a new UUID.
	 *
	 * @return void
	 */
	protected function add()
	{
		/* Check CSRF */
		Session::i()->csrfCheck();

		$tsMember = TsMember::i();

		$form = new Form();
		$form->addHeader( 'teamspeak_add_uuid' );
		$form->add( new Form\Text( 's_uuid', null, true, array(), function ( $value ) use ( $tsMember ) {

			if ( !$tsMember->isValidUuid( $value ) )
			{
				throw new \InvalidArgumentException( 'Invalid UUID' );
			}

			return $value;
		} ) );

		if ( $values = $form->values() )
		{
			try
			{
				$uuid = new Uuid;
				$uuid->member_id = Member::loggedIn()->member_id;
				$uuid->uuid = $values['s_uuid'];
				$uuid->save();

				$tsMember->addGroups( Member::loggedIn(), $values['s_uuid'] );

				Output::i()->redirect(
					Url::internal( 'app=teamspeak&module=teamspeak&controller=membersync', 'front' ),
					'teamspeak_added_uuid'
				);
			}
			catch ( Db\Exception $e )
			{
				if ( $e->getCode() === 1062 )
				{
					$form->error = 'This UUID has already been registered.';
				}
			}
		}

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'teamspeak' );

		if ( Request::i()->isAjax() )
		{
			Output::i()->output = $form->customTemplate(
				array(
					call_user_func_array( array( Theme::i(), 'getTemplate' ), array( 'forms', 'core' ) ),
					'popupTemplate'
				)
			);
		}
		else
		{
			Output::i()->output = $form;
		}
	}

	/**
	 * Unlink a UUID.
	 */
	protected function delete()
	{
		/* Check CSRF */
		Session::i()->csrfCheck();

		$tsMember = Uuid::load( Request::i()->id );

		if ( $tsMember->member_id !== Member::loggedIn()->member_id )
		{
			Output::i()->error( 'uuid_does_not_belong_to_you', '2P100/1', 403 );
		}

		$tsMember->delete();

		/* Redirect back to the table with a message that the UUID has been removed */
		Output::i()->redirect(
			Url::internal( 'app=teamspeak&module=teamspeak&controller=membersync', 'front' ), 'teamspeak_removed_uuid'
		);
	}
}