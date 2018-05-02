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

use Pimple\Container;

class Application extends Container
{
    const VERSION = '1.0.0';

    public function __construct(array $values = [])
    {
        parent::__construct($values);
        $this->register(new ServiceProvider());
    }

    public function run()
    {
        /** @var IrcBot $bot */
        $bot = $this[IrcBot::class];
        $bot->run();
    }
}
