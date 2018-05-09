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
use Eventum\IrcBot\Entity\Channel;
use Eventum\IrcBot\Entity\Project;
use Eventum\IrcBot\Traits\LoggerTrait;
use Eventum\IrcBot\UserDb;
use Eventum\RPC\EventumXmlRpcClient;
use Eventum\RPC\XmlRpcException;
use Net_SmartIRC;
use Psr\Log\LoggerInterface;

class EventumEventsListener extends BaseCommand implements EventListenerInterface
{
    use LoggerTrait;

    /** @var Config */
    private $config;
    /** @var string */
    private $default_category;
    /** @var array */
    private $channels;
    /** @var Project[] */
    private $projects;

    public function __construct(
        LoggerInterface $logger,
        Net_SmartIRC $irc,
        UserDb $userDb,
        EventumXmlRpcClient $rpcClient,
        Config $config
    ) {
        parent::__construct($logger, $irc, $userDb, $rpcClient);
        $this->default_category = $config['default_category'];
        $this->config = $config;
    }

    public function register(Net_SmartIRC $irc)
    {
        $pollInterval = $this->config['events.poll_interval'];
        $irc->registerTimeHandler($pollInterval, $this, 'notifyEvents');
    }

    public function notifyEvents(Net_SmartIRC $irc)
    {
        if (!$this->configure()) {
            return;
        }

        if (!$irc->isConnected()) {
            $this->debug('Disconnected; Skip events processing');

            return;
        }

        foreach ($this->projects as $project) {
            if (!$project->anyChannelJoined($irc)) {
                continue;
            }
            $this->processMessages($project);
        }
    }

    private function processMessages(Project $project)
    {
        foreach ($this->getPendingMessages($project) as $row) {
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
                $this->markEventSent($project, $row['ino_id']);
                continue;
            }

            $channels = $project->getChannels();
            if (!$channels) {
                $this->debug('No channels, this should not happen');
                continue;
            }

            /** @var Channel $channel */
            foreach ($channels as $channel) {
                $message = $row['ino_message'];
                if (isset($row['issue_url'])) {
                    $message .= ' - ' . $row['issue_url'];
                } elseif (isset($row['emails_url'])) {
                    $message .= ' - ' . $row['emails_url'];
                }

                if ($channel->getProjectCount() > 1) {
                    // if multiple projects display in the same channel, display project in message
                    $message = '[' . $row['project_name'] . '] ' . $message;
                }

                if ($channel->hasCategory($row['ino_category'])) {
                    $this->sendResponse($channel->name, $message);
                }
            }
            $this->markEventSent($project, $row['ino_id']);
        }
    }

    /**
     * @param Project $project
     * @return array
     */
    private function getPendingMessages(Project $project)
    {
        try {
            return $this->rpcClient->getPendingMessages($project->getId(), 50);
        } catch (XmlRpcException $e) {
            $this->error($e->getMessage());

            return [];
        }
    }

    /**
     * Mark message as sent
     *
     * @param Project $project
     * @param int $ino_id
     */
    private function markEventSent(Project $project, $ino_id)
    {
        try {
            $this->rpcClient->markEventSent($project->getId(), (int)$ino_id);
        } catch (XmlRpcException $e) {
            $this->error($e->getMessage());
        }
    }

    private function configure()
    {
        if ($this->channels === null) {
            $this->channels = $this->config->getChannels();
        }

        if ($this->projects === null) {
            try {
                $this->projects = $this->resolveProjects();
            } catch (XmlRpcException $e) {
                $this->error($e->getMessage());

                return false;
            }

            if (!$this->projects) {
                $this->warning('No projects enabled, skip events processing');

                return false;
            }

            foreach ($this->projects as $project) {
                $channels = implode(', ', $project->getChannelNames());
                $this->notice("Loaded project '{$project->getTitle()}' (#{$project->getId()}): Channels: $channels");
            }
        }

        return (bool)$this->projects;
    }

    private function resolveProjects()
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
            /** @var Channel $channel */
            foreach ($channels as $channel) {
                $channel->addProject($project);
            }
            $projects[$project->getId()] = $project;
        }

        return $projects;
    }
}
