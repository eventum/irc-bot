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

namespace Eventum\IrcBot\Entity;

use ArrayAccess;
use Eventum\IrcBot\Traits;
use IteratorAggregate;
use Net_SmartIRC;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Project implements ArrayAccess, IteratorAggregate
{
    use Traits\OptionsArrayAccessTrait;

    const KEYS = [
        'prj_id' => null,
        'prj_created_date' => null,
        'prj_remote_invocation' => null,
        'prj_segregate_reporter' => null,
        'prj_status' => null,
        'prj_title' => null,
    ];

    /** @var Channel[] */
    private $channels = [];

    public function __construct(array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($this->filterKeys($options));
    }

    public function enabled()
    {
        return $this['prj_status'] === 'active' && $this['prj_remote_invocation'] === 'enabled';
    }

    public function getId()
    {
        return (int)$this['prj_id'];
    }

    public function getTitle()
    {
        return $this['prj_title'];
    }

    public function setChannels($channels)
    {
        $this->channels = $channels;

        return $this;
    }

    public function getChannels()
    {
        return $this->channels;
    }

    public function getChannelNames()
    {
        $result = [];
        foreach ($this->channels as $channel) {
            $result[] = $channel->name;
        }

        return $result;
    }

    /**
     * @param Net_SmartIRC $irc
     * @return bool
     */
    public function anyChannelJoined(Net_SmartIRC $irc)
    {
        foreach ($this->channels as $channel) {
            // the temp object and & is here to avoid php notices
            $obj = &$irc->getChannel($channel->name);
            if ($obj) {
                return true;
            }
        }

        return false;
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(self::KEYS);
    }

    private function filterKeys($options)
    {
        return array_intersect_key($options, array_flip(array_keys(self::KEYS)));
    }
}
