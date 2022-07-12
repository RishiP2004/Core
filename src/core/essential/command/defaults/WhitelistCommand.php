<?php

declare(strict_types = 1);

namespace core\essential\command\defaults;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;

use core\Core;

use core\player\PlayerManager;

use core\network\NetworkManager;
use core\network\server\Server;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

use pocketmine\permission\Permission;
//subcommands?
class WhitelistCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("whitelist.command");
		$this->registerArgument(0, new RawStringArgument("arg", false));
	}

	public function canUse(CommandSender $sender) {
		return $sender->hasPermission("whitelist.command");
	}

    public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
		switch(strtolower($args["arg"])) {
       		case "on":
       			if(!isset($args["server"])) {
       				$sender->sendMessage(Core::PREFIX . "Usage: /whitelist on <server : all>");
       				return;
       			}
       			$server = NetworkManager::getInstance()->getServer($args[1]);
					
       			if(is_null($server) && strtolower($args[1]) !== "all") {
       				$sender->sendMessage(Core::PREFIX . $args[1] . " is not a valid Athena Server");
       				return;
       			}
       			if(strtolower($args[1]) === "all") {
       				foreach(NetworkManager::getInstance()->getServers() as $server) {
       					if($server instanceof Server) {
       						$server->setWhitelisted();
       					}
       				}
       				$sender->sendMessage(Core::PREFIX . "Turned the WhitelistCommand On for all ServersCommand");
       				return;
       			} else {
       				if($server->isWhitelisted()) {
       					$sender->sendMessage(Core::PREFIX . $server->getName() . " is already Whitelisted");
       					return;
       				} else {
       					$server->setWhitelisted(true);
       					$sender->sendMessage(Core::PREFIX . "Turned the WhitelistCommand On for the Server " . $server->getName());
       					return;
       				}
       			}
       		break;
       		case "off":
       			if(!isset($args[1])) {
       				$sender->sendMessage(Core::PREFIX . "Usage: /whitelist off <server : all>");
       				return;
       			}
       			$server = NetworkManager::getInstance()->getServer($args[1]);
					
       			if(is_null($server) && strtolower($args[1]) !== "all") {
       				$sender->sendMessage(Core::PREFIX . $args[1] . " is not a valid Athena Server");
       				return;
       			}
       			if(strtolower($args[1]) === "all") {
       				foreach(NetworkManager::getInstance()->getServers() as $server) {
       					if($server instanceof Server) {
       						$server->setWhitelisted(false);
       					}
       				}
       				$sender->sendMessage(Core::PREFIX . "Turned the WhitelistCommand Off for all ServersCommand");
       				return;
       			} else {
       				if(!$server->isWhitelisted()) {
       					$sender->sendMessage(Core::PREFIX . $server->getName() . " is already not Whitelisted");
       					return;
       				} else {
       					$server->setWhitelisted(false);
       					$sender->sendMessage(Core::PREFIX . "Turned the WhitelistCommand Off for the Server " . $server->getName());
       					return;
       				}
       			}
            break;
       		case "list":
       			if(!isset($args[1])) {
       				$sender->sendMessage(Core::PREFIX . "Usage: /whitelist off <server : all>");
       				return;
       			}
       			$server = NetworkManager::getInstance()->getServer($args[1]);
					
       			if(is_null($server) && strtolower($args[1]) !== "all") {
       				$sender->sendMessage(Core::PREFIX . $args[1] . " is not a valid Athena Server");
       				return;
       			}
       			if(strtolower($args[1]) === "all") {
       				PlayerManager::getInstance()->getAllCoreUsers(function($users) use ($sender) {
       					foreach($users as $user) {
       						if($user->hasPermission("core.network.whitelist")) {
       							$users[] = $user->getName();
       						}
       					}
       					$message = \implode($users, ", ");
       					$sender->sendMessage(Core::PREFIX . "Whitelisted Players " . count($users)  . ":");
       					$sender->sendMessage(TextFormat::GRAY . $message);
       					return true;
       				});
       			} else {
       				PlayerManager::getInstance()->getAllCoreUsers(function($users) use ($sender, $server) {
       					foreach($users as $user) {
       						if($user->hasPermission("core.network. " . $server->getName() . " . whitelist")) {
       							$users[] = $user->getName();
       						}
       					}
       					$message = implode(", ", $users);
       					$sender->sendMessage(Core::PREFIX . "Whitelisted Players " . count($users)  . ":");
       					$sender->sendMessage(TextFormat::GRAY . $message);
       					return;
       				});
       			}
            break;
       		case "add":
       			if(!isset($args[1])) {
       				$sender->sendMessage(Core::PREFIX . "Usage: /whitelist add <player> <server : all>");
       				return;
       			}
       			PlayerManager::getInstance()->getCoreUser($args[1], function($user) use ($sender, $args) {
       				if(is_null($user)) {
       					$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
       					return;
       				}
       				$server = NetworkManager::getInstance()->getServer($args[2]);
					
       				if(is_null($server) && strtolower($args[2]) !== "all") {
       					$sender->sendMessage(Core::PREFIX . $args[2] . " is not a valid Athena Server");
       					return;
       				}
       				if(strtolower($args[2]) === "all") {
       					foreach(NetworkManager::getInstance()->getServers() as $server) {
       						if($server instanceof Server) {
       							$perm = new Permission("core.network." . $server->getName() . ".whitelist");
									
       							$user->addPermission($perm);
       						}
       					}
       					$sender->sendMessage(Core::PREFIX . "Added " . $user->getName() . " to WhitelistCommand for all ServersCommand");
       					return;
					} else {
       					if($user->hasPermission("core.network." . $server->getName() . ".whitelist")) {
       						$sender->sendMessage(Core::PREFIX . $user->getName() . " is already Whitelisted to the Server " . $server->getName());
       						return;
       					}
       					$perm = new Permission("core.network." . $server->getName() . ".whitelist");
							
       					$user->addPermission($perm);
       					$sender->sendMessage(Core::PREFIX . "Added " . $user->getName() . " to the WhitelistCommand for the Server " . $server->getName());
       					return;
       				}
				});
				return;
            break;
            case "remove":
            	if(!isset($args[1])) {
            		$sender->sendMessage(Core::PREFIX . "Usage: /whitelist remove <player> <server : all>");
            		return;
            	}
            	PlayerManager::getInstance()->getCoreUser($args[1], function($user) use ($sender, $args) {
            		if(is_null($user)) {
            			$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
            			return;
            		}
            		$server = NetworkManager::getInstance()->getServer($args[2]);
					
            		if(is_null($server) && strtolower($args[2]) !== "all") {
            			$sender->sendMessage(Core::ERROR_PREFIX . $args[2] . " is not a valid Athena Server");
            			return;
            		}
            		if(strtolower($args[2]) === "all") {
            			foreach(NetworkManager::getInstance()->getServers() as $server) {
            				if($server instanceof Server) {
            					$perm = new Permission("core.network." . $server->getName() . ".whitelist");
									
            					$user->removePermission($perm);
            				}
            			}
            			$sender->sendMessage(Core::PREFIX . "Removed " . $user->getName() . " from WhitelistCommand for all ServersCommand");
            			return;
            		} else {
            			if(!$user->hasPermission("core.network." . $server->getName() . ".whitelist")) {
            				$sender->sendMessage(Core::PREFIX . $user->getName() . " is already not Whitelisted to the Server " . $server->getName());
            				return;
            			}
            			$perm = new Permission("core.network." . $server->getName() . ".whitelist");
							
            			$user->removePermission($perm);
            			$sender->sendMessage(Core::PREFIX . "Removed " . $user->getName() . " from WhitelistCommand for the Server " . $server->getName());
            			return;
            		}
            	});
            	return;
            break;
		}
		return;
    }
}