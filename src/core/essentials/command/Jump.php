<?php

declare(strict_types = 1);

namespace core\essentials\command;

use core\Core;
use core\CorePlayer;

use core\utils\Entity;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Jump extends PluginCommand {
    private $core;
    
    public function __construct(Core $core) {
        parent::__construct("jump", $core);
       
        $this->core = $core;
       
        $this->setPermission("core.essentials.command.jump");
        $this->setUsage("[player]");
        $this->setDescription("Jump yourself or a Player to the Block you are Facing");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        }
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }	
        if(isset($args[0])) {
			if(!$sender->hasPermission($this->getPermission() . ".other")) {
				$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
				return false;
			}
            $player = $this->core->getServer()->getPlayer($args[0]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not Online");
                return false;
            }
            $block = $player->getTargetBlock(100, Entity::NON_SOLID_BLOCKS);

			if($block === null) {
				$sender->sendMessage($this->core->getErrorPrefix() . "There isn't a Reachable Block to Jump too");
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
				$sender->sendMessage($this->core->getPrefix() . "Jumped " . $player->getName() . " to the Facing Block");
				$player->sendMessage($this->core->getPrefix() . $sender->getName() . " Jumped you to your Facing Block");
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
		}
		$block = $sender->getTargetBlock(100, Entity::NON_SOLID_BLOCKS);
			
		if($block === null) {
			$sender->sendMessage($this->core->getInstance()->getErrorPrefix() . "There isn't a Reachable Block to Jump too");
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
			$sender->sendMessage($this->core->getPrefix() . "Jumped to the Facing Block");
			return true;
        }
    }
}