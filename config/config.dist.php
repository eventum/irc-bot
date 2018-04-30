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

    // configured IRC channels
    'channels' => [
	    'Default Project' => '#issues',
    ],

    'default_category' => 'default',
];
