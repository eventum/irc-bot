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
    /** @var Config */
    private $config;
    /** @var string */
    private $default_category;
    /** @var array */
    private $channels = [];
    /** @var array */
    private $projects = [];

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
        $this->config = $config;
    }

    public function register(Net_SmartIRC $irc)
    {
        $this->channels = $this->config->getChannels();

        foreach (array_keys($this->channels) as $projectName) {
            $project = $this->rpcClient->getProjectByName($projectName);
            // skip inexistent projects
            if (!$project) {
                continue;
            }
            // skip inactive projects
            if ($project['prj_status'] !== 'active' || $project['prj_remote_invocation'] !== 'enabled') {
                continue;
            }
            $this->projects[$project['prj_id']] = $project;
        }

        if (!$this->projects) {
            // skip notify if no projects enabled
            return;
        }

        $irc->registerTimeHandler(3000, $this, 'notifyEvents');
    }

    public function notifyEvents()
    {
        foreach (array_keys($this->projects) as $prj_id) {
            $this->processMessages($prj_id);
        }
    }

    /**
     * @param int $prj_id
     */
    private function processMessages($prj_id)
    {
        foreach ($this->getPendingMessages($prj_id) as $row) {
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
                $this->markEventSent($prj_id, $row['ino_id']);
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
            $this->markEventSent($prj_id, $row['ino_id']);
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

    /**
     * @param int $prj_id
     * @return array
     */
    private function getPendingMessages($prj_id)
    {
        try {
            return $this->rpcClient->getPendingMessages($prj_id, 50);
        } catch (XmlRpcException $e) {
            return [];
        }
    }

    /**
     * Mark message as sent
     * @param int $prj_id
     * @param int $ino_id
     */
    private function markEventSent($prj_id, $ino_id)
    {
        try {
            $this->rpcClient->markEventSent($prj_id, (int)$ino_id);
        } catch (XmlRpcException $e) {
        }
    }
}
