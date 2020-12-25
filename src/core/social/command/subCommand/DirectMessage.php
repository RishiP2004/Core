<?php

declare(strict_types = 1);

namespace core\social\command\subCommand;

use core\Core;

use core\social\Social;

use core\utils\SubCommand;

use pocketmine\command\CommandSender;

class DirectMessage extends SubCommand {
    private $manager;

    public function __construct(Social $manager) {
        $this->manager = $manager;
    }

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission("core.social.twitter.directmessage");
    }

    public function getUsage() : string {
        return "<user> <message>";
    }

    public function getName() : string {
        return "directmessage";
    }

    public function getDescription() : string {
        return "Direct Message a User on twitter";
    }

    public function getAliases() : array {
        return ["dm"];
    }

    public function execute(CommandSender $sender, array $args) : bool {
        if(count($args) > 2) {
            return false;
        }
        if(strlen(implode(" ", $args)) > 10000) {
            $sender->sendMessage(Core::ERROR_PREFIX . "The Direct Message is Over the Max Limit: 10000");
            return false;
        } else {
            $this->manager->twitterDirectMessage($args[0], implode(" ", $args));
            $sender->sendMessage(Core::PREFIX . "Direct Message to " . $args[0] . " with the Message: " . $args[1] . " Sent");
            return true;
        }
    }
}