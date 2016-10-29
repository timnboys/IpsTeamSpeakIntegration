<?php

namespace IPS\teamspeak\Api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

//@see https://github.com/5G5/TS3ServerStatusViewer/blob/master/LICENSE
//TODO: Make theme selectable instead of forcing the old style ;)
//TODO: need to get themes with 16x16px icons or this is gonna be too complicated to be worth it (using CSS sprites)

use IPS\Log;
use IPS\Settings;

class _Viewer extends \IPS\teamspeak\Api
{
	protected $_serverDatas = array();
	protected $_channelDatas = array();
	protected $_userDatas = array();
	protected $_serverGroupFlags = array();
	protected $_channelGroupFlags = array();
	protected $_channelList = array();
	protected $_javascriptName;

	public $hideEmptyChannels = false;
	public $hideParentChannels = false;

	/**
	 *  Get Viewer instance.
	 *
	 * @param bool $login
	 * @return Viewer
	 */
	public static function viewerInstance( $login )
	{
		return new static( $login );
	}

	public function clearServerGroupFlags()
	{
		$this->_serverGroupFlags = array();
	}

	public function setServerGroupFlag( $serverGroupId, $image )
	{
		$this->_serverGroupFlags[$serverGroupId] = $image;
	}

	public function clearChannelGroupFlags()
	{
		$this->_channelGroupFlags = array();
	}

	public function setChannelGroupFlag( $channelGroupId, $image )
	{
		$this->_channelGroupFlags[$channelGroupId] = $image;
	}

	public function limitToChannels()
	{
		$this->_channelList = func_get_args();
	}

	protected function tsDecode( $str, $reverse = false )
	{
		$find = array( '\\\\', "\/", "\s", "\p", "\a", "\b", "\f", "\n", "\r", "\t", "\v" );
		$replace = array(
			chr( 92 ),
			chr( 47 ),
			chr( 32 ),
			chr( 124 ),
			chr( 7 ),
			chr( 8 ),
			chr( 12 ),
			chr( 10 ),
			chr( 3 ),
			chr( 9 ),
			chr( 11 )
		);

		if ( !$reverse )
		{
			return str_replace( $find, $replace, $str );
		}

		return str_replace( $replace, $find, $str );
	}

	protected function sortUsers( $a, $b )
	{
		if ( $a["client_talk_power"] != $b["client_talk_power"] )
		{
			return $a["client_talk_power"] > $b["client_talk_power"] ? -1 : 1;
		}

		return strcasecmp( $a["client_nickname"], $b["client_nickname"] );
	}

	protected function parseLine( $rawLine )
	{
		$data = array();
		$rawItems = explode( "|", $rawLine );

		foreach ( $rawItems as $rawItem )
		{
			$rawDataArr = explode( " ", $rawItem );
			$tempData = array();
			foreach ( $rawDataArr as $rawData )
			{
				$ar = explode( "=", $rawData, 2 );
				$tempData[$ar[0]] = isset( $ar[1] ) ? $this->tsDecode( $ar[1] ) : "";
			}

			$data[] = $tempData;
		}

		return $data;
	}

	protected function queryServer()
	{
		//TODO rewrite this using the API instead of manual commands (error checking)
		$ts = static::getInstance();
		$response = "";
		$response .= $ts->getElement( 'data', $ts->execOwnCommand( 3, "serverinfo" ) );
		$response .= $ts->getElement( 'data', $ts->execOwnCommand( 3, "channellist -topic -flags -voice -limits" ) );
		$response .= $ts->getElement( 'data', $ts->execOwnCommand( 3, "clientlist -uid -away -voice -groups" ) );
		$response .= $ts->getElement( 'data', $ts->execOwnCommand( 3, "servergrouplist" ) );
		$response .= $ts->getElement( 'data', $ts->execOwnCommand( 3, "channelgrouplist" ) );

		$this->logout();
		return $response;
	}

	protected function update()
	{
		$response = \substr( $this->queryServer(), 1 );
		$lines = explode( "\n\r\n\r", $response );

		if ( count( $lines ) == 1 )
		{
			$lines = explode( "\n\r \n\r", $response );
		}

		if ( count( $lines ) == 5 )
		{
			$this->_serverDatas = $this->parseLine( $lines[0] );
			$this->_serverDatas = $this->_serverDatas[0];

			$tmpChannels = $this->parseLine( $lines[1] );
			$hide = count( $this->_channelList ) > 0 || $this->hideEmptyChannels;

			foreach ( $tmpChannels as $channel )
			{
				$channel["show"] = !$hide;
				$this->_channelDatas[$channel["cid"]] = $channel;
			}

			$tmpUsers = $this->parseLine( $lines[2] );
			usort( $tmpUsers, array( $this, "sortUsers" ) );
			foreach ( $tmpUsers as $user )
			{
				if ( $user["client_type"] == 0 )
				{
					if ( !isset( $this->_userDatas[$user["cid"]] ) )
					{
						$this->_userDatas[$user["cid"]] = array();
					}
					$this->_userDatas[$user["cid"]][] = $user;
				}
			}

			$serverGroups = $this->parseLine( $lines[3] );
			foreach ( $serverGroups as $sg )
			{
				if ( $sg["iconid"] > 0 )
				{
					$this->setServerGroupFlag( $sg["sgid"], 'group_' . $sg["iconid"] );
				}
			}

			$channelGroups = $this->parseLine( $lines[4] );
			foreach ( $channelGroups as $cg )
			{
				if ( $cg["iconid"] > 0 )
				{
					$this->setChannelGroupFlag( $cg['cgid'], 'group_' . $cg["iconid"] );
				}
			}
		}
		else
		{
			throw new \Exception( "Invalid server response" );
		}
	}

	protected function setShowFlag( $channelIds )
	{
		if ( !is_array( $channelIds ) )
		{
			$channelIds = array( $channelIds );
		}
		foreach ( $channelIds as $cid )
		{
			if ( isset( $this->_channelDatas[$cid] ) )
			{
				$this->_channelDatas[$cid]["show"] = true;
				if ( !$this->hideParentChannels && $this->_channelDatas[$cid]["pid"] != 0 )
				{
					$this->setShowFlag( $this->_channelDatas[$cid]["pid"] );
				}
			}
		}
	}

	protected function renderUsers( $channelId )
	{
		$id = 1;
		$content = array();
		if ( isset( $this->_userDatas[$channelId] ) )
		{
			foreach ( $this->_userDatas[$channelId] as $user )
			{
				if ( $user["client_type"] == 0 )
				{
					$name = $user["client_nickname"];

					$icon = "16x16_player_off";
					if ( $user["client_away"] == 1 )
					{
						$icon = "16x16_away";
					}
					else
					{
						if ( $user["client_flag_talking"] == 1 )
						{
							$icon = "16x16_player_on";
						}
						else
						{
							if ( $user["client_output_hardware"] == 0 )
							{
								$icon = "16x16_hardware_output_muted";
							}
							else
							{
								if ( $user["client_output_muted"] == 1 )
								{
									$icon = "16x16_output_muted";
								}
								else
								{
									if ( $user["client_input_hardware"] == 0 )
									{
										$icon = "16x16_hardware_input_muted";
									}
									else
									{
										if ( $user["client_input_muted"] == 1 )
										{
											$icon = "16x16_input_muted";
										}
									}
								}
							}
						}
					}

					$flags = array();

					if ( isset( $this->_channelGroupFlags[$user["client_channel_group_id"]] ) )
					{
						$flags[] = $this->_channelGroupFlags[$user["client_channel_group_id"]];
					}

					$serverGroups = explode( ",", $user["client_servergroups"] );
					foreach ( $serverGroups as $serverGroup )
					{
						if ( isset( $this->_serverGroupFlags[$serverGroup] ) )
						{
							$flags[] = $this->_serverGroupFlags[$serverGroup];
						}
					}

					$content[$id]['icon'] = $icon;
					$content[$id]['name'] = $name;
					$content[$id]['flags'] = $flags;
					$id += 1;
				}
			}
		}

		return $content;
	}

	protected function renderChannels( $channelId )
	{
		$content = array();
		foreach ( $this->_channelDatas as $channel )
		{
			if ( $channel["pid"] == $channelId )
			{
				if ( $channel["show"] )
				{
					$name = $channel["channel_name"];
					$title = $name . " [" . $channel["cid"] . "]";
					$link = "javascript:ts3ssvconnect('" . $this->_javascriptName . "'," . $channel["cid"] . ")";

					$icon = "16x16_channel_green";
					if ( $channel["channel_maxclients"] > -1 && ( $channel["total_clients"] >= $channel["channel_maxclients"] ) )
					{
						$icon = "16x16_channel_red";
					}
					else
					{
						if ( $channel["channel_maxfamilyclients"] > -1 && ( $channel["total_clients_family"] >= $channel["channel_maxfamilyclients"] ) )
						{
							$icon = "16x16_channel_red";
						}
						else
						{
							if ( $channel["channel_flag_password"] == 1 )
							{
								$icon = "16x16_channel_yellow";
							}
						}
					}

					$flags = array();
					if ( $channel["channel_flag_default"] == 1 )
					{
						$flags[] = '16x16_default';
					}
					if ( $channel["channel_needed_talk_power"] > 0 )
					{
						$flags[] = '16x16_moderated';
					}
					if ( $channel["channel_flag_password"] == 1 )
					{
						$flags[] = '16x16_register';
					}
					$cid = $channel["cid"];

					$users = $this->renderUsers( $cid );
					$childs = $this->renderChannels( $cid );


					$content[$cid]['link'] = $link;
					$content[$cid]['title'] = $title;
					$content[$cid]['icon'] = $icon;
					$content[$cid]['name'] = $name;
					$content[$cid]['flags'] = $flags;
					$content[$cid]['users'] = $users;
					$content[$cid]['childs'] = $childs;
				}
			}
		}

		return $content;
	}

	public function render()
	{
		try
		{
			$this->update();

			if ( $this->hideEmptyChannels && count( $this->_channelList ) > 0 )
			{
				$this->setShowFlag( array_intersect( $this->_channelList, array_keys( $this->_userDatas ) ) );
			}
			else
			{
				if ( $this->hideEmptyChannels )
				{
					$this->setShowFlag( array_keys( $this->_userDatas ) );
				}
				else
				{
					if ( count( $this->_channelList ) > 0 )
					{
						$this->setShowFlag( $this->_channelList );
					}
				}
			}

			$host = Settings::i()->teamspeak_server_ip;
			$port = $this->_serverDatas["virtualserver_port"];
			$name = $this->_serverDatas["virtualserver_name"];
			$icon = "16x16_server_green";
			$this->_javascriptName = $javascriptName = preg_replace( "#[^a-z-A-Z0-9]#", "-", $host . "-" . $port );

			$channels = $this->renderChannels( 0 );
			$content = array();

			$content['jsName'] = $javascriptName;
			$content['channels'] = $channels;
			$content['icon'] = $icon;
			$content['name'] = $name;
		}
		catch ( \Exception $e )
		{
			$this->logout();
			Log::log( $e );
			$content = 'The Viewer could not be loaded, please check the error logs.';
		}
		
		return $content;
	}
}