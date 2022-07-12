<?php

declare(strict_types = 1);

namespace core\essential\command;

use core\Core;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;

use CortexPE\Commando\constraint\InGameRequiredConstraint;

use pocketmine\command\CommandSender;

class TogglePMCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("togglepm.command");
		$this->addConstraint(new InGameRequiredConstraint($this));
		$this->registerArgument(0, new RawStringArgument("value", true));
	}

    public function onRun(CommandSender $sender, string $commandLabel, array $args): void {
		if(!isset($args["value"])) {
			if($sender->getCoreUser()->hasDM()) {
				$sender->sendMessage(Core::PREFIX . "You have just turned private messages off");
				$sender->getCoreUser()->toggleDM(false);
			} else if(!$sender->getCoreUser()->hasDM()) {
				$sender->sendMessage(Core::PREFIX . "You have just turned private messages on");
				$sender->getCoreUser()->toggleDM(true);
			}
			return;
		}
		if($args["value"] === "on") {
			$sender->sendMessage(Core::PREFIX . "You have just turned private messages off");
			$sender->getCoreUser()->toggleDM(false);
		} else if($args["value"] === "off") {
			$sender->sendMessage(Core::PREFIX . "You have just turned private messages on");
			$sender->getCoreUser()->toggleDM(true);
		}
    }
}
