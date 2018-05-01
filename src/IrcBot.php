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
    /** @var IrcClient */
    private $ircClient;
    /** @var ProcessControl */
    private $processControl;

    public function __construct(IrcClient $ircClient, ProcessControl $processControl, array $listeners)
    {
        $this->ircClient = $ircClient;
        $this->processControl = $processControl;
        foreach ($listeners as $listener) {
            $this->ircClient->register($listener);
        }
    }

    public function run()
    {
        $this->ircClient->connect();
        $this->ircClient->login();
        $this->ircClient->joinChannels();

        // loop forever, reconnect and retry
        // @see https://pear.php.net/bugs/bug.php?id=20974
        while (!$this->processControl->shutdown) {
            $this->ircClient->listen();
            $this->ircClient->reconnect();
        }

        $this->ircClient->disconnect();
    }
}
