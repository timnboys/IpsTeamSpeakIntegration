//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

use IPS\Output;
use IPS\Request;
use IPS\Theme;

abstract class teamspeak_hook_hookCssJs extends _HOOK_CLASS_
{
	protected static function baseJs()
	{
		parent::baseJS();
		if ( !Request::i()->isAjax() )
		{
			Output::i()->jsFiles = array_merge( Output::i()->jsFiles, Output::i()->js( 'viewer.js', 'teamspeak', 'interface' ) );
		}
	}

	public static function baseCss()
	{
		parent::baseCss();
		Output::i()->cssFiles = array_merge( Output::i()->cssFiles, Theme::i()->css( 'viewer.css', 'teamspeak', 'front' ) );
	}
}