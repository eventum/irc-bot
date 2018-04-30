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

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['config.path'] = dirname(__DIR__) . '/config/config.php';

        $app[Config::class] = function ($app) {
            return new Config($app['config.path']);
        };

        $app[IrcClient::class] = function ($app) {
            return new IrcClient($app[Config::class]);
        };

        $app[IrcBot::class] = function ($app) {
            return new IrcBot($app[Config::class], $app[IrcClient::class]);
        };
    }
}
