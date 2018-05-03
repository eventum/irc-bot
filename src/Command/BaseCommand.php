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

use Eventum\IrcBot\SendResponseTrait;
use Eventum\IrcBot\Traits\LoggerTrait;
use Eventum\IrcBot\UserDb;
use Eventum\RPC\EventumXmlRpcClient;
use Net_SmartIRC;
use Psr\Log\LoggerInterface;

abstract class BaseCommand
{
    use SendResponseTrait;
    use LoggerTrait;

    /** @var UserDb */
    protected $userDb;
    /** @var EventumXmlRpcClient */
    protected $rpcClient;

    public function __construct(LoggerInterface $logger, Net_SmartIRC $irc, UserDb $userDb = null, EventumXmlRpcClient $rpcClient = null)
    {
        $this->irc = $irc;
        $this->userDb = $userDb;
        $this->rpcClient = $rpcClient;
        $this->logger = $logger;
    }
}
