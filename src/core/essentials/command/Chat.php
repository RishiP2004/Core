<?php

declare(strict_types = 1);

namespace core\essentials\command;

use core\Core;
use core\CorePlayer;

use core\essentials\Essentials;

use pocketmine\command\{
	PluginCommand,
	CommandSender
};
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class Chat extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("chat", Core::getInstance());

		$this->manager = $manager;

		$this->setPermission("core.essentials.command.chat");
		$this->setUsage("<on : off : list : check>");
		$this->setDescription("Change Chat Type of a Player or yourself");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
		if(!$sender->hasPermission($this->getPermission())) {
			$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
			return false;
		}
		if(count($args) < 1) {
			$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /chat " . $this->getUsage());
			return false;
		}
		switch(strtolower($args[0])) {
			case "on":
				if(count($args) < 3) {
					$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /chat on <type> [player]");
					return false;
				}
				switch(strtolower($args[1])) {
					case CorePlayer::STAFF:
						$type = CorePlayer::STAFF;
					break;
					case CorePlayer::NORMAL:
						$type = CorePlayer::NORMAL;
					break;
					case CorePlayer::VIP:
						$type = CorePlayer::VIP;
					break;
					default:
						$sender->sendMessage(Core::ERROR_PREFIX . $args[1] . " is not a valid Type");
						return false;
					break;
				}
				if($type === CorePlayer::NORMAL) {
					$sender->sendMessage(Core::ERROR_PREFIX . "Normal is the default Chat Type");
					return false;
				}
				if(isset($args[2])) {
					$player = Server::getInstance()->getPlayer($args[2]);

					if(!$sender->hasPermission($this->getPermission() . "." . $type . ".other")) {
						$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission for " . ucfirst($type) . " Chat for Others");
						return false;
					}
					if(!$player instanceof CorePlayer) {
						$sender->sendMessage(Core::ERROR_PREFIX . $args[2] . " is not a valid Player");
						return false;
					}
					if($player->getChatType() === $type) {
						$sender->sendMessage(Core::ERROR_PREFIX . $player->getName() . " is already in " . ucfirst($type) . " Chat");
						return false;
					} else {
						$sender->sendMessage(Core::PREFIX . "Set " . $player->getName() . "'s Chat Type to " . ucfirst($type));
						$player->setChatType($type);
						$player->sendMessage(Core::PREFIX . $sender->getName() . " set your Chat Type to " . ucfirst($type));
						return true;
					}
				}
				if(!$sender instanceof CorePlayer) {
					$sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this command");
					return false;
				}
				if(!$sender->hasPermission($this->getPermission() . "." . $type)) {
					$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission for " . ucfirst($type) . " Chat");
					return false;
				}
				if($sender->getChatType() === CorePlayer::STAFF) {
					$sender->sendMessage(Core::ERROR_PREFIX . "You are already in Staff Chat");
					return false;
				} else {
					$sender->setChatType($type);
					$sender->sendMessage(Core::PREFIX . "Set your Chat Type to " . ucfirst($type));
					return true;
				}
				break;
			case "off":
				if(count($args) < 1) {
					$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /chat off [player]");
					return false;
				}
				if(isset($args[1])) {
					$player = Server::getInstance()->getPlayer($args[1]);

					if(!$player instanceof CorePlayer) {
						$sender->sendMessage(Core::ERROR_PREFIX . $args[2] . " is not a valid Player");
						return false;
					}
					if($player->getChatType() === CorePlayer::NORMAL) {
						$sender->sendMessage(Core::ERROR_PREFIX . $player->getName() . " is already in Normal Chat");
						return false;
					} else {
						$sender->sendMessage(Core::PREFIX . "Reset " . $player->getName() . "'s Chat Type to Normal");
						$player->setChatType(CorePlayer::NORMAL);
						$player->sendMessage(Core::PREFIX . $sender->getName() . " reset your Chat Type to Normal");
						return true;
					}
				}
				if(!$sender instanceof CorePlayer) {
					$sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this command");
					return false;
				}
				if($sender->getChatType() === CorePlayer::NORMAL) {
					$sender->sendMessage(Core::ERROR_PREFIX . "You are already in Normal Chat");
					return false;
				} else {
					$sender->setChatType(CorePlayer::NORMAL);
					$sender->sendMessage(Core::ERROR_PREFIX . "Reset your Chat Type to Normal");
					return true;
				}
				break;
			case "list":
				if(!$sender->hasPermission($this->getPermission() . ".list")) {
					$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission for Listing players in the Chat Type");
					return false;
				}
				$types = [CorePlayer::STAFF, CorePlayer::NORMAL, CorePlayer::VIP];

				if(isset($args[1])) {
					switch(strtolower($args[1])) {
						case CorePlayer::STAFF:
							$sender->sendMessage(Core::PREFIX . "Players in Staff Chat:");

							$typ[] = CorePlayer::STAFF;

							foreach(Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
								if($onlinePlayer instanceof CorePlayer) {
									$typ[CorePlayer::STAFF] = $onlinePlayer->getName();
								}
							}
							$sender->sendMessage(TextFormat::GRAY . implode(", ", $typ[0]));
						break;
						case CorePlayer::NORMAL:
							$sender->sendMessage(Core::PREFIX . "Players in Normal Chat:");

							$typ[] = CorePlayer::NORMAL;

							foreach(Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
								if($onlinePlayer instanceof CorePlayer) {
									$typ[CorePlayer::NORMAL] = $onlinePlayer->getName();
								}
							}
							$sender->sendMessage(TextFormat::GRAY . implode(", ", $typ[1]));
						break;
						case CorePlayer::VIP:
							$sender->sendMessage(Core::PREFIX . "Players in VIP Chat:");

							$typ[] = CorePlayer::VIP;

							foreach(Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
								if($onlinePlayer instanceof CorePlayer) {
									$typ[CorePlayer::VIP] = $onlinePlayer->getName();
								}
							}
							$sender->sendMessage(TextFormat::GRAY . implode(", ", $typ[1]));
						break;
						default:
							$sender->sendMessage(Core::ERROR_PREFIX . $args[1] . " is not a valid Type");
							return false;
						break;
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
					return true;
				}
				foreach($types as $type) {
					$sender->sendMessage(TextFormat::GRAY . $type . " (x" . count($type[2]) . "):\n" . TextFormat::GRAY . implode(", ", $type[2]));
					return true;
				}
				break;
			case "say":
				if(count($args) < 2) {
					$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /chat say <type> <message>");
					return false;
				}
				if(isset($args[1])) {
					switch(strtolower($args[1])) {
						case CorePlayer::STAFF:
							$type = CorePlayer::STAFF;
						break;
						case CorePlayer::NORMAL:
							$type = CorePlayer::NORMAL;
						break;
						case CorePlayer::VIP:
							$type = CorePlayer::VIP;
						break;
						case "all":
							$type = "all";
						break;
						default:
							$sender->sendMessage(Core::ERROR_PREFIX . $args[1] . " is not a valid Type");
							return false;
						break;
					}
				}
				if(!$sender instanceof CorePlayer) {
					$sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
					return false;
				} else {
					if(!$sender->hasPermission($this->getPermission() . "." . $type)) {
						$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission for Sending messages");
						return false;
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
				}
			break;
			case "check":
				if(count($args) < 1) {
					$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /chat check [player]");
					return false;
				}
				if(isset($args[1])) {
					$player = Server::getInstance()->getPlayer($args[1]);

					if(!$player instanceof CorePlayer) {
						$sender->sendMessage(Core::ERROR_PREFIX . $args[2] . " is not a valid Player");
						return false;
					} else {
						$sender->sendMessage(Core::PREFIX . $player->getName() . " is in " . ucfirst($player->getChatType()) . " Chat");
						return true;
					}
				}
				if(!$sender instanceof CorePlayer) {
					$sender->sendMessage(Core::ERROR_PREFIX . $args[2] . " is not a valid Player");
					return false;
				} else {
					$sender->sendMessage(Core::PREFIX . "You are in " . ucfirst($sender->getChatType()) . " Chat");
					return true;
				}
			break;
		}
		return true;
	}
}