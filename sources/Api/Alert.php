<?php

namespace IPS\teamspeak\Api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _Alert extends \IPS\teamspeak\Api\AbstractConnection
{
	/**
	 * sendMessage
	 *
	 * Sends a text message a specified target. The type of the target is determined by targetmode while target specifies the ID of the recipient, whether it be a virtual server, a channel or a client.<br>
	 * <b>Hint:</b> You can just write to the channel the query client is in. See link in description for details.
	 *
	 * <b>Modes:</b>
	 * <ul>
	 *    <li><b>1:</b> send to client</li>
	 *    <li><b>2:</b> send to channel</li>
	 *    <li><b>3:</b> send to server</li>
	 * </ul><br>
	 * <b>Targets:</b>
	 * <ul>
	 *    <li>clientID</li>
	 *    <li>channelID</li>
	 *    <li>serverID</li>
	 * </ul>
	 *
	 * @author     Par0noid Solutions
	 * @access        public
	 * @param        integer $mode
	 * @param        integer $target
	 * @param        string $msg Message
	 * @see        http://forum.teamspeak.com/showthread.php/84280-Sendtextmessage-by-query-client http://forum.teamspeak.com/showthread.php/84280-Sendtextmessage-by-query-client
	 * @return     boolean    success
	 * @todo different methods for different modes for easier reference
	 */
	public function sendMessage( $msg, $mode = 3, $target = 3 )
	{
		return (bool) $this->instance->getElement( 'success', $this->instance->sendMessage( $mode, $target, $msg ) );
	}
}