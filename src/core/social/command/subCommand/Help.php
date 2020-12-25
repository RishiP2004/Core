<?php

declare(strict_types = 1);

namespace core\social\command\subCommand;

use core\Core;

use core\social\Social;

use core\utils\SubCommand;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class Help extends SubCommand {
	private $manager;

	public function __construct(Social $manager) {
		$this->manager = $manager;
	}

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission("core.social.twitter.help");
    }

    public function getUsage() : string {
        return "";
    }

    public function getName() : string {
        return "help";
    }

    public function getDescription() : string {
        return "Help about twitter";
    }

    public function getAliases() : array {
        return [];
    }

    public function execute(CommandSender $sender, array $args) : bool {
        $sender->sendMessage(Core::PREFIX  . "TwitterSend Help:");
        $sender->sendMessage(TextFormat::GRAY . "/twitter help");
        $sender->sendMessage(TextFormat::GRAY . "/twitter follow <username>");
        $sender->sendMessage(TextFormat::GRAY . "/twitter directmessage <user> <message>");
        $sender->sendMessage(TextFormat::GRAY . "/twitter tweet <tweet>");
        return true;
    }
}