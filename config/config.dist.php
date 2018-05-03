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

use Eventum\IrcBot\Command;

// This is an example config file for the IRC bot.
// This file should be copied to config.php and customized for your needs.
// You can remove this comment :)

// The file should return array with configuration,
// You are free to use local variables if that makes config more readable for You.

// The following is the list of IRC channels that the bot should connect to,
// and the associated project name
//      Project Name -> IRC Channel(s),
//      Second Project' => array('#issues_2', '#byrocrate'),
// If you want to use IRC message categories (only applies if you have a custom workflow backend)
// the list of channels should be an associated array with the channel for the key and an array of categories
// for the value:
//      Project => array(
//          '#issues_2' =>  array(APP_EVENTUM_IRC_CATEGORY_DEFAULT, 'other')
//      )

return [
    /// connection parameters
    // IRC server address
    'hostname' => 'localhost',
    'port' => 6667,
    'nickname' => 'EventumBOT',
    'realname' => 'Eventum Issue Tracking System',
    // do you need a username/password to connect to this server?
    // if so, fill in the next two variables
    'username' => '',
    'password' => '',

    // Eventum XMLRPC access
    'xmlrpc.url' => 'http://eventum.127.0.0.1.xip.io:8012/rpc/xmlrpc.php',
    'xmlrpc.login' => 'admin@example.com',
    'xmlrpc.token' => 'admin',

    // configured IRC channels
    'channels' => [
        'Default Project' => '#issues',
    ],

    // default category for events without it
    'default_category' => 'default',

    // commands to register
    'commands' => [
        Command\AuthCommand::class => true,
        Command\ClockInCommand::class => true,
        Command\QuarantinedIssueCommand::class => true,
    ],

    /*
     * Bitwise debug level out of SMARTIRC_DEBUG_* constants
     *
     * @see Net_SmartIRC::setDebugLevel
     */
    // debug everything
//    'debugLevel' => SMARTIRC_DEBUG_ALL,

    // or bit flag of individual categories
//    'debugLevel' => SMARTIRC_DEBUG_NONE
//        | SMARTIRC_DEBUG_NOTICE
//        | SMARTIRC_DEBUG_CONNECTION
//        | SMARTIRC_DEBUG_SOCKET
//        | SMARTIRC_DEBUG_IRCMESSAGES
//        | SMARTIRC_DEBUG_MESSAGETYPES
//        | SMARTIRC_DEBUG_ACTIONHANDLER
//        | SMARTIRC_DEBUG_TIMEHANDLER
//        | SMARTIRC_DEBUG_MESSAGEHANDLER
//        | SMARTIRC_DEBUG_CHANNELSYNCING
//        | SMARTIRC_DEBUG_MODULES
//        | SMARTIRC_DEBUG_USERSYNCING
//        | SMARTIRC_DEBUG_MESSAGEPARSER
//        | SMARTIRC_DEBUG_DCC
//    ,

    // SmartIRC logger, defaults to stdout
//    'logging.smartirc' => 'irc_bot_smartirc.log',

    // PHP error logs, defaults to php.ini defaults
//    'logging.errorlog' => 'irc_bot_error.log',
];
