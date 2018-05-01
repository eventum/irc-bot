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

        $app[EventumXmlRpcClient::class] = function ($app) {
            $config = $app[Config::class];

            $client = new EventumXmlRpcClient($config['xmlrpc.url']);
            $client->setCredentials($config['xmlrpc.login'], $config['xmlrpc.token']);

            return $client;
        };

        $app[IrcClient::class] = function ($app) {
            return new IrcClient($app[Net_SmartIRC::class], $app[Config::class]);
        };

        $app[UserDb::class] = function () {
            return new UserDb();
        };

        $app[IrcBot::class] = function ($app) {
            $commands = [
                new Command\HelpCommand($app[IrcClient::class]),
                new Command\AuthCommand(
                    $app[IrcClient::class],
                    $app[UserDb::class],
                    $app[EventumXmlRpcClient::class]
                ),
                new Command\ClockInCommand(
                    $app[IrcClient::class],
                    $app[UserDb::class],
                    $app[EventumXmlRpcClient::class]
                ),
                new Command\QuarantinedIssueCommand(
                    $app[IrcClient::class],
                    $app[UserDb::class],
                    $app[EventumXmlRpcClient::class]
                ),
            ];
            $listeners = [
                new Command\CommandSet($commands),
                new Event\NickChangeListener($app[UserDb::class]),
                new Event\EventumEventsListener(
                    $app[IrcClient::class],
                    $app[UserDb::class],
                    $app[EventumXmlRpcClient::class],
                    $app[Config::class]
                ),
            ];

            return new IrcBot($app[Config::class], $app[IrcClient::class], $listeners);
        };
    }
}
