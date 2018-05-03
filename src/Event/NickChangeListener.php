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

namespace Eventum\IrcBot\Event;

use Eventum\IrcBot\UserDb;
use Net_SmartIRC;
use Net_SmartIRC_data;

class NickChangeListener implements EventListenerInterface
{
    /** @var UserDb */
    private $userDb;

    public function __construct(UserDb $userdb)
    {
        $this->userDb = $userdb;
    }

    public function register(Net_SmartIRC $irc)
    {
        // methods that keep track of who is authenticated
        $irc->registerActionHandler(SMARTIRC_TYPE_NICKCHANGE, '.*', $this, 'updateAuthenticatedUser');
        $irc->registerActionHandler(
            SMARTIRC_TYPE_KICK | SMARTIRC_TYPE_QUIT | SMARTIRC_TYPE_PART, '.*', $this, 'removeAuthenticatedUser'
        );
    }

    /**
     * Keep track of nicks for authenticated users
     *
     * @param Net_SmartIRC $irc
     * @param Net_SmartIRC_data $data
     */
    public function updateAuthenticatedUser(Net_SmartIRC $irc, Net_SmartIRC_data $data)
    {
        $user = $this->userDb->findByNick($data->nick);
        if (!$user) {
            return;
        }

        $user->nick = $data->message;
    }

    /**
     * Keep track of nicks for authenticated users
     *
     * @param Net_SmartIRC $irc
     * @param Net_SmartIRC_data $data
     */
    public function removeAuthenticatedUser(Net_SmartIRC $irc, Net_SmartIRC_data $data)
    {
        $user = $this->userDb->findByNick($data->nick);
        if (!$user) {
            return;
        }

        $this->userDb->remove($user);
    }
}
