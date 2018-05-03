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

use Eventum\IrcBot\Entity\User;
use Eventum\RPC\XmlRpcException;
use Net_SmartIRC;
use Net_SmartIRC_data;

class AuthCommand extends BaseCommand
{
    /**
     * @param Net_SmartIRC $irc
     * @param Net_SmartIRC_data $data
     * @usage Format is "auth user@example.com password"
     */
    final public function auth(Net_SmartIRC $irc, Net_SmartIRC_data $data)
    {
        if (count($data->messageex) !== 3) {
            $this->sendResponse(
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
            $this->sendResponse($data->nick, 'Error: Temporary error');

            return;
        }

        // check if the given password is correct
        if (!$authenticated) {
            $this->sendResponse(
                $data->nick, 'Error: The email address / password combination could not be found in the system.'
            );

            return;
        }

        $user = new User($email, $password, $data->nick);
        $this->userDb->add($user);
        $this->sendResponse($data->nick, 'Thank you, you have been successfully authenticated.');
    }

    /**
     * @param Net_SmartIRC $irc
     * @param Net_SmartIRC_data $data
     */
    final public function listAuth(Net_SmartIRC $irc, Net_SmartIRC_data $data)
    {
        $this->sendResponse($data->nick, 'Showing authenticated users:');
        foreach ($this->userDb->all() as $user) {
            $this->sendResponse($data->nick, (string)$user);
        }
    }
}
