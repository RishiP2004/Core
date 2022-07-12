<?php

declare(strict_types = 1);

namespace core\essential\command;

use core\Core;

use core\player\CorePlayer;
use core\player\command\args\PlayerArgument;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;

use pocketmine\command\CommandSender;

use pocketmine\Server;
use pocketmine\utils\TextFormat;
//TODO: Use subcommands
class ChatCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("chat.command");
		$this->registerArgument(0, new RawStringArgument("parameter", false));
		$this->registerArgument(1, new RawStringArgument("type", true));
		$this->registerArgument(1, new PlayerArgument("player", true));
	}

	public function getUsageMessage() : string {
		return Core::ERROR_PREFIX . "Usage: /chat <parameter>";
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		switch(strtolower($args["parameter"])) {
			case "on":
				if(count($args) < 2) {
					$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /chat on <type> [player]");
				}
				switch(strtolower($args[1])) {
					case CorePlayer::STAFF_CHAT:
						$type = CorePlayer::STAFF_CHAT;
					break;
					case CorePlayer::NORMAL_CHAT:
						$type = CorePlayer::NORMAL_CHAT;
					break;
					case CorePlayer::VIP_CHAT:
						$type = CorePlayer::VIP_CHAT;
					break;
					default:
						$sender->sendMessage(Core::ERROR_PREFIX . $args["mode"] . " is not a valid Type");
						return;
				}
				if($type === CorePlayer::NORMAL_CHAT) {
					$sender->sendMessage(Core::ERROR_PREFIX . "Normal is the default Chat Type");
					return;
				}
				if(isset($args["player"])) {
					if(!$sender->hasPermission($this->getPermission() . "." . $type . ".other")) {
						$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to set " . ucfirst($type) . " Chat for Others");
						return;
					}
					if($args["player"]->getChatType() === $type) {
						$sender->sendMessage(Core::ERROR_PREFIX . $args["player"]->getName() . " is already in " . ucfirst($type) . " Chat");
						return;
					}
					$sender->sendMessage(Core::PREFIX . "Set " . $args["player"]->getName() . "'s Chat Type to " . ucfirst($type));
					$args["player"]->setChatType($type);
					$args["player"]->sendMessage(Core::PREFIX . $sender->getName() . " set your Chat Type to " . ucfirst($type));
				}
				if(!$sender instanceof CorePlayer) {
					$sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this command");
					return;
				}
				if(!$sender->hasPermission($this->getPermission() . "." . $type)) {
					$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission for " . ucfirst($type) . " ChatCommand");
					return;
				}
				if($sender->getChatType() === CorePlayer::STAFF_CHAT) {
					$sender->sendMessage(Core::ERROR_PREFIX . "You are already in Staff ChatCommand");
					return;
				}
				$sender->setChatType($type);
				$sender->sendMessage(Core::PREFIX . "Set your ChatCommand Type to " . ucfirst($type));
			break;
			case "off":
				if(isset($args["player"])) {
					if($args["player"]->getChatType() === CorePlayer::NORMAL_CHAT) {
						$sender->sendMessage(Core::ERROR_PREFIX . $args["player"]->getName() . " is already in Normal Chat");
						return;
					}
					$sender->sendMessage(Core::PREFIX . "Reset " . $args["player"]->getName() . "'s Chat Type to Normal");
					$args["player"]->setChatType(CorePlayer::NORMAL_CHAT);
					$args["player"]->sendMessage(Core::PREFIX . $sender->getName() . " reset your Chat Type to Normal");
				}
				if(!$sender instanceof CorePlayer) {
					$sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this command");
					return;
				}
				if($sender->getChatType() === CorePlayer::NORMAL_CHAT) {
					$sender->sendMessage(Core::ERROR_PREFIX . "You are already in Normal ChatCommand");
					return;
				}
				$sender->setChatType(CorePlayer::NORMAL_CHAT);
				$sender->sendMessage(Core::ERROR_PREFIX . "Reset your ChatCommand Type to Normal");
			break;
			case "list":
				if(!$sender->hasPermission($this->getPermission() . ".list")) {
					$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission for Listing players in the Chat Type");
					return;
				}
				$types = [CorePlayer::STAFF_CHAT, CorePlayer::NORMAL_CHAT, CorePlayer::VIP_CHAT];

				if(isset($args["type"])) {
					switch(strtolower($args["type"])) {
						case CorePlayer::STAFF_CHAT:
							$sender->sendMessage(Core::PREFIX . "Players in Staff Chat:");

							$typ[] = CorePlayer::STAFF_CHAT;

							foreach(Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
								if($onlinePlayer instanceof CorePlayer) {
									$typ[CorePlayer::STAFF_CHAT] = $onlinePlayer->getName();
								}
							}
							$sender->sendMessage(TextFormat::GRAY . implode(", ", $typ[0]));
						break;
						case CorePlayer::NORMAL_CHAT:
							$sender->sendMessage(Core::PREFIX . "Players in Normal Chat:");

							$typ[] = CorePlayer::NORMAL_CHAT;

							foreach(Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
								if($onlinePlayer instanceof CorePlayer) {
									$typ[CorePlayer::NORMAL_CHAT] = $onlinePlayer->getName();
								}
							}
							$sender->sendMessage(TextFormat::GRAY . implode(", ", $typ[1]));
						break;
						case CorePlayer::VIP_CHAT:
							$sender->sendMessage(Core::PREFIX . "Players in VIP Chat:");

							$typ[] = CorePlayer::VIP_CHAT;

							foreach(Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
								if($onlinePlayer instanceof CorePlayer) {
									$typ[CorePlayer::VIP_CHAT] = $onlinePlayer->getName();
								}
							}
							$sender->sendMessage(TextFormat::GRAY . implode(", ", $typ[1]));
						break;
						default:
							$sender->sendMessage(Core::ERROR_PREFIX . $args["type"] . " is not a valid Type");
							return;
					}
				}
				$sender->sendMessage(Core::PREFIX . "All Chat type Lists:");

				foreach(Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
					if($onlinePlayer instanceof CorePlayer) {
						foreach($types as $type) {
							if($onlinePlayer->getChatType() === $type) {
								$types[$type] = $onlinePlayer->getName();
							}
						}
					}
				}
				if(empty($type[2])) {
					$sender->sendMessage(TextFormat::GRAY . "No one is Online");
					return;
				}
				foreach($types as $type) {
					$sender->sendMessage(TextFormat::GRAY . $type . " (x" . count($type[2]) . "):\n" . TextFormat::GRAY . implode(", ", $type[2]));
					return;
				}
				break;
			case "say":
				if(count($args) < 2) {
					$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /chat say <type> <message>");
					return;
				}
				if(isset($args["type"])) {
					switch(strtolower($args[1])) {
						case CorePlayer::STAFF_CHAT:
							$type = CorePlayer::STAFF_CHAT;
							break;
						case CorePlayer::NORMAL_CHAT:
							$type = CorePlayer::NORMAL_CHAT;
							break;
						case CorePlayer::VIP_CHAT:
							$type = CorePlayer::VIP_CHAT;
							break;
						case "all":
							$type = "all";
							break;
						default:
							$sender->sendMessage(Core::ERROR_PREFIX . $args["type"] . " is not a valid Type");
							return;
					}
				}
				if(!$sender instanceof CorePlayer) {
					$sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
					return;
				}
				if(!$sender->hasPermission($this->getPermission() . "." . $type)) {
					$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission for Sending messages");
					return;
				}
				foreach(Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
					if($onlinePlayer instanceof CorePlayer) {
						if($onlinePlayer->getChatType() === $type) {
							$onlinePlayer->sendMessage($sender->getCoreUser()->getRank()->getChatFormat() . implode(" ", $args[2]));
						}
					}
					if($type === "all") {
						$onlinePlayer->sendMessage($sender->getCoreUser()->getRank()->getChatFormat() . implode(" ", $args[2]));
					}
				}
				break;
			case "check":
				if(isset($args["player"])) {
					$sender->sendMessage(Core::PREFIX . $args["player"]->getName() . " is in " . ucfirst($args["player"]->getChatType()) . " Chat");
				}
				if(!$sender instanceof CorePlayer) {
					$sender->sendMessage(Core::ERROR_PREFIX . "Must be a player to use this command");
					return;
				}
				$sender->sendMessage(Core::PREFIX . "You are in " . ucfirst($sender->getChatType()) . " ChatCommand");
			break;
		}
	}
}