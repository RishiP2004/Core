<?php

declare(strict_types = 1);

namespace core\player\command;

use core\Core;

use core\player\CorePlayer;
use core\player\{
	Statistics,
	traits\PlayerCallTrait
};
use core\player\command\args\OfflinePlayerArgument;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\IntegerArgument;

use pocketmine\Server;

use pocketmine\command\CommandSender;

class TakeCoinsCommand extends BaseCommand {
	use PlayerCallTrait;

	public function prepare() : void {
		$this->setPermission("takecoins.command");
		$this->registerArgument(0, new OfflinePlayerArgument("player"));
		$this->registerArgument(1, new IntegerArgument("amount"));
	}

	public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
		$this->getCoreUser($args["player"]->getName(), function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args["player"] . " is not a valid Player");
				return false;
			} else {
				$user->setCoins($user->getCoins() - (int) $args["amount"]);

				$player = Server::getInstance()->getPlayerByPrefix($user->getName());

				if($player instanceof CorePlayer) {
					$player->sendMessage(Core::PREFIX . $sender->getName() . " Took " . Statistics::COIN_UNIT . $args["amount"] . " from you");
				}
				$sender->sendMessage(Core::PREFIX . "Took away " . Statistics::COIN_UNIT . $args[1] . " from " . $user->getName());
				return true;
			}
        });
    }
}