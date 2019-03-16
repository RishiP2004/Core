<?php

use core\Core;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class Plugins extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("plugins", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.plugins.command");
        $this->setDescription("See the Server's Plugins");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
            $list = "";

            foreach(($plugins = $sender->getServer()->getPluginManager()->getPlugins()) as $plugin) {
                if(\strlen($list) > 0) {
                    $list .= ", ";
                }
                $list .= $plugin->isEnabled() ? TextFormat::GREEN : TextFormat::RED;
                $list .= $plugin->getDescription()->getFullName();
            }
            $sender->sendMessage($this->core->getPrefix() . "Plugins (" . count($plugins) . "): " . $list);
            return true;
        }
    }
}