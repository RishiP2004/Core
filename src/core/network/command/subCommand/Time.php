<?php

namespace core\network\command\subCommand;

use core\Core;

use core\utils\{
    SubCommand,
    MathUtils
};

use pocketmine\command\CommandSender;

class Time extends SubCommand {
    private $core;

    public function __construct(Core $core) {
        $this->core = $core;
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
        $sender->sendMessage($this->core->getPrefix() . "Time remaining until Restart: " . MathUtils::getFormattedTime($this->core->getNetwork()->getTimer()->getTime()));
        return true;
    }
}