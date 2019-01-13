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

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\Server;

class DefaultGamemodeCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("defaultgamemode", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.DefaultGamemode");
        $this->setUsage("<gamemode>");
        $this->setDescription("Set the Default Gamemode");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /defaultgamemode" . " " . $this->getUsage());
            return false;
        }
        if(Server::getGamemodeFromString($args[0]) === -1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Unknown Gamemode");
            return false;
        }
        if($this->GPCore->getServer()->getDefaultGamemode() === $args[0]) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is already the Default Gamemode");
            return false;
        } else {
            $this->GPCore->getServer()->setConfigInt("gamemode", $args[0]);
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Set Default Gamemode to " . $args[0]);
            return true;
        }
    }
}