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

abstract class BaseCommand
{
    /** @var IrcClient */
    protected $ircClient;

    public function __construct(IrcClient $ircClient)
    {
        $this->ircClient = $ircClient;
    }
}
