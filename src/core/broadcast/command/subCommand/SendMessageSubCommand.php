<?php

declare(strict_types = 1);

namespace core\broadcast\command\subCommand;

use core\Core;

use core\broadcast\BroadcastManager;

use core\player\CorePlayer;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;

use pocketmine\Server;

use pocketmine\command\CommandSender;

class SendMessageSubCommand extends BaseSubCommand {
	public function prepare() : void {
		$this->setPermission("broadcast.subcommand.sendmessage");
		$this->registerArgument(0, new RawStringArgument("message"));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		if($sender instanceof CommandSender) {
			Server::getInstance()->broadcastMessage(BroadcastManager::getInstance()->broadcastByConsole($sender, $args[0]));
			$sender->sendMessage(Core::PREFIX . "Sent Message: " . $args[0] . " to everyone");
		} else if($sender instanceof CorePlayer) {
			Server::getInstance()->broadcastMessage(BroadcastManager::getInstance()->broadcast($args[0]));
			$sender->sendMessage(Core::PREFIX . "Sent Message: " . $args[0] . " to everyone");
		}
	}
}