<?php

declare(strict_types = 1);

namespace core\essential\command;

use core\Core;
use core\player\command\args\PlayerArgument;
use core\player\CorePlayer;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;

use pocketmine\Server;

use pocketmine\command\CommandSender;

class PingCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("ping.command");
		$this->registerArgument(0, new PlayerArgument("player", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		if(isset($args["player"])) {
			if(!$sender->hasPermission($this->getPermission() . ".other")) {
				$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
				return;
			}
			$sender->sendMessage(Core::PREFIX . $args["player"]->getName() . "'s PingCommand is: " . $args["player"]->getNetworkSession()->getPing());
			return;
		}
		if(!$sender instanceof CorePlayer) {
			$sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
			return;
		}
		$sender->sendMessage(Core::PREFIX. "Your Ping is: " . $sender->getNetworkSession()->getPing());
	}
}