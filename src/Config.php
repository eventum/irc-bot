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

use ArrayAccess;
use Eventum\IrcBot\Config\Channel;
use InvalidArgumentException;
use IteratorAggregate;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Config implements ArrayAccess, IteratorAggregate
{
    use OptionsArrayAccessTrait;

    public function __construct($configPath)
    {
        $options = $this->loadConfig($configPath);

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    /**
     * @return array
     */
    public function getChannels()
    {
        $channels = [];
        // map project_id => channel(s)
        foreach ($this['channels'] as $project_name => $options) {
            $channels[$project_name] = Channel::createChannels($options, $this['default_category']);
        }

        return $channels;
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            /// connection parameters
            // IRC server address
            'hostname' => 'localhost',
            'port' => 6667,
            'nickname' => 'EventumBOT',
            'realname' => 'Eventum Issue Tracking System',
            // do you need a username/password to connect to this server?
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

            'debugLevel' => 0,

            'default_category' => 'default',
        ]);

        $resolver->setAllowedTypes('channels', 'array');
    }

    private function loadConfig($configPath)
    {
        if (!is_readable($configPath)) {
            throw new InvalidArgumentException("Config file {$configPath} is not readable");
        }

        return require $configPath;
    }
}
