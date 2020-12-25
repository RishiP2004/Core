<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;

use core\essentials\Essentials;

use core\network\Network;

use core\stats\Stats;

use core\network\server\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

use pocketmine\permission\Permission;

class Whitelist extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("whitelist", Core::getInstance());

		$this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.whitelist");
        $this->setUsage("<on : off : list : add : remove>");
        $this->setDescription("Whitelist Command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /whitelist " . $this->getUsage());
            return false;
        } else {
            switch(strtolower($args[0])) {
                case "on":
					if(!isset($args[1])) {
						$sender->sendMessage(Core::PREFIX . "Usage: /whitelist on <server : all>");
						return false;
					} 
					$server = Network::getInstance()->getServer($args[1]);
					
					if(is_null($server) && strtolower($args[1]) !== "all") {
						$sender->sendMessage(Core::PREFIX . $args[1] . " is not a valid Athena Server");
						return false;
					}
					if(strtolower($args[1]) === "all") {
						foreach(Network::getInstance()->getServers() as $server) {
							if($server instanceof Server) {
								$server->setWhitelisted();
							}
						}
						$sender->sendMessage(Core::PREFIX . "Turned the Whitelist On for all Servers");
						return true;
					} else {
						if($server->isWhitelisted()) {
							$sender->sendMessage(Core::PREFIX . $server->getName() . " is already Whitelisted");
							return false;
						} else {
							$server->setWhitelisted(true);
							$sender->sendMessage(Core::PREFIX . "Turned the Whitelist On for the Server " . $server->getName());
							return true;
						}
					}
                break;
                case "off":
					if(!isset($args[1])) {
						$sender->sendMessage(Core::PREFIX . "Usage: /whitelist off <server : all>");
						return false;
					} 
					$server = Network::getInstance()->getServer($args[1]);
					
					if(is_null($server) && strtolower($args[1]) !== "all") {
						$sender->sendMessage(Core::PREFIX . $args[1] . " is not a valid Athena Server");
						return false;
					}
					if(strtolower($args[1]) === "all") {
						foreach(Network::getInstance()->getServers() as $server) {
							if($server instanceof Server) {
								$server->setWhitelisted(false);
							}
						}
						$sender->sendMessage(Core::PREFIX . "Turned the Whitelist Off for all Servers");
						return true;
					} else {
						if(!$server->isWhitelisted()) {
							$sender->sendMessage(Core::PREFIX . $server->getName() . " is already not Whitelisted");
							return false;
						} else {
							$server->setWhitelisted(false);
							$sender->sendMessage(Core::PREFIX . "Turned the Whitelist Off for the Server " . $server->getName());
							return true;
						}
					}
                break;
                case "list":
					if(!isset($args[1])) {
						$sender->sendMessage(Core::PREFIX . "Usage: /whitelist off <server : all>");
						return false;
					} 
					$server = Network::getInstance()->getServer($args[1]);
					
					if(is_null($server) && strtolower($args[1]) !== "all") {
						$sender->sendMessage(Core::PREFIX . $args[1] . " is not a valid Athena Server");
						return false;
					}
					if(strtolower($args[1]) === "all") {
						$users = [];

						Stats::getInstance()->getAllCoreUsers(function($users) use ($sender) {
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
						$users = [];

						Stats::getInstance()->getAllCoreUsers(function($users) use ($sender, $server) {
							foreach($users as $user) {
								if($user->hasPermission("core.network. " . $server->getName() . " . whitelist")) {
									$users[] = $user->getName();
								}
							}
							$message = \implode($users, ", ");
							$sender->sendMessage(Core::PREFIX . "Whitelisted Players " . count($users)  . ":");
							$sender->sendMessage(TextFormat::GRAY . $message);
							return true;
						});
					}
                break;
                case "add":
					if(!isset($args[1])) {	
						$sender->sendMessage(Core::PREFIX . "Usage: /whitelist add <player> <server : all>");
						return false;
					} 
					Stats::getInstance()->getCoreUser($args[1], function($user) use ($sender, $args) {
						if(is_null($user)) {
							$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
							return false;
						}
						$server = Network::getInstance()->getServer($args[2]);
					
						if(is_null($server) && strtolower($args[2]) !== "all") {
							$sender->sendMessage(Core::PREFIX . $args[2] . " is not a valid Athena Server");
							return false;
						}
						if(strtolower($args[2]) === "all") {
							foreach(Network::getInstance()->getServers() as $server) {
								if($server instanceof Server) {
									$perm = new Permission("core.network." . $server->getName() . ".whitelist");
									
									$user->addPermission($perm);
								}
							}
							$sender->sendMessage(Core::PREFIX . "Added " . $user->getName() . " to Whitelist for all Servers");
							return true;
						} else {
							if($user->hasPermission("core.network." . $server->getName() . ".whitelist")) {
								$sender->sendMessage(Core::PREFIX . $user->getName() . " is already Whitelisted to the Server " . $server->getName());
								return false;
							}
							$perm = new Permission("core.network." . $server->getName() . ".whitelist");
							
							$user->addPermission($perm);
							$sender->sendMessage(Core::PREFIX . "Added " . $user->getName() . " to the Whitelist for the Server " . $server->getName());
							return true;
						}
					});
					return false;
                break;
                case "remove":
					if(!isset($args[1])) {	
						$sender->sendMessage(Core::PREFIX . "Usage: /whitelist remove <player> <server : all>");
						return false;
					} 
					Stats::getInstance()->getCoreUser($args[1], function($user) use ($sender, $args) {
						if(is_null($user)) {
							$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
							return false;
						}
						$server = Network::getInstance()->getServer($args[2]);
					
						if(is_null($server) && strtolower($args[2]) !== "all") {
							$sender->sendMessage(Core::ERROR_PREFIX . $args[2] . " is not a valid Athena Server");
							return false;
						}
						if(strtolower($args[2]) === "all") {
							foreach(Network::getInstance()->getServers() as $server) {
								if($server instanceof Server) {
									$perm = new Permission("core.network." . $server->getName() . ".whitelist");
									
									$user->removePermission($perm);
								}
							}
							$sender->sendMessage(Core::PREFIX . "Removed " . $user->getName() . " from Whitelist for all Servers");
							return true;
						} else {
							if(!$user->hasPermission("core.network." . $server->getName() . ".whitelist")) {
								$sender->sendMessage(Core::PREFIX . $user->getName() . " is already not Whitelisted to the Server " . $server->getName());
								return false;
							}
							$perm = new Permission("core.network." . $server->getName() . ".whitelist");
							
							$user->removePermission($perm);
							$sender->sendMessage(Core::PREFIX . "Removed " . $user->getName() . " from Whitelist for the Server " . $server->getName());
							return true;
						}
					});
					return false;
                break;
            }
            return true;
        }
    }
}