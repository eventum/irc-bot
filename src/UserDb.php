<?php

namespace Eventum\IrcBot;

class UserDb
{
    /**
     * List of authenticated users
     *
     * @var array
     */
    private $db = [];

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
}