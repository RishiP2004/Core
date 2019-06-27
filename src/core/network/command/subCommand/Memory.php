<?php

declare(strict_types = 1);

namespace core\network\command\subCommand;

use core\Core;

use core\utils\{
	SubCommand,
	Math
};

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class Memory extends SubCommand {
    private $core;

    public function __construct(Core $core) {
        $this->core = $core;
    }

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission("core.network.subcommand.memory");
    }

    public function getUsage() : string {
        return "";
    }

    public function getName() : string {
        return "memory";
    }

    public function getDescription() : string {
        return "Check Memory info for Restarter";
    }

    public function getAliases() : array {
        return [];
    }

    public function execute(CommandSender $sender, array $args) : bool {
        $sender->sendMessage($this->core->getPrefix() . "Restarter Memory Info:");
        $sender->sendMessage(TextFormat::GRAY . "Bytes: " . memory_get_usage(true) . "/" . Math::calculateBytes($this->core->getNetwork()->getMemoryLimit()));
        $sender->sendMessage(TextFormat::GRAY . "Memory-limit: " . $this->core->getNetwork()->getMemoryLimit());
		
		$overloaded = Math::isOverloaded($this->core->getNetwork()->getMemoryLimit()) ? "Yes" : "No";
		
        $sender->sendMessage(TextFormat::GRAY . "Overloaded: " . $overloaded);
        return true;
    }
}