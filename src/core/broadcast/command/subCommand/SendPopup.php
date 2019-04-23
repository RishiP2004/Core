<?php

declare(strict_types = 1);

namespace core\broadcast\command\subCommand;

use core\Core;
use core\CorePlayer;

use core\utils\SubCommand;

use core\broadcast\task\DurationSend;

use pocketmine\command\CommandSender;

class SendPopup extends SubCommand {
    private $core;

    public function __construct(Core $core) {
        $this->core = $core;
    }

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission("core.broadcast.subcommand.sendpopup");
    }

    public function getUsage() : string {
        return "<popup> <player>";
    }

    public function getName() : string {
        return "sendpopup";
    }

    public function getDescription() : string {
        return "Send a Popup to the Server";
    }

    public function getAliases() : array {
        return ["sp"];
    }

    public function execute(CommandSender $sender, array $args) : bool {
        if(count($args) < 2) {
            return false;
        } else {
            if(isset($args[1])) {
                $player = $this->core->getServer()->getPlayer($args[1]);
            } else {
                $player = null;
            }
            if($sender instanceof CommandSender) {
                $this->core->getScheduler()->scheduleRepeatingTask(new DurationSend($this->core, "popup", $player, $this->core->getBroadcast()->getDurations("Popup"), $this->core->getBroadcast()->broadcastByConsole($sender, $args[0])), 10);
                $sender->sendMessage($this->core->getPrefix() . "Sent Popup: " . $args[0] . " to " . $player->getName() ?? "everyone");
            } else if($sender instanceof CorePlayer) {
                $this->core->getScheduler()->scheduleRepeatingTask(new DurationSend($this->core, "popup", $player, $this->core->getBroadcast()->getDurations("Popup"), $sender->broadcast($args[0])), 10);
                $sender->sendMessage($this->core->getPrefix() . "Sent Popup: " . $args[0] . " to " . $player->getName() ?? "everyone");
            }
            return true;
        }
    }
}