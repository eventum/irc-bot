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

use Eventum\RPC\XmlRpcException;
use Net_SmartIRC;
use Net_SmartIRC_data;

class ClockInCommand extends BaseCommand
{
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
        try {
            $result = $this->rpcClient->timeClock($command);
        } catch (XmlRpcException $e) {
            $this->sendResponse($data->nick, 'Error: Temporary error');

            return;
        }

        $this->sendResponse($data->nick, $result);
    }

    /**
     * Format is "list-clocked-in"
     *
     * @param Net_SmartIRC $irc
     * @param Net_SmartIRC_data $data
     */
    final public function listClockedIn(Net_SmartIRC $irc, Net_SmartIRC_data $data)
    {
        if (!$this->userDb->has($data->nick)) {
            $this->sendResponse($data->nick, 'Error: You need to be authenticated to run this command.');

            return;
        }

        try {
            $list = $this->rpcClient->getClockedInList();
        } catch (XmlRpcException $e) {
            $this->sendResponse($data->nick, 'Error: Temporary error');

            return;
        }

        if (count($list) == 0) {
            $this->sendResponse($data->nick, 'There are no clocked-in users as of now.');

            return;
        }

        $this->sendResponse($data->nick, 'The following is the list of clocked-in users:');
        foreach ($list as $name => $email) {
            $this->sendResponse($data->nick, "$name: $email");
        }
    }
}
