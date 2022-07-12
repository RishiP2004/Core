<?php

declare(strict_types = 1);

namespace core\player\command;

use core\Core;

use core\player\{
	PlayerManager,
	Statistics
};

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\IntegerArgument;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class TopCoinsCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("topcoins.command");
		$this->registerArgument(0, new IntegerArgument("page", true));
	}

    public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
		$page = $args["page"] ?? 1;
		$top = PlayerManager::getInstance()->getTopCoins(5, $page);

		if(empty($top)) {
			$sender->sendMessage(Core::ERROR_PREFIX . "No accounts registered");
			return;
		}
		$message = Core::PREFIX . "Top Coins (Page: " . $page . ")";

		for($i = 0; $i < count($top); ++$i) {
			$message .= TextFormat::EOL . TextFormat::GOLD . "$i + 1" . TextFormat::GRAY . array_keys($top)[$i] . ": " . TextFormat::GREEN . Statistics::COIN_UNIT . " " . array_values($top)[$i];
		}
		$sender->sendMessage($message);
	}
}