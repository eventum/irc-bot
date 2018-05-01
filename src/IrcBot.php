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

class IrcBot
{
    /** @var Config */
    private $config;
    /** @var IrcClient */
    private $ircClient;

    public function __construct(Config $config, IrcClient $ircClient)
    {
        $this->config = $config;
        $this->ircClient = $ircClient;
    }

    public function run()
    {
        $this->ircClient->register(new Event\NickChangeListener());
        $this->ircClient->connect();
        $this->ircClient->login();
        $this->ircClient->joinChannels();
        $this->ircClient->listen();
    }
}
