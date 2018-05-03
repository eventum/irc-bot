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
use Eventum\IrcBot\ParseDocCommentTrait;
use Eventum\IrcBot\SendResponseTrait;
use Net_SmartIRC;
use Net_SmartIRC_data;
use ReflectionClass;
use ReflectionMethod;

class CommandSet implements EventListenerInterface
{
    use SendResponseTrait;
    use ParseDocCommentTrait;

    /** @var array */
    private $commands;

    /** @var string[] */
    private $usage = [];

    public function __construct(Net_SmartIRC $irc, array $commands)
    {
        $this->irc = $irc;
        $this->commands = $commands;
        // always add ourselves for help command
        $this->commands[] = $this;
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

                $usage = $this->getUsageDoc($method);
                if ($usage) {
                    $this->usage[] = "$commandName: $usage";
                }
            }
            // unreference, as registerActionHandler uses &$command
            unset($command);

            // keep order sorted
            asort($this->usage);
        }
    }

    /**
     * @param Net_SmartIRC $irc
     * @param Net_SmartIRC_data $data
     * @usage Display this usage
     */
    final public function help(Net_SmartIRC $irc, Net_SmartIRC_data $data)
    {
        if (!$this->usage) {
            $this->sendResponse($data->nick, 'There are no commands enabled to provide usage');

            return;
        }

        $this->sendResponse($data->nick, 'This is the list of available commands:');
        $this->sendResponse($data->nick, $this->usage);
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

    private function getUsageDoc(ReflectionMethod $method)
    {
        $doc = $this->parseBlockComment($method->getDocComment());
        if (!isset($doc['usage'])) {
            return null;
        }

        return implode(' ', $doc['usage'][0]);
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
