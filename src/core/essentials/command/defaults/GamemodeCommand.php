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

use pocketmine\Server;

class GamemodeCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("gamemode", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.Gamemode");
        $this->setUsage("<gamemode> [player]");
        $this->setDescription("Set the Gamemode of a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /gamemode" . " " . $this->getUsage());
            return false;
        }
		$gamemode = Server::getGamemodeFromString($args[0]);

		if($gamemode === -1) {
			$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $gamemode . " is not a valid Gamemode");
            return false;
		}
        if(isset($args[1])) {
			if(!$sender->hasPermission($this->getPermission() . ".Other")) {
				$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
				return false;
			}
 			$user = $this->GPCore->getStats()->getGPUser($args[1]);

			if(!$user->hasAccount()) {
				$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[1] . " is not a valid Player");
				return false;
			}
            $player = $user->getGPPlayer();

            if(!$player instanceof GPPlayer) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $user->getUsername() . " is not Online");
                return false;
			}
			if($player->getGamemode() === $gamemode) {
				$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $gamemode . " is already " . $user->getUsername() . "'s Gamemode");
				return false;
            } else {
                $player->setGamemode($gamemode);
				$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Set " . $user->getUsername() . "'s Gamemode to " . $gamemode);
				$player->sendMessage($sender->getName() . " set your Gamemode to " . $gamemode);
                return true;
            }
        }
        if(!$sender instanceof GPPlayer) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
            $sender->setGamemode($gamemode);
			$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Set your Gamemode to " . $gamemode);
            return true;
        }
    }
}