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

namespace Eventum\IrcBot\Config;

class Channel
{
    /** @var string */
    public $name;
    /** @var string */
    public $key;
    /** @var string[] */
    public $categories;

    public function __construct($channelName, array $categories)
    {
        $parts = explode(' ', $channelName, 2);
        if (count($parts) > 1) {
            $this->name = $parts[0];
            $this->key = $parts[1];
        } else {
            $this->name = $channelName;
        }
        $this->categories = $categories;
    }

    /**
     * Returns true if this channel is tagged with category
     * @param string $category
     * @return bool
     */
    public function hasCategory($category)
    {
        return in_array($category, $this->categories, true);
    }

    /**
     * @param string|array $channel
     * @param string $default_category
     * @return self[]
     */
    public static function createChannels($channel, $default_category = '')
    {
        // we need to map old configs with just channels to new config with categories as well
        if (!is_array($channel)) {
            // old config, one channel
            $options = [
                $channel => [$default_category],
            ];
        } elseif (isset($channel[0]) and !is_array($channel[0])) {
            // old config with multiple channels
            $options = [];
            $channels = $channel;
            foreach ($channels as $name) {
                $options[$name] = [$default_category];
            }
        } else {
            // new format
            $options = $channel;
        }

        $res = [];
        foreach ($options as $name => $categories) {
            $res[] = new self($name, $categories);
        }

        return $res;
    }
}
