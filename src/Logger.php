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
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class Logger extends AbstractLogger
{
    /** @var Net_SmartIRC */
    private $irc;

    // use unused SMARTIRC_DEBUG_MODULES for loglevels higher than NOTICE
    private $logLevels = [
        LogLevel::EMERGENCY => SMARTIRC_DEBUG_MODULES,
        LogLevel::ALERT => SMARTIRC_DEBUG_MODULES,
        LogLevel::CRITICAL => SMARTIRC_DEBUG_MODULES,
        LogLevel::ERROR => SMARTIRC_DEBUG_MODULES,
        LogLevel::WARNING => SMARTIRC_DEBUG_MODULES,
        LogLevel::NOTICE => SMARTIRC_DEBUG_NOTICE,
        LogLevel::INFO => SMARTIRC_DEBUG_NOTICE,
        LogLevel::DEBUG => SMARTIRC_DEBUG_NOTICE,
    ];

    public function __construct(Net_SmartIRC $irc)
    {
        $this->irc = $irc;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);
        $frame = $trace[2];

        if ($context) {
            $message .= '. [';
            foreach ($context as $item => $value) {
                $message .= sprintf("'$item' => $value");
            }
            $message .= ']';
        }

        $this->irc->log($this->logLevels[$level], $message, $frame['file'], $frame['line']);
    }
}
