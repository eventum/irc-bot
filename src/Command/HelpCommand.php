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
use Net_SmartIRC;
use Net_SmartIRC_data;

class HelpCommand
{
    /** @var IrcClient */
    private $ircClient;

    public function __construct(IrcClient $ircClient)
    {
        $this->ircClient = $ircClient;
    }

    /**
     * @param Net_SmartIRC $irc
     * @param Net_SmartIRC_data $data
     */
    final public function help(Net_SmartIRC $irc, Net_SmartIRC_data $data)
    {
        $commands = [
            'help' => 'Display this usage',
        ];

        $this->ircClient->sendResponse($data->nick, 'This is the list of available commands:');
        foreach ($commands as $command => $description) {
            $this->ircClient->sendResponse($data->nick, "$command: $description");
        }
    }
}
