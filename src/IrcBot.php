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

use Eventum\IrcBot\Entity\Channel;
use Eventum\IrcBot\Event\EventListenerInterface;
use Net_SmartIRC;

class IrcBot
{
    /** @var Config */
    private $config;
    /** @var Net_SmartIRC */
    private $irc;
    /** @var EventListenerInterface[] */
    private $listeners = [];

    public function __construct(Config $config, Net_SmartIRC $irc, array $listeners)
    {
        $this->config = $config;
        $this->irc = $irc;
        $this->listeners = $listeners;
    }

    public function run()
    {
        $this->configure($this->irc, $this->config);
        foreach ($this->listeners as $listener) {
            $listener->register($this->irc);
        }
        $this->login($this->irc, $this->config);
        $this->join($this->irc, $this->config);
        $this->irc->listen();
    }

    private function configure(Net_SmartIRC $irc, Config $config)
    {
        $irc->setAutoRetryMax(0);
        $irc->setAutoRetry(true);
        $irc->setReconnectDelay(10000);

        $irc->setReceiveTimeout(600);
        $irc->setTransmitTimeout(600);

        // enable user and channel syncing,
        // users are accessible via $irc->user array, i.e $irc->user['meebey']->host;
        $irc->setChannelSyncing(true);
        $irc->setUserSyncing(true);

        if ($config['debugLevel']) {
            $irc->setDebugLevel($config['debugLevel']);
        }
    }

    private function login(Net_SmartIRC $irc, Config $config)
    {
        $this->irc->connect($config['hostname'], $config['port']);

        if (!$config['username']) {
            $irc->login($config['nickname'], $config['realname']);
        } elseif (!$config['password']) {
            $irc->login($config['nickname'], $config['realname'], 0, $config['username']);
        } else {
            $irc->login($config['nickname'], $config['realname'], 0, $config['username'], $config['password']);
        }
    }

    /**
     * Join configured channels.
     */
    private function join(Net_SmartIRC $irc, Config $config)
    {
        foreach ($config->getChannels() as $projectName => $channels) {
            foreach ($channels as $channel) {
                /** @var Channel $channel */
                if ($channel->key) {
                    $irc->join($channel->name, $channel->key);
                } else {
                    $irc->join($channel->name);
                }
            }
        }
    }
}
