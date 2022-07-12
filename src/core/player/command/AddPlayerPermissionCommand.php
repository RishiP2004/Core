<?php

declare(strict_types = 1);

namespace core\player\command;

use core\Core;

use core\player\CorePlayer;

use core\player\traits\PlayerCallTrait;
use core\player\command\args\OfflinePlayerArgument;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;

use pocketmine\Server;

use pocketmine\command\CommandSender;

use pocketmine\permission\Permission;

class AddPlayerPermissionCommand extends BaseCommand {
	use PlayerCallTrait;

    public function prepare() : void {
    	$this->setPermission("addplayerpermission.command");
		$this->registerArgument(0, new OfflinePlayerArgument("player"));
		$this->registerArgument(1, new RawStringArgument("permission"));
	}

    public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
		$this->getCoreUser($args["player"]->getName(), function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args["player"] . " is not a valid Player");
				return false;
			}
			if($user->hasPermission($args["permission"])) {
				$sender->sendMessage(Core::ERROR_PREFIX . $user->getName() . " already has the Permission " . $args[1]);
				return false;
			} else {
				$perm = new Permission($args["permission"]);
				$user->addPermission($perm);

				$player = Server::getInstance()->getPlayerByPrefix($user->getName());
		
				if($player instanceof CorePlayer) {
					$player->sendMessage(Core::PREFIX . $sender->getName() . " gave you the Permission " . $perm->getName());
				}
				$sender->sendMessage(Core::PREFIX . "Added the Permission " . $perm->getName() . " to " . $user->getName());
				return true;
			}
        });
    }
}