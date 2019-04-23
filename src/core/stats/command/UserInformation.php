<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class UserInformation extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("userinformation", $core);

        $this->core = $core;

        $this->setAliases(["userinfo"]);
        $this->setPermission("core.stats.command.userinformation");
        $this->setUsage("[player]");
        $this->setDescription("Check a Player's Information");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0])) {
			$user = $this->core->getStats()->getCoreUser($args[0]);
		
			if(!$user) {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
				return false;
            } else {
				$sender->sendMessage($this->core->getPrefix() . $user->getName() . "'s Information:");
				$sender->sendMessage(TextFormat::GRAY . "Register Date: " . $user->getRegisterDate());
				$sender->sendMessage(TextFormat::GRAY . "Xuid: " . $user->getXuid());
				$sender->sendMessage(TextFormat::GRAY . "Ip: " . $user->getIp());
				$sender->sendMessage(TextFormat::GRAY . "Locale: " . $user->getLocale());
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
            $sender->sendMessage($this->core->getPrefix() . "Your Information:");
				$sender->sendMessage(TextFormat::GRAY . "Register Date: " . $sender->getCoreUser()->getRegisterDate());
				$sender->sendMessage(TextFormat::GRAY . "Xuid: " . $sender->getCoreUser()->getXuid());
				$sender->sendMessage(TextFormat::GRAY . "Ip: " . $sender->getCoreUser()->getIp());
				$sender->sendMessage(TextFormat::GRAY . "Locale: " . $sender->getCoreUser()->getLocale());
            return true;
        }
    }
}