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

namespace Eventum\IrcBot\Command;

use Eventum\IrcBot\Event\EventListenerInterface;
use Net_SmartIRC;
use ReflectionClass;
use ReflectionMethod;

class CommandSet implements EventListenerInterface
{
    /** @var array */
    private $commands;

    public function __construct($commands)
    {
        $this->commands = $commands;
    }

    public function register(Net_SmartIRC $irc)
    {
        // register all commands
        foreach ($this->commands as $command) {
            $methods = $this->getMethods($command);
            foreach ($methods as $methodName => $method) {
                $commandName = $this->getCommandName($methodName);
                $regex = "^!?{$commandName}\b";
                $irc->registerActionHandler(SMARTIRC_TYPE_QUERY, $regex, $command, $methodName);
            }
        }
    }

    /**
     * Get public final methods that will be used as bot commands
     *
     * @return ReflectionMethod[]
     */
    private function getMethods($class)
    {
        $methods = [];
        $reflectionClass = new ReflectionClass($class);
        foreach ($reflectionClass->getMethods() as $method) {
            if (
                $method->isPublic() // only public
                && !$method->isStatic() // no static
                && strpos($method->getName(), '__') !== 0 // no magic
                && $method->isFinal() // must be final
            ) {
                $methods[$method->getName()] = $method;
            }
        }

        return $methods;
    }

    /**
     * Transform camel case name to dash version
     *
     * @param  string $name
     * @return string
     */
    private function getCommandName($name)
    {
        return strtolower(preg_replace('/([^A-Z-])([A-Z])/', '$1-$2', $name));
    }
}
