<?php

declare(strict_types = 1);

namespace core\network\command\subCommand;

use core\Core;

use core\network\Network;

use core\utils\SubCommand;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class Help extends SubCommand {
	private $manager;

	public function __construct(Network $manager) {
		$this->manager = $manager;
	}

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission("core.network.subcommand.add");
    }

    public function getUsage() : string {
        return "";
    }

    public function getName() : string {
        return "help";
    }

    public function getDescription() : string {
        return "Help about the Restarter";
    }

    public function getAliases() : array {
        return [];
    }

    public function execute(CommandSender $sender, array $args) : bool {
        $sender->sendMessage(Core::PREFIX . "Restarter Help:");
        $sender->sendMessage(TextFormat::GRAY . "/restarter help");
        $sender->sendMessage(TextFormat::GRAY . "/restarter time");
        $sender->sendMessage(TextFormat::GRAY . "/restarter memory");
        $sender->sendMessage(TextFormat::GRAY . "/restarter start");
        $sender->sendMessage(TextFormat::GRAY . "/restarter stop");
        $sender->sendMessage(TextFormat::GRAY . "/restarter set <time>");
        $sender->sendMessage(TextFormat::GRAY . "/restarter add <time>");
        $sender->sendMessage(TextFormat::GRAY . "/restarter reduce <time>");
        return true;
    }
}