<?php

declare(strict_types = 1);

namespace core\network\command\subCommand;

use core\Core;

use CortexPE\Commando\BaseSubCommand;

use core\network\Networking;

use core\utils\MathUtils;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class MemorySubCommand extends BaseSubCommand {
	public function prepare() : void {
		$this->setPermission("restarter.subcommand.memory");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$sender->sendMessage(Core::PREFIX . "Server Memory Info:");
		$sender->sendMessage(TextFormat::GRAY . "Bytes: " . memory_get_usage(true) . "/" . MathUtils::calculateBytes(Networking::MEMORY_LIMIT));
		$sender->sendMessage(TextFormat::GRAY . "Memory-limit: " . Networking::MEMORY_LIMIT);

		$overloaded = MathUtils::isOverloaded(Networking::MEMORY_LIMIT) ? "Yes" : "No";

		$sender->sendMessage(TextFormat::GRAY . "Overloaded: " . $overloaded);
	}
}