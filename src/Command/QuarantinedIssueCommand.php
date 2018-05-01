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

namespace Eventum\IrcBot\Command;

use Eventum\IrcBot\IrcClient;
use Eventum\IrcBot\UserDb;
use Eventum\RPC\EventumXmlRpcClient;
use Eventum\RPC\XmlRpcException;
use Net_SmartIRC;
use Net_SmartIRC_data;

class QuarantinedIssueCommand extends BaseCommand
{
    /** @var UserDb */
    private $userDb;
    /** @var EventumXmlRpcClient */
    private $rpcClient;

    public function __construct(IrcClient $ircClient, UserDb $userDb, EventumXmlRpcClient $rpcClient)
    {
        parent::__construct($ircClient);
        $this->userDb = $userDb;
        $this->rpcClient = $rpcClient;
    }

    /**
     * Format is "list-quarantined"
     *
     * @param Net_SmartIRC $irc
     * @param Net_SmartIRC_data $data
     */
    final public function listQuarantined(Net_SmartIRC $irc, Net_SmartIRC_data $data)
    {
        if (!$this->userDb->has($data->nick)) {
            $this->sendResponse($data->nick, 'Error: You need to be authenticated to run this command.');

            return;
        }

        try {
            $list = $this->rpcClient->getQuarantinedIssueList();
        } catch (XmlRpcException $e) {
            $this->sendResponse($data->nick, 'Error: Temporary error');

            return;
        }

        $count = count($list);
        if ($count === 0) {
            $this->sendResponse($data->nick, 'There are no quarantined issues as of now.');

            return;
        }

        $this->sendResponse($data->nick, "The following are the details of the {$count} quarantined issue(s):");
        foreach ($list as $row) {
            $msg = sprintf(
                'Issue #%d: %s, Assignment: %s, %s', $row['iss_id'], $row['iss_summary'],
                $row['assigned_users'], $row['issue_url']
            );
            $this->sendResponse($data->nick, $msg);
        }
    }
}
