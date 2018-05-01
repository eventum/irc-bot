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
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['config.path'] = dirname(__DIR__) . '/config/config.php';

        $app[Net_SmartIRC::class] = function ($app) {
            // NB: must require this in global context
            // otherise $SMARTIRC_nreplycodes from defines.php is not initialized
            global $SMARTIRC_nreplycodes;
            require_once 'Net/SmartIRC/defines.php';

            return new Net_SmartIRC();
        };

        $app[Config::class] = function ($app) {
            return new Config($app['config.path']);
        };

        $app[IrcClient::class] = function ($app) {
            return new IrcClient($app[Net_SmartIRC::class], $app[Config::class]);
        };

        $app[IrcBot::class] = function ($app) {
            $commands = [
                new Command\HelpCommand($app[IrcClient::class]),
            ];
            $listeners = [
                new Event\NickChangeListener(),
                new Command\CommandSet($commands),
            ];

            return new IrcBot($app[Config::class], $app[IrcClient::class], $listeners);
        };
    }
}
