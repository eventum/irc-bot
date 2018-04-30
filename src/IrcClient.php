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

use Net_SmartIRC;

class IrcClient
{
    /** @var Config */
    private $config;

    /** @var Net_SmartIRC */
    private $irc;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->irc = $this->configure(new Net_SmartIRC());
    }

    private function configure(Net_SmartIRC $irc)
    {
        // reconnect is poorly designed, do not use it
        // @see https://pear.php.net/bugs/bug.php?id=20974
        //$irc->setAutoRetry(true);
        $irc->setAutoRetry(false);
        $irc->setAutoRetryMax(PHP_INT_MAX);
        $irc->setReconnectDelay(10000);

        $irc->setReceiveTimeout(600);
        $irc->setTransmitTimeout(600);

        // enable user and channel syncing,
        // users are accessible via $irc->user array, i.e $irc->user['meebey']->host;
        $irc->setChannelSyncing(true);
        $irc->setUserSyncing(true);

        return $irc;
    }

    public function connect()
    {
        $config = $this->config;

        $this->irc->connect($config['hostname'], $config['port']);
    }

    public function login()
    {
        $config = $this->config;

        if (!$config['username']) {
            $this->irc->login($config['nickname'], $config['realname']);
        } elseif (!$config['password']) {
            $this->irc->login($config['nickname'], $config['realname'], 0, $config['username']);
        } else {
            $this->irc->login($config['nickname'], $config['realname'], 0, $config['username'], $config['password']);
        }
    }

    /**
     * Join configured channels.
     */
    public function joinChannels()
    {
        foreach ($this->config['channels'] as $projectName => $channelName) {
            $this->irc->join($channelName);
        }
    }

    public function listen()
    {
        $this->irc->listen();
    }
}