<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;

use core\stats\Stats;

use core\utils\PocketMine;

use pocketmine\command\{
    PluginCommand,
    CommandSender,
    ConsoleCommandSender
};

use pocketmine\Server;

use pocketmine\plugin\PluginBase;

use pocketmine\permission\Permission;

class PluginPermissions extends PluginCommand {
    private $manager;

    public function __construct(Stats $manager) {
        parent::__construct("pluginpermissions", Core::getInstance());

        $this->manager = $manager;

        $this->setAliases(["pluginperm", "pp"]);
        $this->setPermission("core.stats.command.pluginpermissions");
        $this->setUsage("<plugin>");
        $this->setDescription("Check Permissions of a Plugin");;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /pluginpermissions " . $this->getUsage());
            return false;
        }
        $plugin = (strtolower($args[0]) === "pocketmine" or strtolower($args[0]) === "pmmp") ? "pocketmine" : Server::getInstance()->getPluginManager()->getPlugin($args[0]);

        if($plugin === null) {
            $sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Plugin");
            return false;
        }
        $permissions = ($plugin instanceof PluginBase) ? $plugin->getDescription()->getPermissions() : PocketMine::getPocketMinePermissions();

        if(empty($permissions)) {
            $sender->sendMessage(Core::ERROR_PREFIX . $plugin->getName() . " does not have any Permissions");
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
            $sender->sendMessage(Core::PREFIX . "List of all Plugin Permissions from " . ($plugin instanceof PluginBase) ? $plugin : "PocketMine-MP (" . $pageNumber . " / " . $maxPageNumber . ") :");

            foreach($chunkedPermissions[$pageNumber - 1] as $permission) {
                if($permission instanceof Permission) {
                    $sender->sendMessage(Core::PREFIX . '- ' . $permission->getName());
                }
            }
            return true;
        }
    }
}
