<?php
/**
 *    ___________________________
 *   /  _____/\______   \_   ___ \  ___________   ____
 *  /   \  ___ |     ___/    \  \/ /  _ \_  __ \_/ __ \
 *  \    \_\  \|    |   \     \___(  <_> )  | \/\  ___/
 *   \______  /|____|    \______  /\____/|__|    \___  >
 *          \/                  \/                   \/
 */
namespace GPCore\Essentials\Commands;

use GPCore\GPCore;

use GPCore\Stats\Objects\GPPlayer;

use Essentials

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Jump extends PluginCommand {
    private $GPCore;
    
    public function __construct(GPCore $GPCore) {
        parent::__construct("jump", $GPCore);
       
        $this->GPCore = $GPCore;
       
        $this->setPermission("GPCore.Essentials.Command.Jump");
        $this->setUsage("[player]");
        $this->setDescription("Jump yourself or a Player to the Block you are Facing");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender instanceof GPPlayer) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        }
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }	
        if(isset($args[0])) {
			if(!$sender->hasPermission($this->getPermission() . ".Other")) {
				$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
				return false;
			}
            $player = $this->GPCore->getServer()->getPlayer($args[0]);

            if(!$player instanceof GPPlayer) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not Online");
                return false;
            }
            if(!$player->getGPUser()->hasAccount()) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
                return false;
            }
            $block = $player->getTargetBlock(100, Essentials::NON_SOLID_BLOCKS);

			if($block === null) {
				$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "There isn't a Reachable Block to Jump too");
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
				$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Jumped " . $player->getName() . " to the Facing Block");
				$player->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " Jumped you to your Facing Block");
                return true;
            }
        }
        if($sender instanceof GPPlayer) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
		}
		$block = $sender->getTargetBlock(100, Essentials::NON_SOLID_BLOCKS);
			
		if($block === null) {
			$sender->sendMessage($this->GPCore->getInstance()->getBroadcast()->getErrorPrefix() . "There isn't a Reachable Block to Jump too");
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
			$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Jumped to the Facing Block");
			return true;
        }
    }
}