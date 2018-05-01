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

use Net_SmartIRC;
use Net_SmartIRC_data;

class HelpCommand extends BaseCommand
{
    /**
     * @param Net_SmartIRC $irc
     * @param Net_SmartIRC_data $data
     */
    final public function help(Net_SmartIRC $irc, Net_SmartIRC_data $data)
    {
        $commands = [
            'auth' => 'Format is "auth user@example.com password"',
            'clock' => 'Format is "clock [in|out]"',
            'help' => 'Display this usage',
            'list-clocked-in' => 'Format is "list-clocked-in"',
        ];

        $this->sendResponse($data->nick, 'This is the list of available commands:');
        foreach ($commands as $command => $description) {
            $this->sendResponse($data->nick, "$command: $description");
        }
    }
}
