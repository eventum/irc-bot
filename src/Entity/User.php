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

use Eventum\RPC\EventumXmlRpcClient;

class User
{
    /** @var string */
    public $login;
    /** @var string */
    public $nick;
    /** @var string */
    private $token;

    public function __construct($login, $token, $nick)
    {
        $this->login = $login;
        $this->nick = $nick;
        $this->token = $token;
    }

    /**
     * @param EventumXmlRpcClient $client
     * @return EventumXmlRpcClient
     */
    public function getXmlRpcClient(EventumXmlRpcClient $client)
    {
        $cloned = clone $client;
        $cloned->setCredentials($this->login, $this->token);

        return $cloned;
    }

    public function __toString()
    {
        return "{$this->nick} ({$this->login})";
    }

    public function __sleep()
    {
        return ['login', 'nick'];
    }

    public function __debugInfo()
    {
        return [
            'login' => $this->login,
            'nick' => $this->nick,
            'token' => '***hidden***',
        ];
    }
}
