#!/usr/bin/php
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

use Eventum\IrcBot\Config;
use Eventum\IrcBot\IrcBot;

ini_set('memory_limit', '1024M');

require __DIR__ . '/../vendor/autoload.php';

$bot = new IrcBot(new Config(dirname(__DIR__) . '/config/config.php'));
$bot->run();
