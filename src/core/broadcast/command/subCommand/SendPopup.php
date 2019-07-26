<?php

declare(strict_types = 1);

namespace core\broadcast\command\subCommand;

use core\Core;
use core\CorePlayer;

use core\broadcast\Broadcasts;

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
        return "<popup> <player : all>";
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
        if(count($args) < 1) {
            return false;
        } else {
            if(isset($args[1]) && strtolower($args[1]) !== "all") {
                $player = $this->core->getServer()->getPlayer($args[1]);

                if(!$player instanceof CorePlayer) {
                	$sender->sendMessage($this->core->getErrorPrefix() . $args[1] . " is not a valid Player");
                	return false;
				}
            } else {
                $player = null;
            }
			if(is_null($player)) {
				$p = "everyone";
			} else {
				$p = $player->getName();
			}
            if($sender instanceof CommandSender) {
                $this->core->getScheduler()->scheduleRepeatingTask(new DurationSend($this->core, "popup", $player, Broadcasts::DURATIONS["popup"], $this->core->getBroadcast()->broadcastByConsole($sender, $args[0])), 10);
                $sender->sendMessage($this->core->getPrefix() . "Sent Popup: " . $args[0] . " to " . $p);
            } else if($sender instanceof CorePlayer) {
                $this->core->getScheduler()->scheduleRepeatingTask(new DurationSend($this->core, "popup", $player, Broadcasts::DURATIONS["popup"], $sender->broadcast($args[0])), 10);
                $sender->sendMessage($this->core->getPrefix() . "Sent Popup: " . $args[0] . " to " . $p);
            }
            return true;
        }
    }
}