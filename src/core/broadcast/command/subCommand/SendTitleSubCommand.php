<?php

declare(strict_types = 1);

namespace core\broadcast\command\subCommand;

use core\broadcast\BroadcastManager;

use core\Core;

use core\player\CorePlayer;
use core\player\command\args\PlayerArgument;

use core\broadcast\task\DurationSendTask;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\{
	RawStringArgument,
	IntegerArgument
};

use pocketmine\Server;

use pocketmine\command\CommandSender;

class SendTitleSubCommand extends BaseSubCommand {
	public function prepare() : void {
		$this->setPermission("broadcast.subcommand.sendtitle");
		$this->registerArgument(0, new PlayerArgument("receiver"));
		$this->registerArgument(1, new RawStringArgument("title"));
		$this->registerArgument(2, new IntegerArgument("duration"));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		if(strtolower($args["receiver"]) !== "all") {
			$player = Server::getInstance()->getPlayerByPrefix($args["receiver"]);

			if(!$player instanceof CorePlayer) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args["receiver"] . " is not a valid Player");
				return;
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
			Core::getInstance()->getScheduler()->scheduleRepeatingTask(new DurationSendTask("title", $player, $args["duration"], BroadcastManager::getInstance()->broadcastByConsole($sender, $args[0])), 10);
			$sender->sendMessage(Core::PREFIX . "Sent Title: " . $args[0] . " to " . $p);
		} else if($sender instanceof CorePlayer) {
			Core::getInstance()->getScheduler()->scheduleRepeatingTask(new DurationSendTask("title", $player, $args["duration"], $sender->broadcast($args[0])), 10);
			$sender->sendMessage(Core::PREFIX . "Sent Title: " . $args[0] . " to " . $p);
		}
	}
}