<?php

declare(strict_types = 1);

namespace core\broadcast\command\subCommand;

use core\Core;
use core\CorePlayer;

use core\broadcast\Broadcast;

use core\broadcast\Broadcasts;

use core\utils\SubCommand;

use core\broadcast\task\DurationSend;

use pocketmine\Server;

use pocketmine\command\CommandSender;

class SendTitle extends SubCommand {
	private $manager;

	public function __construct(Broadcast $manager) {
		$this->manager = $manager;
	}

	public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission("core.broadcast.command.subcommand.sendtitle");
    }

    public function getUsage() : string {
        return "<player> <title> [subTitle]";
    }

    public function getName() : string {
        return "sendtitle";
    }

    public function getDescription() : string {
        return "Send a Title to the Server";
    }

    public function getAliases() : array {
        return ["st"];
    }

    public function execute(CommandSender $sender, array $args) : bool {
        if(count($args) < 2) {
            return false;
        } else {
            if(isset($args[0]) && strtolower($args[0]) !== "all") {
                $player = Server::getInstance()->getPlayer($args[0]);

				if(!$player instanceof CorePlayer) {
					$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
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
                if(isset($args[2])) {
                    Core::getInstance()->getScheduler()->scheduleRepeatingTask(new DurationSend($this->manager, "title", $player, $this->manager::DURATIONS["title"], $this->manager->broadcastByConsole($sender, $args[1]), $this->manager->broadcastByConsole($sender, $args[2])), 10);
                    $sender->sendMessage(Core::PREFIX . "Sent SubTitle: " . $args[1] . " to " . $p);
                }
                Core::getInstance()->getScheduler()->scheduleRepeatingTask(new DurationSend($this->manager, "title", $player, $this->manager::DURATIONS["title"], $this->manager->broadcastByConsole($sender, $args[1])), 10);
                $sender->sendMessage(Core::PREFIX . "Sent Title: " . $args[0] . " to " . $p);
            } else if($sender instanceof CorePlayer) {
                if(isset($args[2])) {
                    Core::getInstance()->getScheduler()->scheduleRepeatingTask(new DurationSend($this->manager, "title", $player, $this->manager::DURATIONS["title"], $sender->broadcast($args[1]), $this->manager->broadcastByConsole($sender, $args[2])), 10);
                    $sender->sendMessage(Core::PREFIX . "Sent SubTitle: " . $args[1] . " to " . $p);
                }
                Core::getInstance()->getScheduler()->scheduleRepeatingTask(new DurationSend($this->manager, "title", $player, Broadcasts::DURATIONS["title"], $sender->broadcast($args[1])), 10);
                $sender->sendMessage(Core::PREFIX . "Sent Title: " . $args[0] . " to " . $p);
            }
            return true;
        }
    }
}