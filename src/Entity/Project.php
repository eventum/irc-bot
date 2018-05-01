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
use Eventum\IrcBot\Config\Channel;
use Eventum\IrcBot\OptionsArrayAccessTrait;
use IteratorAggregate;
use Net_SmartIRC;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Project implements ArrayAccess, IteratorAggregate
{
    use OptionsArrayAccessTrait;

    /** @var Channel[] */
    private $channels = [];

    public function __construct(array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    public function enabled()
    {
        return $this['prj_status'] === 'active' && $this['prj_remote_invocation'] === 'enabled';
    }

    public function setChannels($channels)
    {
        $this->channels = $channels;
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
        $resolver->setDefaults([
            'prj_id' => null,
            'assigned_statuses' => null,
            'prj_anonymous_post' => null,
            'prj_anonymous_post_options' => null,
            'prj_assigned_users' => null,
            'prj_created_date' => null,
            'prj_customer_backend' => null,
            'prj_initial_sta_id' => null,
            'prj_lead_usr_id' => null,
            'prj_mail_aliases' => null,
            'prj_outgoing_sender_email' => null,
            'prj_outgoing_sender_name' => null,
            'prj_remote_invocation' => null,
            'prj_segregate_reporter' => null,
            'prj_sender_flag' => null,
            'prj_sender_flag_location' => null,
            'prj_status' => null,
            'prj_title' => null,
            'prj_workflow_backend' => null,
        ]);
    }
}
