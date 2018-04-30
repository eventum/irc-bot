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
use Eventum\IrcBot\IrcClient;

ini_set('memory_limit', '1024M');

require __DIR__ . '/../vendor/autoload.php';

// NB: must require this in global context
// otherise $SMARTIRC_nreplycodes from defines.php is not initialized
require_once 'Net/SmartIRC/defines.php';

$config = new Config(dirname(__DIR__) . '/config/config.php');
$bot = new IrcBot(
    $config,
    new IrcClient($config)
);
$bot->run();
