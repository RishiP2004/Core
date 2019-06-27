<?php

declare(strict_types = 1);

namespace core\network\command\subCommand;

use core\Core;

use core\utils\SubCommand;

use pocketmine\command\CommandSender;

class Subtract extends SubCommand {
    private $core;

    public function __construct(Core $core) {
        $this->core = $core;
    }

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission("core.network.subcommand.subtract");
    }

    public function getUsage() : string {
        return "<time>";
    }

    public function getName() : string {
        return "subtract";
    }

    public function getDescription() : string {
        return "Subtract the time for Restart";
    }

    public function getAliases() : array {
        return [];
    }

    public function execute(CommandSender $sender, array $args) : bool {
        if(count($args) < 1) {
            return false;
        }
        if(!is_numeric($args[0])) {
            $sender->sendMessage($this->core->getErrorPrefix() . $args[1] . " is not Numeric");
            return false;
        } else {
            $this->core->getNetwork()->getTimer()->subtractTime((int) $args[0]);
            $sender->sendMessage($this->core->getPrefix() . "Subtracted " . $args[0] . " seconds from Restart timer");
            return true;
        }
    }
}