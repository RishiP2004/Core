<?php
/**
 *    ___________________________
 *   /  _____/\______   \_   ___ \  ___________   ____
 *  /   \  ___ |     ___/    \  \/ /  _ \_  __ \_/ __ \
 *  \    \_\  \|    |   \     \___(  <_> )  | \/\  ___/
 *   \______  /|____|    \______  /\____/|__|    \___  >
 *          \/                  \/                   \/
 */
namespace GPCore\Essentials\Defaults\Commands;

use GPCore\GPCore;

use GPCore\Stats\Objects\GPPlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\level\Level;

class DifficultyCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("difficulty", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.Difficulty");
        $this->setUsage("<difficulty>");
        $this->setDescription("Set the Server's Difficulty");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /difficulty" . " " . $this->getUsage());
            return false;
        }
		$difficulty = Level::getDifficultyFromString($args[0]);

		if($sender->getServer()->isHardcore()) {
			$difficulty = Level::DIFFICULTY_HARD;
		}
		if($difficulty === $this->GPCore->getServer()->getDifficulty()) {
			$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $difficulty . " is already the Server's Difficulty");
			return false;
		}
		if($difficulty === -1) {
			$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $difficulty . " is not a valid Difficulty");
            return false;
		} else {
			if($sender instanceof GPPlayer) {
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
			$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $difficulty . " is now the Server's Difficulty");
            $this->GPCore->getServer()->broadcastMessage($this->GPCore->getBroadcast()->getPrefix() . "The Server's Difficulty has been set to " . $difficulty . " by " . $sender->getName());
			return true;
		}
    }
}