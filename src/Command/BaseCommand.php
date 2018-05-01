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

namespace Eventum\IrcBot\Command;

use Eventum\IrcBot\UserDb;
use Eventum\RPC\EventumXmlRpcClient;
use Net_SmartIRC;

abstract class BaseCommand
{
    /** @var Net_SmartIRC */
    protected $irc;
    /** @var UserDb */
    protected $userDb;
    /** @var EventumXmlRpcClient */
    protected $rpcClient;

    public function __construct(Net_SmartIRC $irc, UserDb $userDb = null, EventumXmlRpcClient $rpcClient = null)
    {
        $this->irc = $irc;
        $this->userDb = $userDb;
        $this->rpcClient = $rpcClient;
    }

    /**
     * Method used to send a message to the given target.
     *
     * @param string $target The target for this message
     * @param string|string[] $response The message to send
     * @param int $priority the priority level of the message
     */
    protected function sendResponse($target, $response, $priority = SMARTIRC_MEDIUM)
    {
        if (strpos($target, '#') !== 0) {
            $type = SMARTIRC_TYPE_QUERY;
        } else {
            $type = SMARTIRC_TYPE_CHANNEL;
        }
        $this->irc->message($type, $target, $response, $priority);
    }
}
