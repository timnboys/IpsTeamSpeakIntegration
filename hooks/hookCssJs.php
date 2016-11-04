//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

abstract class teamspeak_hook_hookCssJs extends _HOOK_CLASS_
{
	protected static function baseJs()
	{
		parent::baseJS();
		if ( !\IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'viewer.js', 'teamspeak', 'interface' ) );
		}
	}

	public static function baseCss()
	{
		parent::baseCss();
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'viewer.css', 'teamspeak', 'front' ) );
	}
}