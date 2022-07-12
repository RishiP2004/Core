<?php

declare(strict_types = 1);

namespace core\player\command;

use core\Core;

use core\player\command\args\OfflinePlayerArgument;
use core\player\traits\PlayerCallTrait;

use CortexPE\Commando\BaseCommand;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class ListPlayerPermissionsCommand extends BaseCommand {
	use PlayerCallTrait;

	public function prepare() : void {
		$this->setPermission("listplayerpermission.command");
		$this->registerArgument(0, new OfflinePlayerArgument("player", false));
	}

    public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
		$this->getCoreUser($args["player"]->getName(), function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args["player"] . " is not a valid Player");
				return false;
			} else {
				$sender->sendMessage(Core::PREFIX . $user->getName() . "'s Permissions:");
				
				if(empty($user->getPermissions()) or !is_array($user->getPermissions())) {
					$sender->sendMessage(TextFormat::GRAY . "None");
					return true;
				}
				$sender->sendMessage(TextFormat::GRAY . implode(", ", (array) $user->getPermissions()));
				return true;
			}	
        });
    }
}