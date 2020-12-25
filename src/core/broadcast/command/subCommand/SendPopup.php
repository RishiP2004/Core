<?php

declare(strict_types = 1);

namespace core\broadcast\command\subCommand;

use core\broadcast\Broadcast;
use core\Core;
use core\CorePlayer;


use core\utils\SubCommand;

use core\broadcast\task\DurationSend;

use pocketmine\Server;

use pocketmine\command\CommandSender;

class SendPopup extends SubCommand {
	private $manager;

	public function __construct(Broadcast $manager) {
		$this->manager = $manager;
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
                $player = Server::getInstance()->getPlayer($args[1]);

                if(!$player instanceof CorePlayer) {
                	$sender->sendMessage(Core::ERROR_PREFIX . $args[1] . " is not a valid Player");
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
				Core::getInstance()->getScheduler()->scheduleRepeatingTask(new DurationSend($this->manager, "popup", $player, $this->manager::DURATIONS["popup"], $this->manager->broadcastByConsole($sender, $args[0])), 10);
                $sender->sendMessage(Core::PREFIX . "Sent Popup: " . $args[0] . " to " . $p);
            } else if($sender instanceof CorePlayer) {
                Core::getInstance()->getScheduler()->scheduleRepeatingTask(new DurationSend($this->manager, "popup", $player, $this->manager::DURATIONS["popup"], $sender->broadcast($args[0])), 10);
                $sender->sendMessage(Core::PREFIX . "Sent Popup: " . $args[0] . " to " . $p);
            }
            return true;
        }
    }
}