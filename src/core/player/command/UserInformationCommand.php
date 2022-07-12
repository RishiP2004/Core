<?php

declare(strict_types = 1);

namespace core\player\command;

use core\Core;

use core\player\CorePlayer;
use core\player\PlayerManager;

use CortexPE\Commando\BaseCommand;
use core\player\command\args\OfflinePlayerArgument;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class UserInformationCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("userinformation.command");
		$this->registerArgument(0, new OfflinePlayerArgument("player", true));
	}

    public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
        if(isset($args["player"])) {
			PlayerManager::getInstance()->getCoreUser($args["player"]->getName(), function($user) use ($sender, $args) {
				if(is_null($user)) {
					$sender->sendMessage(Core::ERROR_PREFIX . $args["player"] . " is not a valid Player");
					return false;
				} else {
					$sender->sendMessage(Core::PREFIX . $user->getName() . "'s Information:");
					$sender->sendMessage(TextFormat::GRAY . "Register Date: " . $user->getRegisterDate());
					$sender->sendMessage(TextFormat::GRAY . "Ip: " . $user->getIp());
					$sender->sendMessage(TextFormat::GRAY . "Locale: " . $user->getLocale());
					return true;
				}
            });
        } else {
			if(!$sender instanceof CorePlayer) {
				$sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
				return;
			}
			$sender->sendMessage(Core::PREFIX . "Your Information:");
			$sender->sendMessage(TextFormat::GRAY . "Register Date: " . $sender->getCoreUser()->getRegisterDate());
			$sender->sendMessage(TextFormat::GRAY . "Ip: " . $sender->getCoreUser()->getIp());
			$sender->sendMessage(TextFormat::GRAY . "Locale: " . $sender->getCoreUser()->getLocale());
		}
    }
}