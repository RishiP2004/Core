<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\level\Level;

class Difficulty extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("difficulty", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.difficulty.command");
        $this->setUsage("<difficulty>");
        $this->setDescription("Set the Server's Difficulty");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /difficulty " . $this->getUsage());
            return false;
        }
		$difficulty = Level::getDifficultyFromString($args[0]);

		if($sender->getServer()->isHardcore()) {
			$difficulty = Level::DIFFICULTY_HARD;
		}
		if($difficulty === $this->core->getServer()->getDifficulty()) {
			$sender->sendMessage($this->core->getErrorPrefix() . $difficulty . " is already the Server's Difficulty");
			return false;
		}
		if($difficulty === -1) {
			$sender->sendMessage($this->core->getErrorPrefix() . $difficulty . " is not a valid Difficulty");
            return false;
		} else {
			if($sender instanceof CorePlayer) {
				$sender->getLevel()->setDifficulty($difficulty);
				
				foreach($sender->getLevel()->getEntities() as $entity) {
					$entity->flagForDespawn();
				}
			} else {
				$sender->getServer()->setConfigInt("difficulty", $difficulty);
				
				foreach($sender->getServer()->getLevels() as $level) {
					$level->setDifficulty($difficulty);
					
					foreach($level->getEntities() as $entity) {
						$entity->flagForDespawn();
					}
				}
			}
			$sender->sendMessage($this->core->getPrefix() . $difficulty . " is now the Server's Difficulty");
            $this->core->getServer()->broadcastMessage($this->core->getPrefix() . "The Server's Difficulty has been set to " . $difficulty . " by " . $sender->getName());
			return true;
		}
    }
}