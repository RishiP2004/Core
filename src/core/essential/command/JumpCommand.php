<?php

declare(strict_types = 1);

namespace core\essential\command;

use core\Core;

use core\player\CorePlayer;

use core\utils\EntityUtils;

use CortexPE\Commando\BaseCommand;
use core\player\command\args\PlayerArgument;

use pocketmine\Server;

use pocketmine\command\CommandSender;

class JumpCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("jump.command");
		$this->registerArgument(0, new PlayerArgument("player", false));
	}

    public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
        if(isset($args["player"])) {
			if(!$sender->hasPermission($this->getPermission() . ".other")) {
				$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
				return;
			}
            $block = $args["player"]->getTargetBlock(100, EntityUtils::NON_SOLID_BLOCKS);

			if($block === null) {
				$sender->sendMessage(Core::ERROR_PREFIX . "There isn't a Reachable BlockCommand to JumpCommand too");
				return;
            } else {
				if(!$args["player"]->getWorld()->getBlock($block->getPosition()->add(0, 2))->isSolid()) {
					$args["player"]->teleport($block->getPosition()->add(0, 1));
				}
				switch($side = $args["player"]->getDirectionVector()) {
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
					$args["player"]->teleport($block->getPosition());
				}
				$sender->sendMessage(Core::PREFIX . "Jumped " . $args["player"]->getName() . " to the Facing BlockCommand");
				$args["player"]->sendMessage(Core::PREFIX . $sender->getName() . " Jumped you to your Facing BlockCommand");
                return;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
            return;
		}
		$block = $sender->getTargetBlock(100, EntityUtils::NON_SOLID_BLOCKS);
			
		if($block === null) {
			$sender->sendMessage(Core::ERROR_PREFIX . "There isn't a Reachable BlockCommand to JumpCommand too");
		} else {
			if(!$sender->getWorld()->getBlock($block->getPosition()->add(0, 2))->isSolid()) {
				$sender->teleport($block->getPosition()->add(0, 1));
			}
			switch($side = $sender->getDirectionVector()) {
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
				$sender->teleport($block->getPosition());
			}
			$sender->sendMessage(Core::PREFIX . "Jumped to the Facing BlockCommand");
		}
    }
}