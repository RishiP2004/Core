<?php

declare(strict_types = 1);

namespace core\social\command\subCommand;

use core\Core;

use core\social\Social;

use core\utils\SubCommand;

use pocketmine\command\CommandSender;

class Follow extends SubCommand {
	private $manager;

	public function __construct(Social $manager) {
		$this->manager = $manager;
	}

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission("core.social.twitter.follow");
    }

    public function getUsage() : string {
        return "<username>";
    }

    public function getName() : string {
        return "follow";
    }

    public function getDescription() : string {
        return "Follow someone on twitter";
    }

    public function getAliases() : array {
        return [];
    }

    public function execute(CommandSender $sender, array $args) : bool {
        if(count($args) > 1) {
            return false;
        }
        if(strlen(implode(" ", $args)) > 15) {
            $sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Username");
            return false;
        } else {
            $this->manager->twitterFollow(implode(" ", $args));
            $sender->sendMessage(Core::PREFIX . "Followed " . $args[0]);
            return true;
        }
    }
}