<?php

declare(strict_types = 1);

namespace core\player\command;

use core\Core;

use core\player\CorePlayer;
use core\player\{
	command\args\OfflinePlayerArgument,
	Statistics,
	traits\PlayerCallTrait
};

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\IntegerArgument;

use pocketmine\Server;

use pocketmine\command\CommandSender;

class SetCoinsCommand extends BaseCommand {
	use PlayerCallTrait;

	public function prepare() : void {
		$this->setPermission("setcoins.command");
		$this->registerArgument(0, new OfflinePlayerArgument("player"));
		$this->registerArgument(1, new IntegerArgument("amount"));
	}

	public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
		$this->getCoreUser($args["player"]->getName(), function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args["player"] . " is not a valid Player");
				return false;
			}
			if($args["amount"] + $user->getCoins() > Statistics::MAX_COINS) {
				$sender->sendMessage(Core::ERROR_PREFIX . $user->getName() . " will have over the Maximum amount of Coins");
				return false;
			} else {
				$user->setCoins((int) $args["amount"]);

				$player = Server::getInstance()->getPlayerByPrefix($user->getName());
		
				if($player instanceof CorePlayer) {
					$player->sendMessage(Core::PREFIX . $sender->getName() . " set your Coins to " . Statistics::COIN_UNIT . $args["amount"]);
				}
				$sender->sendMessage(Core::PREFIX . "Set " . $user->getName() . "'s Coins to " . Statistics::COIN_UNIT . $args["amount"]);
				return true;
			}
		});
    }
}