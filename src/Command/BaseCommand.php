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

use Eventum\IrcBot\IrcClient;
use Eventum\IrcBot\UserDb;
use Eventum\RPC\EventumXmlRpcClient;

abstract class BaseCommand
{
    /** @var IrcClient */
    protected $ircClient;
    /** @var UserDb */
    protected $userDb;
    /** @var EventumXmlRpcClient */
    protected $rpcClient;

    public function __construct(IrcClient $ircClient, UserDb $userDb = null, EventumXmlRpcClient $rpcClient = null)
    {
        $this->ircClient = $ircClient;
        $this->userDb = $userDb;
        $this->rpcClient = $rpcClient;
    }

    protected function sendResponse($target, $response, $priority = SMARTIRC_MEDIUM)
    {
        $this->ircClient->sendResponse($target, $response, $priority);
    }
}
