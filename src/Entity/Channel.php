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

class Channel
{
    /** @var string */
    public $name;
    /** @var string */
    public $key;
    /** @var string[] */
    public $categories;
    /** @var Project[] */
    private $projects;

    public function __construct($channelName, array $categories)
    {
        $parts = explode(' ', $channelName, 2);
        if (count($parts) > 1) {
            $this->name = $parts[0];
            $this->key = $parts[1];
        } else {
            $this->name = $channelName;
        }
        $this->categories = $categories;
    }

    public function addProject(Project $project)
    {
        $this->projects[$project->getId()] = $project;

        return $this;
    }

    /**
     * @return Project[]
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * @return int
     */
    public function getProjectCount()
    {
        return count($this->projects);
    }

    /**
     * Returns true if this channel is tagged with category
     * @param string $category
     * @return bool
     */
    public function hasCategory($category)
    {
        return in_array($category, $this->categories, true);
    }
}
