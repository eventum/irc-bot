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

use Net_SmartIRC;
use Net_SmartIRC_data;

/**
 * Class containing IRC Bot command handlers.
 *
 * All public final methods are taken as commands.
 */
class BotCommands extends AbstractBotCommands
{
    /**
     * @param Net_SmartIRC $irc
     * @param Net_SmartIRC_data $data
     */
    final public function help(Net_SmartIRC $irc, Net_SmartIRC_data $data)
    {
        $commands = [
            'help' => 'Display this usage',
        ];

        $this->sendResponse($data->nick, 'This is the list of available commands:');
        foreach ($commands as $command => $description) {
            $this->sendResponse($data->nick, "$command: $description");
        }
    }
}
