<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;

use core\essentials\Essentials;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class Plugins extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("plugins", Core::getInstance());

		$this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.plugins");
        $this->setDescription("See the Server's Plugins");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
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
            $sender->sendMessage(Core::PREFIX . "Plugins (" . count($plugins) . "): " . $list);
            return true;
        }
    }
}