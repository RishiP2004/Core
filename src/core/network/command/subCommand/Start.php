<?php

declare(strict_types = 1);

namespace core\network\command\subCommand;

use core\Core;

use core\utils\SubCommand;

use pocketmine\command\CommandSender;

class Start extends SubCommand {
    private $core;

    public function __construct(Core $core) {
        $this->core = $core;
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
        if(!$this->core->getNetwork()->getTimer()->isPaused()) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Timer is already not paused");
            return false;
        } else {
            $this->core->getNetwork()->getTimer()->setPaused(false);
            $sender->sendMessage($this->core->getPrefix() . "Timer is no longer paused");
            return true;
        }
    }
}