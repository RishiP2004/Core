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

class SetSpawnCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("setspawn", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.SetSpawn");
        $this->setUsage("[player]");
        $this->setDescription("Set the Spawn");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(count($args) < 1) {
            if(!$sender instanceof GPPlayer) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You must be a Player to use this Command");
                return false;
            }
        }
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
            $sender->getLevel()->setSpawnLocation($sender);
            $sender->getServer()->setDefaultLevel($sender->getLevel());
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Server's Spawn changed to Level: " . $sender->getLevel()->getName() . " at X: " . $sender->getX() . ", Y: " . $sender->getY() . ", Z: " . $sender->getZ());
            $this->GPCore->getServer()->broadcastMessage($this->GPCore->getBroadcast()->getPrefix() . "Server's Spawn changed to Level: " . $sender->getLevel()->getName() . " at X: " . $sender->getX() . ", Y: " . $sender->getY() . ", Z: " . $sender->getZ() . " by " . $sender->getName());
            return true;
        }
    }
}