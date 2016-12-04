<?php
/**
 * @brief		removeUnlinkedGroups Task
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @subpackage	teamspeak
 * @since		04 Dec 2016
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\teamspeak\tasks;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
    header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * removeUnlinkedGroups Task
 */
class _removeUnlinkedGroups extends \IPS\Task
{
    /**
     * Execute
     *
     * If ran successfully, should return anything worth logging. Only log something
     * worth mentioning (don't log "task ran successfully"). Return NULL (actual NULL, not '' or 0) to not log (which will be most cases).
     * If an error occurs which means the task could not finish running, throw an \IPS\Task\Exception - do not log an error as a normal log.
     * Tasks should execute within the time of a normal HTTP request.
     *
     * @return	mixed	Message to log or NULL
     * @throws	\IPS\Task\Exception
     */
    public function execute()
    {
        /* If not enabled, just return NULL */
        if ( !\IPS\Settings::i()->teamspeak_remove_unlinked_groups )
        {
            return NULL;
        }

        try
        {
            $tsMember = \IPS\teamspeak\Member::i();
            $tsMember->syncUnlinkedUuids();
        }
        catch ( \IPS\teamspeak\Exception\ClientNotFoundException $e )
        {
            /* Ignore invalid UUIDs */
        }
        catch ( \Exception $e )
        {
            throw new \IPS\Task\Exception( $this, $e->getMessage() );
        }

        return NULL;
    }

    /**
     * Cleanup
     *
     * If your task takes longer than 15 minutes to run, this method
     * will be called before execute(). Use it to clean up anything which
     * may not have been done
     *
     * @return	void
     */
    public function cleanup()
    {

    }
}