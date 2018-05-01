<?php

/*
 * This file is part of the Eventum (Issue Tracking System) package.
 *
 * @copyright (c) Eventum Team
 * @license GNU General Public License, version 2 or later (GPL-2+)
 *
 * For the full copyright and license information,
 * please see the COPYING and AUTHORS files
 * that were distributed with this source code.
 */

namespace Eventum\IrcBot;

use Eventum\IrcBot\Event\EventListenerInterface;
use Net_SmartIRC;

class ProcessControl implements EventListenerInterface
{
    public $shutdown = false;

    public function register(Net_SmartIRC $irc)
    {
        $handler = function ($signal = null) use ($irc) {
            $this->handler($irc, $signal);
        };

        if ($this->supports()) {
            pcntl_signal(SIGINT, $handler);
            pcntl_signal(SIGTERM, $handler);
        }

        // NOTE: signal handler is not enough because stream_select() also catches the signals and aborts the process
        // so register the shutdown handler as well
        register_shutdown_function($handler);
    }

    protected function handler(Net_SmartIRC $irc, $signal = null)
    {
        $this->shutdown = true;
        // if stream_select receives signal, SmartIRC will automatically retry
        // disable reconnect, and die
        // this is not needed if we are connected,
        // but unable to query such state, all variables and methods related to it are not public
        $irc->setAutoRetry(false);

        if ($signal) {
            $irc->log(SMARTIRC_DEBUG_NOTICE, "Got signal[$signal]; shutdown", __FILE__, __LINE__);
            $irc->quit('Terminated');
        } else {
            $irc->log(SMARTIRC_DEBUG_NOTICE, 'Shutdown handler', __FILE__, __LINE__);
            $irc->quit('Bye');
        }

        // QUIT has no effect if not connected
        $irc->disconnect();
    }

    private function supports()
    {
        return function_exists('pcntl_signal');
    }
}
