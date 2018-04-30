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

use Eventum\IrcBot\IrcBot;
use Net_SmartIRC;
use ReflectionClass;
use ReflectionMethod;

/**
 * Class containing helper methods for BotCommands class
 */
class AbstractBotCommands
{
    /** @var Net_SmartIRC */
    protected $irc;

    public function __construct(Net_SmartIRC $irc)
    {
        $this->irc = $irc;
    }

    /**
     * Method used to send a message to the given target.
     *
     * @param string $target The target for this message
     * @param string|string[] $response The message to send
     * @param int $priority the priority level of the message
     */
    protected function sendResponse($target, $response, $priority = SMARTIRC_MEDIUM)
    {
        if (strpos($target, '#') !== 0) {
            $type = SMARTIRC_TYPE_QUERY;
        } else {
            $type = SMARTIRC_TYPE_CHANNEL;
        }
        $this->irc->message($type, $target, $response, $priority);
    }

    /**
     * Register commands.
     * All public final methods are registered. the method name is the prefix of the command
     *
     * @param Net_SmartIRC $irc
     */
    public function register(Net_SmartIRC $irc)
    {
        // register all commands
        $methods = $this->getMethods();
        foreach ($methods as $methodName => $method) {
            $commandName = $this->getCommandName($methodName);
            $regex = "^!?{$commandName}\b";
            $irc->registerActionHandler(SMARTIRC_TYPE_QUERY, $regex, $this, $methodName);
        }
    }

    /**
     * Get public final methods that will be used as bot commands
     *
     * @return ReflectionMethod[]
     */
    private function getMethods()
    {
        $methods = [];
        $reflectionClass = new ReflectionClass($this);
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
