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

            // configured IRC channels
            'channels' => [
                'Default Project' => '#issues',
            ],

            'default_category' => 'default',
        ]);

        $resolver->setAllowedTypes('channels', 'array');
    }

    private function loadConfig($configPath)
    {
        return require $configPath;
    }
}
