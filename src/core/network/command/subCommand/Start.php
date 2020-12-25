<?php

declare(strict_types = 1);

namespace core\network\command\subCommand;

use core\Core;

use core\network\Network;

use core\utils\SubCommand;

use pocketmine\command\CommandSender;

class Start extends SubCommand {
	private $manager;

	public function __construct(Network $manager) {
		$this->manager = $manager;
	}

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission("core.network.subcommand.start");
    }

    public function getUsage() : string {
        return "";
    }

    public function getName() : string {
        return "start";
    }

    public function getDescription() : string {
        return "Start the time for Restart";
    }

    public function getAliases() : array {
        return [];
    }

    public function execute(CommandSender $sender, array $args) : bool {
        if(!$this->manager->getTimer()->isPaused()) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Timer is already not paused");
            return false;
        } else {
            $this->manager->getTimer()->setPaused(false);
            $sender->sendMessage(Core::PREFIX . "Timer is no longer paused");
            return true;
        }
    }
}