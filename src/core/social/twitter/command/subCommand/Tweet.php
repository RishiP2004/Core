<?php

namespace core\social\twitter\command\subCommand;

use core\Core;

use core\utils\SubCommand;

use pocketmine\command\CommandSender;

class Tweet extends SubCommand {
    private $core;

    public function __construct(Core $core) {
        $this->core = $core;
    }

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission("core.social.twitter.tweet");
    }

    public function getUsage() : string {
        return "<tweet>";
    }

    public function getName() : string {
        return "tweet";
    }

    public function getDescription() : string {
        return "Tweet something on twitter";
    }

    public function getAliases() : array {
        return [];
    }

    public function execute(CommandSender $sender, array $args) : bool {
        if(count($args) > 1) {
            return false;
        }
        if(strlen(implode(" ", $args)) > 280) {
            $sender->sendMessage($this->core->getErrorPrefix() . "The Tweet is Over the Max Limit: 280");
            return false;
        } else {
            $this->core->getSocial()->getTwitter()->postTweet(implode(" ", $args));
            $sender->sendMessage($this->core->getPrefix() . "Tweet: " . $args[0] . " Posted");
            return true;
        }
    }
}