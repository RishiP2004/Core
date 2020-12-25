<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use core\stats\Stats;

use pocketmine\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class DeleteAccount extends PluginCommand {
    private $manager;

    public function __construct(Stats $manager) {
        parent::__construct("deleteaccount", Core::getInstance());

        $this->manager = $manager;

        $this->setAliases(["delaccount", "deleteacc"]);
        $this->setPermission("core.stats.command.deleteaccount");
        $this->setUsage("<player>");
        $this->setDescription("Delete a Player's Account");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /deleteaccount " . $this->getUsage());
            return false;
        }
		$this->manager->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
				return false;
			} else {
				$this->manager->unregisterCoreUser($user);
				unlink(Server::getInstance()->getDataPath() . "players/" . strtolower($user->getName()) . ".dat");
				
				$player = Server::getInstance()->getPlayer($user->getName());
		
				if($player instanceof CorePlayer) {
					$player->kick($sender->getName() . " Deleted your Account");
				}
				$sender->sendMessage(Core::PREFIX . "Deleted " . $user->getName() . "'s Account");
				return true;
			}
        });
		return false;
    }
}