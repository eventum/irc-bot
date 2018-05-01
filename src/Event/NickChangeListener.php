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

use Net_SmartIRC;
use Net_SmartIRC_data;

class NickChangeListener implements EventListenerInterface
{
    /**
     * List of authenticated users
     *
     * @var array
     */
    private $auth = [];

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
        if (!array_key_exists($data->nick, $this->auth)) {
            return;
        }

        $old_nick = $data->nick;
        $new_nick = $data->message;

        $this->auth[$new_nick] = $this->auth[$old_nick];
        unset($this->auth[$old_nick]);
    }

    /**
     * Keep track of nicks for authenticated users
     *
     * @param Net_SmartIRC $irc
     * @param Net_SmartIRC_data $data
     */
    public function removeAuthenticatedUser(Net_SmartIRC $irc, Net_SmartIRC_data $data)
    {
        if (!array_key_exists($data->nick, $this->auth)) {
            return;
        }

        unset($this->auth[$data->nick]);
    }
}
