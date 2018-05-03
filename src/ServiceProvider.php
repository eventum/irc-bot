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

use Eventum\RPC\EventumXmlRpcClient;
use Net_SmartIRC;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['config.path'] = dirname(__DIR__) . '/config/config.php';

        $app[Net_SmartIRC::class] = function () {
            // NB: must require this in global context
            // otherise $SMARTIRC_nreplycodes from defines.php is not initialized
            global $SMARTIRC_nreplycodes;
            require_once 'Net/SmartIRC/defines.php';

            return new Net_SmartIRC();
        };

        $app[Config::class] = function ($app) {
            // preload smartirc class for constants
            $app[Net_SmartIRC::class];

            return new Config($app['config.path']);
        };

        $app[EventumXmlRpcClient::class] = function ($app) {
            $config = $app[Config::class];

            $client = new EventumXmlRpcClient($config['xmlrpc.url']);
            $client->setCredentials($config['xmlrpc.login'], $config['xmlrpc.token']);
            $client->addUserAgent('EventumIRC/' . Application::VERSION);

            return $client;
        };

        $app[UserDb::class] = function () {
            return new UserDb();
        };

        $app[Command\AuthCommand::class] = function ($app) {
            return new Command\AuthCommand(
                $app[Net_SmartIRC::class],
                $app[UserDb::class],
                $app[EventumXmlRpcClient::class]
            );
        };

        $app[Command\ClockInCommand::class] = function ($app) {
            return new Command\ClockInCommand(
                $app[Net_SmartIRC::class],
                $app[UserDb::class],
                $app[EventumXmlRpcClient::class]
            );
        };

        $app[Command\QuarantinedIssueCommand::class] = function ($app) {
            return new Command\QuarantinedIssueCommand(
                $app[Net_SmartIRC::class],
                $app[UserDb::class],
                $app[EventumXmlRpcClient::class]
            );
        };
        $app[IrcBot::class] = function ($app) {
            $commands = [];
            foreach ($app[Config::class]['commands'] as $commandClass => $enabled) {
                if ($enabled) {
                    $commands[] = $app[$commandClass];
                }
            }

            $listeners = [
                new Command\CommandSet($app[Net_SmartIRC::class], $commands),
                new Event\NickChangeListener($app[UserDb::class]),
                new Event\EventumEventsListener(
                    $app[Net_SmartIRC::class],
                    $app[UserDb::class],
                    $app[EventumXmlRpcClient::class],
                    $app[Config::class]
                ),
            ];

            return new IrcBot($app[Config::class], $app[Net_SmartIRC::class], $listeners);
        };
    }
}
