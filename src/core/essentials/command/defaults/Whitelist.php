<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;

use core\network\server\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class Whitelist extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("whitelist", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.command.whitelist");
        $this->setUsage("<on : off : list : add : remove>");
        $this->setDescription("Whitelist Command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /whitelist " . $this->getUsage());
            return false;
        } else {
            switch(strtolower($args[0])) {
                case "on":
					if(!isset($args[1])) {
						$sender->sendMessage($this->core->getPrefix() . "Usage: /whitelist on <server : all>");
						return false;
					} 
					$server = $this->core->getNetwork()->getServer($args[1]);
					
					if(is_null($server) && strtolower($args[1]) !== "all") {
						$sender->sendMessage($this->core->getPrefix() . $args[1] . " is not a valid Athena Server");
						return false;
					}
					if(strtolower($args[1]) === "all") {
						foreach($this->core->getNetwork()->getServers() as $server) {
							if($server instanceof Server) {
								$server->setWhitelisted();
							}
						}
						$sender->sendMessage($this->core->getPrefix() . "Turned the Whitelist On for all Servers");
						return true;
					} else {
						if($server->isWhitelisted()) {
							$sender->sendMessage($this->core->getPrefix() . $server->getName() . " is already Whitelisted");
							return false;
						} else {
							$server->setWhitelisted(true);
							$sender->sendMessage($this->core->getPrefix() . "Turned the Whitelist On for the Server " . $server->getName());
							return true;
						}
					}
					return false;
                break;
                case "off":
					if(!isset($args[1])) {
						$sender->sendMessage($this->core->getPrefix() . "Usage: /whitelist off <server : all>");
						return false;
					} 
					$server = $this->core->getNetwork()->getServer($args[1]);
					
					if(is_null($server) && strtolower($args[1]) !== "all") {
						$sender->sendMessage($this->core->getPrefix() . $args[1] . " is not a valid Athena Server");
						return false;
					}
					if(strtolower($args[1]) === "all") {
						foreach($this->core->getNetwork()->getServers() as $server) {
							if($server instanceof Server) {
								$server->setWhitelisted(false);
							}
						}
						$sender->sendMessage($this->core->getPrefix() . "Turned the Whitelist Off for all Servers");
						return true;
					} else {
						if(!$server->isWhitelisted()) {
							$sender->sendMessage($this->core->getPrefix() . $server->getName() . " is already not Whitelisted");
							return false;
						} else {
							$server->setWhitelisted(false);
							$sender->sendMessage($this->core->getPrefix() . "Turned the Whitelist Off for the Server " . $server->getName());
							return true;
						}
					}
					return false;
                break;
                case "list":
					if(!isset($args[1])) {
						$sender->sendMessage($this->core->getPrefix() . "Usage: /whitelist off <server : all>");
						return false;
					} 
					$server = $this->core->getNetwork()->getServer($args[1]);
					
					if(is_null($server) && strtolower($args[1]) !== "all") {
						$sender->sendMessage($this->core->getPrefix() . $args[1] . " is not a valid Athena Server");
						return false;
					}
					if(strtolower($args[1]) === "all") {
						$users = [];
						
						foreach($this->core->getStats()->getAllCoreUsers() as $user) {
							if($user->hasPermission("core.network." . $server->getName() . ".whitelist")) {
								$users[] = $user->getName();
							}
						}
						$message = \implode($users, ", ");
						$sender->sendMessage($this->core->getPrefix() . "Whitelisted Players " . count($users)  . ":");
						$sender->sendMessage(TextFormat::GRAY . $message);
						return true;
					} else {
						$users = [];
						
						foreach($this->core->getStats()->getAllCoreUsers() as $user) {
							if($user->hasPermission("core.network." . $server->getName() . ".whitelist")) {
								$users[] = $user->getName();
							}
						}
						$message = \implode($users, ", ");

						$sender->sendMessage($this->core->getPrefix() . "Whitelisted Players " . count($users)  . ":");
						$sender->sendMessage(TextFormat::GRAY . $message);
						return true;
					}
                break;
                case "add":
					if(!isset($args[1])) {	
						$sender->sendMessage($this->core->getPrefix() . "Usage: /whitelist add <player> <server : all>");
						return false;
					} 
					$this->core->getStats()->getCoreUser($args[1], function($user) use ($sender, $args) {
						if(is_null($user)) {
							$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
							return false;
						}
						$server = $this->core->getNetwork()->getServer($args[2]);
					
						if(is_null($server) && strtolower($args[2]) !== "all") {
							$sender->sendMessage($this->core->getPrefix() . $args[2] . " is not a valid Athena Server");
							return false;
						}
						if(strtolower($args[2]) === "all") {
							foreach($this->core->getNetwork()->getServers() as $server) {
								if($server instanceof Server) {
									$user->addPermission("core.network." . $server->getName() . ".whitelist");
								}
							}
							$sender->sendMessage($this->core->getPrefix() . "Added " . $user->getName() . " to Whitelist for all Servers");
							return true;
						} else {						
							if($user->hasPermission("core.network." . $server->getName() . ".whitelist")) {
								$sender->sendMessage($this->core->getPrefix() . $user->getName() . " already is Whitelisted to " . $server->getName());
								return false;
							} else {
								$user->addPermission("core.network." . $server->getName() . ".whitelist");
								$sender->sendMessage($this->core->getPrefix() . "Added " . $user->getName() . " to Whitelist for the Server " . $server->getName());
								return true;
							}
						}
					});
					return false;
                break;
                case "remove":
					if(!isset($args[1])) {	
						$sender->sendMessage($this->core->getPrefix() . "Usage: /whitelist remove <player> <server : all>");
						return false;
					} 
					$this->core->getStats()->getCoreUser($args[1], function($user) use ($sender, $args) {
						if(is_null($user)) {
							$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
							return false;
						}
						$server = $this->core->getNetwork()->getServer($args[2]);
					
						if(is_null($server) && strtolower($args[2]) !== "all") {
							$sender->sendMessage($this->core->getPrefix() . $args[2] . " is not a valid Athena Server");
							return false;
						}
						if(!$user->hasPermission("core.network." . $server->getName() . ".whitelist")) {
							$sender->sendMessage($this->core->getPrefix() . $user->getName() . " is already not Whitelisted to the Server " . $server->getName());
							return false;
						}
						if(strtolower($args[2]) === "all") {
							foreach($this->core->getNetwork()->getServers() as $server) {
								if($server instanceof Server) {
									$user->removePermission("core.network." . $server->getName() . ".whitelist");
								}
							}
							$sender->sendMessage($this->core->getPrefix() . "Removed " . $user->getName() . " from Whitelist for all Servers");
							return true;
						} else {
							$user->removePermission("core.network." . $server->getName() . ".whitelist");
							$sender->sendMessage($this->core->getPrefix() . "Removed " . $user->getName() . " from Whitelist for the Server " . $server->getName());
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