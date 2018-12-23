<?php
/**
 *    ___________________________
 *   /  _____/\______   \_   ___ \  ___________   ____
 *  /   \  ___ |     ___/    \  \/ /  _ \_  __ \_/ __ \
 *  \    \_\  \|    |   \     \___(  <_> )  | \/\  ___/
 *   \______  /|____|    \______  /\____/|__|    \___  >
 *          \/                  \/                   \/
 */
namespace GPCore\Stats\Commands;

use GPCore\GPCore;

use GPCore\Utils\PocketMineUtils;

use pocketmine\command\{
    PluginCommand,
    CommandSender,
    ConsoleCommandSender
};

use pocketmine\plugin\PluginBase;

use pocketmine\permission\Permission;

class PluginPermissionsCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("pluginpermissions", $GPCore);

        $this->GPCore = $GPCore;

        $this->setAliases(["pluginperm", "pp"]);
        $this->setPermission("GPCore.Stats.Command.PluginPermissions");
        $this->setUsage("<plugin>");
        $this->setDescription("Check Permissions of a Plugin");;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /pluginpermissions" . " " . $this->getUsage());
            return false;
        }
        $plugin = (strtolower($args[0]) === "pocketmine" or strtolower($args[0]) === "pmmp") ? "pocketmine" : $this->GPCore->getServer()->getPluginManager()->getPlugin($args[0]);

        if($plugin === null) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Plugin");
            return false;
        }
        $permissions = ($plugin instanceof PluginBase) ? $plugin->getDescription()->getPermissions() : PocketMineUtils::getPocketMinePermissions();

        if(empty($permissions)) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $plugin->getName() . " does not have any Permissions");
            return false;
        } else {
            $pageHeight = $sender instanceof ConsoleCommandSender ? 48 : 6;
            $chunkedPermissions = array_chunk($permissions, $pageHeight);
            $maxPageNumber = count($chunkedPermissions);

            if(!isset($args[1]) or !is_numeric($args[1]) or $args[1] <= 0) {
                $pageNumber = 1;
            } else if($args[1] > $maxPageNumber) {
                $pageNumber = $maxPageNumber;
            } else {
                $pageNumber = $args[1];
            }
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "List of all Plugin Permissions from " . ($plugin instanceof PluginBase) ? $plugin : "PocketMine-MP (" . $pageNumber . " / " . $maxPageNumber . ") :");

            foreach($chunkedPermissions[$pageNumber - 1] as $permission) {
                if($permission instanceof Permission) {
                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . '- ' . $permission->getName());
                }
            }
            return true;
        }
    }
}
