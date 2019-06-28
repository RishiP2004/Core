<?php

declare(strict_types = 1);

namespace core\anticheat\command\subCommand;

use core\Core;

use core\utils\SubCommand;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class Help extends SubCommand {
    private $core;

    public function __construct(Core $core) {
        $this->core = $core;
    }

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission("core.anticheat.subcommand.help");
    }

    public function getUsage() : string {
        return "";
    }

    public function getName() : string {
        return "help";
    }

    public function getDescription() : string {
        return "Help about Cheat";
    }

    public function getAliases() : array {
        return [];
    }

    public function execute(CommandSender $sender, array $args) : bool {
        $sender->sendMessage($this->core->getPrefix() . "Cheat Help:");
        $sender->sendMessage(TextFormat::GRAY . "/cheat help");
        $sender->sendMessage(TextFormat::GRAY . "/cheat report <player> <cheat>");
        $sender->sendMessage(TextFormat::GRAY . "/cheat history <add : remove : set> <player> <cheat> <amount>");
        return true;
    }
}