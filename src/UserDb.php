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

namespace Eventum\IrcBot;

use Eventum\IrcBot\Entity\User;
use InvalidArgumentException;
use SplObjectStorage;

class UserDb
{
    /** @var User[] */
    private $db;

    public function __construct()
    {
        $this->db = new SplObjectStorage();
    }

    public function add(User $user)
    {
        if ($this->db->contains($user)) {
            throw new InvalidArgumentException("User already exists: {$user}");
        }
        $this->db->attach($user);

        return $this;
    }

    public function remove(User $user)
    {
        if (!$this->db->contains($user)) {
            throw new InvalidArgumentException("User not present: {$user}");
        }

        $this->db->detach($user);

        return $this;
    }

    public function all()
    {
        return $this->db;
    }

    public function findByEmail($email)
    {
        foreach ($this->db as $user) {
            if ($user->email === $email) {
                return $user;
            }
        }

        return null;
    }

    public function findByNick($nick)
    {
        foreach ($this->db as $user) {
            if ($user->nick === $nick) {
                return $user;
            }
        }

        return null;
    }
}
