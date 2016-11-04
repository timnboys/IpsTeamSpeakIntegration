<?php

namespace IPS\teamspeak\modules\front\teamspeak;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

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
		if ( !\IPS\Member::loggedIn()->member_id )
		{
			\IPS\Output::i()->error( 'no_module_permission_guest', '2P100/1', 403 );
		}
	}

	/**
	 * Display table containing all UUIDs that have been registered by this member.
	 *
	 * @return    void
	 */
	protected function manage()
	{
		$uuids = \IPS\Member::loggedIn()->teamspeak_uuids;
		$forced = (bool) \IPS\Request::i()->forced;

		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak_front' );
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'tables', 'teamspeak' )->syncTable( $uuids, $forced );
	}

	/**
	 * Link a new UUID.
	 *
	 * @return void
	 */
	protected function add()
	{
		/* Check CSRF */
		\IPS\Session::i()->csrfCheck();

		$tsMember = \IPS\teamspeak\Member::i();

		$form = new \IPS\Helpers\Form();
		$form->addHeader( 'teamspeak_add_uuid' );
		$form->add( new \IPS\Helpers\Form\Text( 's_uuid', null, true, array(), function ( $value ) use ( $tsMember ) {

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
				$uuid = new \IPS\teamspeak\Uuid;
				$uuid->member_id = \IPS\Member::loggedIn()->member_id;
				$uuid->uuid = $values['s_uuid'];
				$uuid->save();

				$tsMember->addGroups( \IPS\Member::loggedIn(), $values['s_uuid'] );

				\IPS\Output::i()->redirect(
					\IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=membersync', 'front' ),
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
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'teamspeak' );

		if ( \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->output = $form->customTemplate(
				array(
					call_user_func_array( array( \IPS\Theme::i(), 'getTemplate' ), array( 'forms', 'core' ) ),
					'popupTemplate'
				)
			);
		}
		else
		{
			\IPS\Output::i()->output = $form;
		}
	}

	/**
	 * Unlink a UUID.
	 */
	protected function delete()
	{
		/* Check CSRF */
		\IPS\Session::i()->csrfCheck();

		$tsMember = \IPS\teamspeak\Uuid::load( \IPS\Request::i()->id );

		if ( $tsMember->member_id !== \IPS\Member::loggedIn()->member_id )
		{
			\IPS\Output::i()->error( 'uuid_does_not_belong_to_you', '2P100/1', 403 );
		}

		$tsMember->delete();

		/* Redirect back to the table with a message that the UUID has been removed */
		\IPS\Output::i()->redirect(
			\IPS\Http\Url::internal( 'app=teamspeak&module=teamspeak&controller=membersync', 'front' ), 'teamspeak_removed_uuid'
		);
	}
}