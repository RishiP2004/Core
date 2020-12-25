<?php

declare(strict_types = 1);

namespace core\social\command\subCommand;

use core\Core;

use core\social\Social;

use core\utils\SubCommand;

use pocketmine\command\CommandSender;

class Tweet extends SubCommand {
	private $manager;

	public function __construct(Social $manager) {
		$this->manager = $manager;
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
            $sender->sendMessage(Core::ERROR_PREFIX . "The Tweet is Over the Max Limit: 280");
            return false;
        } else {
            $this->manager->postTweet(implode(" ", $args));
            $sender->sendMessage(Core::PREFIX . "Tweet: " . $args[0] . " Posted");
            return true;
        }
    }
}