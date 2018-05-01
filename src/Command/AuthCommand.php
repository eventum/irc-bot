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
use Eventum\RPC\XmlRpcException;
use Net_SmartIRC;
use Net_SmartIRC_data;

class AuthCommand extends BaseCommand
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
     * Format is "auth user@example.com password"
     *
     * @param Net_SmartIRC $irc
     * @param Net_SmartIRC_data $data
     */
    final public function auth(Net_SmartIRC $irc, Net_SmartIRC_data $data)
    {
        if (count($data->messageex) !== 3) {
            $this->ircClient->sendResponse(
                $data->nick,
                'Error: wrong parameter count for "AUTH" command. Format is "!auth user@example.com password".'
            );

            return;
        }

        $email = $data->messageex[1];
        $password = $data->messageex[2];

        try {
            $authenticated = $this->rpcClient->isValidLogin($email, $password);
        } catch (XmlRpcException $e) {
            $this->ircClient->sendResponse($data->nick, 'Error: Temporary error');

            return;
        }

        // check if the given password is correct
        if (!$authenticated) {
            $this->ircClient->sendResponse(
                $data->nick, 'Error: The email address / password combination could not be found in the system.'
            );

            return;
        }

        $this->userDb->add($data->nick, $email);
        $this->ircClient->sendResponse($data->nick, 'Thank you, you have been successfully authenticated.');
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
