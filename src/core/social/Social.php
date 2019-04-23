<?php

declare(strict_types = 1);

namespace core\social;

use core\Core;

use core\social\discord\Discord;
use core\social\twitter\Twitter;

class Social {
    private $core;

    private $Discord, $Twitter;

    public function __construct(Core $core) {
        $this->core = $core;
        $this->Discord = new Discord($core);
        $this->Twitter = new Twitter($core);
    }

    public function getDiscord() : Discord {
        return $this->Discord;
    }

    public function getTwitter() : Twitter {
        return $this->Twitter;
    }
}