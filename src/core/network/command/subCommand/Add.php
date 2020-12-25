<?php

declare(strict_types = 1);

namespace core\network\command\subCommand;

use core\Core;

use core\network\Network;

use core\utils\SubCommand;

use pocketmine\command\CommandSender;

class Add extends SubCommand {
	private $manager;

	public function __construct(Network $manager) {
		$this->manager = $manager;
	}

	public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission("core.network.subcommand.add");
    }

    public function getUsage() : string {
        return "<time>";
    }

    public function getName() : string {
        return "add";
    }

    public function getDescription() : string {
        return "Add time to the Restart timer";
    }

    public function getAliases() : array {
        return [];
    }

    public function execute(CommandSender $sender, array $args) : bool {
        if(count($args) < 1) {
            return false;
        }
        if(!is_numeric($args[0])) {
            $sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not Numeric");
            return false;
        } else {
            $this->manager->getTimer()->addTime((int) $args[0]);
            $sender->sendMessage(Core::ERROR_PREFIX . "Added " . $args[0] . " seconds to Restart timer");
            return true;
        }
    }
}