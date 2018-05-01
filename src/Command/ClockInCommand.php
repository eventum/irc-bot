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
use Net_SmartIRC;
use Net_SmartIRC_data;

class ClockInCommand extends BaseCommand
{
    /** @var UserDb */
    private $userDb;
    /** @var EventumXmlRpcClient */
    private $rpcClient;

    public function __construct(IrcClient $ircClient, UserDb $userDb, EventumXmlRpcClient $rpcClient)
    {
        parent::__construct($ircClient);
        $this->userDb = $userDb;
        $this->rpcClient = $rpcClient;
    }

    /**
     * Format is "clock [in|out]"
     *
     * @param Net_SmartIRC $irc
     * @param Net_SmartIRC_data $data
     */
    final public function clock(Net_SmartIRC $irc, Net_SmartIRC_data $data)
    {
        if (!$this->userDb->has($data->nick)) {
            $this->sendResponse($data->nick, 'Error: You need to be authenticated to run this command.');

            return;
        }

        switch (count($data->messageex)) {
            case 1:
                break;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 2:
                if (in_array($data->messageex[1], ['in', 'out'])) {
                    break;
                }
            // fall through to an error
            // no break
            default:
                $this->sendResponse(
                    $data->nick, 'Error: wrong parameter count for "CLOCK" command. Format is "!clock [in|out]".'
                );

                return;
        }

        $command = isset($data->messageex[1]) ? $data->messageex[1] : null;

        // XXX: the action will be performed as system user
        $result = $this->rpcClient->timeClock($command);

        $this->sendResponse($data->nick, $result);
    }
}
