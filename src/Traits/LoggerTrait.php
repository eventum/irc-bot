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

namespace Eventum\IrcBot\Traits;

use Eventum\IrcBot\Logger;
use Psr\Log\LoggerTrait as AbstractLoggerTrait;

trait LoggerTrait
{
    use AbstractLoggerTrait;

    /** @var Logger */
    protected $logger;

    public function log($level, $message, array $context = [])
    {
        $this->logger->log($level, $message, $context);
    }
}
