<?php

declare(strict_types = 1);

namespace core\broadcast\command\subCommand;

use core\Core;
use core\CorePlayer;

use core\utils\SubCommand;

use core\broadcast\task\DurationSend;

use pocketmine\command\CommandSender;

class SendTitle extends SubCommand {
    private $core;

    public function __construct(Core $core) {
        $this->core = $core;
    }

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission("core.broadcast.command.subcommand.sendtitle");
    }

    public function getUsage() : string {
        return "<title> [subTitle] <player>";
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
        if(count($args) < 3) {
            return false;
        } else {
            if(isset($args[2])) {
                $player = $this->core->getServer()->getPlayer($args[2]);
            } else {
                $player = null;
            }
			if(is_null($player)) {
				$p = "everyone";
			} else {
				$p = $player->getName();
			}
            if($sender instanceof CommandSender) {
                if(isset($args[1])) {
                    $this->core->getScheduler()->scheduleRepeatingTask(new DurationSend($this->core, "title", $player, $this->core->getBroadcast()->getDurations("title"), $this->core->getBroadcast()->broadcastByConsole($sender, $args[0]), $this->core->getBroadcast()->broadcastByConsole($sender, $args[1])), 10);
                    $sender->sendMessage($this->core->getPrefix() . "Sent SubTitle: " . $args[1] . " to " . $p);
                }
                $this->core->getScheduler()->scheduleRepeatingTask(new DurationSend($this->core, "title", $player, $this->core->getBroadcast()->getDurations("title"), $this->core->getBroadcast()->broadcastByConsole($sender, $args[0])), 10);
                $sender->sendMessage($this->core->getPrefix() . "Sent Title: " . $args[0] . " to " . $p);
            } else if($sender instanceof CorePlayer) {
                if(isset($args[1])) {
                    $this->core->getScheduler()->scheduleRepeatingTask(new DurationSend($this->core, "title", $player, $this->core->getBroadcast()->getDurations("title"), $sender->broadcast($args[0]), $this->core->getBroadcast()->broadcastByConsole($sender, $args[1])), 10);
                    $sender->sendMessage($this->core->getPrefix() . "Sent SubTitle: " . $args[1] . " to " . $p);
                }
                $this->core->getScheduler()->scheduleRepeatingTask(new DurationSend($this->core, "title", $player, $this->core->getBroadcast()->getDurations("title"), $sender->broadcast($args[0])), 10);
                $sender->sendMessage($this->core->getPrefix() . "Sent Title: " . $args[0] . " to " . $p);
            }
            return true;
        }
    }
}