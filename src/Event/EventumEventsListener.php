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

namespace Eventum\IrcBot\Event;

use Eventum\IrcBot\Config;
use Eventum\IrcBot\IrcClient;
use Eventum\IrcBot\UserDb;
use Eventum\RPC\EventumXmlRpcClient;
use Eventum\RPC\XmlRpcException;
use Net_SmartIRC;

class EventumEventsListener implements EventListenerInterface
{
    /** @var IrcClient */
    protected $ircClient;
    /** @var UserDb */
    protected $userDb;
    /** @var EventumXmlRpcClient */
    protected $rpcClient;
    /** @var string */
    private $default_category;
    /** @var array */
    private $channels;

    public function __construct(
        IrcClient $ircClient,
        UserDb $userDb,
        EventumXmlRpcClient $rpcClient,
        Config $config
    ) {
        $this->ircClient = $ircClient;
        $this->userDb = $userDb;
        $this->rpcClient = $rpcClient;
        $this->default_category = $config['default_category'];
        $this->channels = $config->getChannels();
    }

    public function register(Net_SmartIRC $irc)
    {
        $irc->registerTimeHandler(3000, $this, 'notifyEvents');
    }

    public function notifyEvents()
    {
        foreach ($this->getPendingMessages() as $row) {
            $row['ino_message'] = base64_decode($row['ino_message_base64']);

            if (!$row['ino_category']) {
                $row['ino_category'] = $this->default_category;
            }

            // check if this is a targeted message
            if ($row['ino_target_usr_id']) {
                $nick = $this->userDb->findByEmail($row['usr_email']);
                if ($nick) {
                    $this->ircClient->sendResponse($nick, $row['ino_message']);
                }
                // FIXME: why mark it sent if user is not online?
                $this->markEventSent($row['ino_id']);
                continue;
            }

            $channels = $this->getChannels($row['project_name']);
            if (!$channels) {
                continue;
            }

            /** @var Config\Channel $channel */
            foreach ($channels as $channel) {
                $message = $row['ino_message'];
                if (isset($row['issue_url'])) {
                    $message .= ' - ' . $row['issue_url'];
                } elseif (isset($row['emails_url'])) {
                    $message .= ' - ' . $row['emails_url'];
                }

                if (count($this->getProjectsForChannel($channel->name)) > 1) {
                    // if multiple projects display in the same channel, display project in message
                    $message = '[' . $row['project_name'] . '] ' . $message;
                }

                if ($channel->hasCategory($row['ino_category'])) {
                    $this->ircClient->sendResponse($channel->name, $message);
                }
            }
            $this->markEventSent($row['ino_id']);
        }
    }

    /**
     * Get IRC Channels for project name
     * @param string $projectName
     * @return array
     */
    private function getChannels($projectName)
    {
        if (isset($this->channels[$projectName])) {
            return $this->channels[$projectName];
        }

        return [];
    }

    /**
     * Helper method to the project names a channel displays messages for.
     *
     * @param   string $channelName The name of the channel
     * @return  array The projects displayed in the channel
     */
    private function getProjectsForChannel($channelName)
    {
        $projectNames = [];
        foreach ($this->channels as $projectName => $channels) {
            /** @var Config\Channel $channel */
            foreach ($channels as $channel) {
                if ($channel->name === $channelName) {
                    $projectNames[] = $projectName;
                }
            }
        }

        return $projectNames;
    }

    private function getPendingMessages()
    {
        try {
            return $this->rpcClient->getPendingMessages(50);
        } catch (XmlRpcException $e) {
            return [];
        }
    }

    /**
     * Mark message as sent
     * @param int $ino_id
     */
    private function markEventSent($ino_id)
    {
        try {
            $this->rpcClient->markEventSent((int)$ino_id);
        } catch (XmlRpcException $e) {
        }
    }
}
