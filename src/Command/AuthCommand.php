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
use Net_SmartIRC;
use Net_SmartIRC_data;

class AuthCommand extends BaseCommand
{
    /** @var UserDb */
    private $userDb;

    public function __construct(IrcClient $ircClient, UserDb $userDb)
    {
        parent::__construct($ircClient);
        $this->userDb = $userDb;
    }

    /**
     * @param Net_SmartIRC $irc
     * @param Net_SmartIRC_data $data
     */
    final public function listAuth(Net_SmartIRC $irc, Net_SmartIRC_data $data)
    {
        $this->ircClient->sendResponse($data->nick, 'Showing authenticated users:');
        foreach ($this->userDb->all() as $nickname => $email) {
            $this->ircClient->sendResponse($data->nick, "$nickname => $email");
        }
    }
}
