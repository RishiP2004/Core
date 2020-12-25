<?php

declare(strict_types = 1);

namespace core\essentials\command;

use core\Core;
use core\CorePlayer;

use core\essentials\Essentials;

use core\utils\Entity;

use pocketmine\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Jump extends PluginCommand {
    private $manager;
    
    public function __construct(Essentials $manager) {
        parent::__construct("jump", Core::getInstance());
       
        $this->manager = $manager;
       
        $this->setPermission("core.essentials.command.jump");
        $this->setUsage("[player]");
        $this->setDescription("Jump yourself or a Player to the Block you are Facing");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
            return false;
        }
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }	
        if(isset($args[0])) {
			if(!$sender->hasPermission($this->getPermission() . ".other")) {
				$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
				return false;
			}
            $player = Server::getInstance()->getPlayer($args[0]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not Online");
                return false;
            }
            $block = $player->getTargetBlock(100, Entity::NON_SOLID_BLOCKS);

			if($block === null) {
				$sender->sendMessage(Core::ERROR_PREFIX . "There isn't a Reachable Block to Jump too");
				return false;
            } else {
				if(!$player->getLevel()->getBlock($block->add(0, 2))->isSolid()) {
					$player->teleport($block->add(0, 1));
				}
				switch($side = $args[0]->getDirection()) {
					case 0:
					case 1:
						$side += 3;
					break;
					case 3:
						$side += 2;
					break;
					default:
					break;
				}
				if(!$block->getSide($side)->isSolid()){
					$player->teleport($block);
				}
				$sender->sendMessage(Core::PREFIX . "Jumped " . $player->getName() . " to the Facing Block");
				$player->sendMessage(Core::PREFIX . $sender->getName() . " Jumped you to your Facing Block");
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
            return false;
		}
		$block = $sender->getTargetBlock(100, Entity::NON_SOLID_BLOCKS);
			
		if($block === null) {
			$sender->sendMessage(Core::ERROR_PREFIX . "There isn't a Reachable Block to Jump too");
			return false;
        } else {
			if(!$sender->getLevel()->getBlock($block->add(0, 2))->isSolid()) {
				$sender->teleport($block->add(0, 1));
			}
			switch($side = $sender->getDirection()) {
				case 0:
				case 1:
					$side += 3;
				break;
				case 3:
					$side += 2;
				break;
				default:
				break;
			}
			if(!$block->getSide($side)->isSolid()) {
				$sender->teleport($block);
			}
			$sender->sendMessage(Core::PREFIX . "Jumped to the Facing Block");
			return true;
        }
    }
}