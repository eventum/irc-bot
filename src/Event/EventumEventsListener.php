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

use Eventum\IrcBot\Command\BaseCommand;
use Eventum\IrcBot\Config;
use Eventum\IrcBot\Entity\Project;
use Eventum\IrcBot\UserDb;
use Eventum\RPC\EventumXmlRpcClient;
use Eventum\RPC\XmlRpcException;
use Net_SmartIRC;

class EventumEventsListener extends BaseCommand implements EventListenerInterface
{
    /** @var Config */
    private $config;
    /** @var string */
    private $default_category;
    /** @var array */
    private $channels = [];
    /** @var Project[] */
    private $projects = [];

    public function __construct(
        Net_SmartIRC $irc,
        UserDb $userDb,
        EventumXmlRpcClient $rpcClient,
        Config $config
    ) {
        parent::__construct($irc, $userDb, $rpcClient);
        $this->default_category = $config['default_category'];
        $this->config = $config;
    }

    public function register(Net_SmartIRC $irc)
    {
        $this->channels = $this->config->getChannels();
        $this->projects = $this->getProjects();

        if (!$this->projects) {
            // skip notify if no projects enabled
            return;
        }

        $irc->registerTimeHandler(3000, $this, 'notifyEvents');
    }

    private function getProjects()
    {
        $projects = [];

        foreach ($this->channels as $projectName => $channels) {
            $result = $this->rpcClient->getProjectByName($projectName);
            if (!$result) {
                continue;
            }
            $project = new Project($result);
            if (!$project->enabled()) {
                continue;
            }
            $project->setChannels($channels);
            $projects[$project['prj_id']] = $project;
        }

        return $projects;
    }

    public function notifyEvents(Net_SmartIRC $irc)
    {
        foreach ($this->projects as $prj_id => $project) {
            if (!$project->anyChannelJoined($irc)) {
                continue;
            }
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
                    $this->sendResponse($nick, $row['ino_message']);
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
                    $this->sendResponse($channel->name, $message);
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
