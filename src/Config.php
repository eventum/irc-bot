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
use Eventum\IrcBot\Entity\Channel;
use InvalidArgumentException;
use IteratorAggregate;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Config implements ArrayAccess, IteratorAggregate
{
    use OptionsArrayAccessTrait;

    /** @var Channel[] */
    private $channels;

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
        if ($this->channels === null) {
            $this->channels = $this->resolveChannels();
        }

        return $this->channels;
    }

    private function resolveChannels()
    {
        $channels = [];
        // map project_id => channel objects
        foreach ($this['channels'] as $project_name => $options) {
            $channels[$project_name] = $this->createChannels($options, $this['default_category']);
        }

        return $channels;
    }

    /**
     * @param string|array $definition
     * @param string $default_category
     * @return self[]
     */
    private function createChannels($definition, $default_category)
    {
        // we need to map old configs with just channels to new config with categories as well
        if (!is_array($definition)) {
            // old config, one channel
            $options = [
                $definition => [$default_category],
            ];
        } elseif (isset($definition[0]) and !is_array($definition[0])) {
            // old config with multiple channels
            $options = [];
            $channels = $definition;
            foreach ($channels as $name) {
                $options[$name] = [$default_category];
            }
        } else {
            // new format
            $options = $definition;
        }

        $res = [];
        foreach ($options as $name => $categories) {
            $res[] = new Channel($name, $categories);
        }

        return $res;
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
            'default_category' => 'default',

            'debugLevel' => 0,

            // commands to register
            'commands' => [
                Command\AuthCommand::class => true,
                Command\ClockInCommand::class => true,
                Command\QuarantinedIssueCommand::class => true,
            ],

            // SmartIRC logger
            'logging.smartirc' => null,
            // PHP error logs
            'logging.errorlog' => null,

            // in milliseconds, how often to pull events from xmlrpc
            'events.poll_interval' => 3000,
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
