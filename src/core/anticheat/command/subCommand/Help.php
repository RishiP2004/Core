<?php

declare(strict_types = 1);

namespace core\anticheat\command\subCommand;

use core\anticheat\AntiCheat;
use core\Core;

use core\utils\SubCommand;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class Help extends SubCommand {
	private $manager;

	public function __construct(AntiCheat $manager) {
		$this->manager = $manager;
	}

	public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission("core.cheat.subcommand.help");
    }

    public function getUsage() : string {
        return "";
    }

    public function getName() : string {
        return "help";
    }

    public function getDescription() : string {
        return "Help about Cheats";
    }

    public function getAliases() : array {
        return [];
    }

    public function execute(CommandSender $sender, array $args) : bool {
        $sender->sendMessage(Core::PREFIX . "Cheats Help:");
        $sender->sendMessage(TextFormat::GRAY . "/cheat help");
        $sender->sendMessage(TextFormat::GRAY . "/cheat report <player> <cheat>");
        $sender->sendMessage(TextFormat::GRAY . "/cheat history <add : remove : set> <player> <cheat> <amount>");
        return true;
    }
}