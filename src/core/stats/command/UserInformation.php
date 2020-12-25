<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use core\stats\Stats;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class UserInformation extends PluginCommand {
    private $manager;

    public function __construct(Stats $manager) {
        parent::__construct("userinformation", Core::getInstance());

        $this->manager = $manager;

        $this->setAliases(["userinfo"]);
        $this->setPermission("core.stats.command.userinformation");
        $this->setUsage("[player]");
        $this->setDescription("Check a Player's Information");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0])) {
			$this->manager->getCoreUser($args[0], function($user) use ($sender, $args) {
				if(is_null($user)) {
					$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
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
				return false;
			} else {
				$sender->sendMessage(Core::PREFIX . "Your Information:");
				$sender->sendMessage(TextFormat::GRAY . "Register Date: " . $sender->getCoreUser()->getRegisterDate());
				$sender->sendMessage(TextFormat::GRAY . "Ip: " . $sender->getCoreUser()->getIp());
				$sender->sendMessage(TextFormat::GRAY . "Locale: " . $sender->getCoreUser()->getLocale());
				return true;
			}
		}
        return true;
    }
}