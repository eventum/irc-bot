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

use ArrayIterator;

trait OptionsArrayAccessTrait
{
    /** @var array */
    private $options;

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->options);
    }

    public function offsetGet($offset)
    {
        return $this->options[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->options[] = $value;
        } else {
            $this->options[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->options[$offset]);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->options);
    }
}
