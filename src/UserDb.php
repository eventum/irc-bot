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

class UserDb
{
    /**
     * List of authenticated users
     *
     * @var array
     */
    private $db = [];

    public function add($nick, $email)
    {
        $this->db[$nick] = $email;
    }

    public function rename($old_nick, $new_nick)
    {
        if (!array_key_exists($old_nick, $this->db)) {
            return;
        }

        $this->db[$new_nick] = $this->db[$old_nick];
        unset($this->db[$old_nick]);
    }

    public function remove($nick)
    {
        if (!array_key_exists($nick, $this->db)) {
            return;
        }

        unset($this->db[$nick]);
    }

    public function has($nick)
    {
        return array_key_exists($nick, $this->db);
    }

    public function findByEmail($email)
    {
        $key = array_search($email, $this->db, true);
        if ($key !== false) {
            return $key;
        }

        return null;
    }

    public function all()
    {
        return $this->db;
    }
}
