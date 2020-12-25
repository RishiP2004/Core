<?php

declare(strict_types = 1);

namespace core\network\command\subCommand;

use core\Core;

use core\network\Network;

use core\utils\{
    SubCommand,
    Math
};

use pocketmine\command\CommandSender;

class Time extends SubCommand {
	private $manager;

	public function __construct(Network $manager) {
		$this->manager = $manager;
	}

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission("core.network.subcommand.time");
    }

    public function getUsage() : string {
        return "";
    }

    public function getName() : string {
        return "time";
    }

    public function getDescription() : string {
        return "Check the Time left until Restart";
    }

    public function getAliases() : array {
        return [];
    }

    public function execute(CommandSender $sender, array $args) : bool {
        $sender->sendMessage(Core::PREFIX . "Time remaining until Restart: " . Math::getFormattedTime($this->manager->getTimer()->getTime()));
        return true;
    }
}