<?php

namespace core\network\command\subCommand;

use core\Core;

use core\utils\SubCommand;

use pocketmine\command\CommandSender;

class Add extends SubCommand {
    private $core;

    public function __construct(Core $core) {
        $this->core = $core;
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
            $sender->sendMessage($this->core->getErrorPrefix() . $args[1] . " is not Numeric");
            return false;
        } else {
            $this->core->getNetwork()->getTimer()->addTime($args[0]);
            $sender->sendMessage($this->core->getPrefix() . "Added " . $args[0] . " seconds to Restart timer");
            return true;
        }
    }
}