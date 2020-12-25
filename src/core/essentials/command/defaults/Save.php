<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;

use core\essentials\Essentials;

use pocketmine\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Save extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("save", Core::getInstance());

		$this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.save");
        $this->setUsage("[on : off]");
        $this->setDescription("Save Command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        } else {
            if(isset($args[0])) {
                switch(strtolower($args[0])) {
                    case "on":
                        Server::getInstance()->setAutoSave(true);
                        $sender->sendMessage(Core::PREFIX . "Auto Save Enabled");
                    break;
                    case "off":
                        Server::getInstance()->setAutoSave(false);
                        $sender->sendMessage(Core::PREFIX . "Auto Save Disabled");
                    break;
                }
            }
            foreach($sender->getServer()->getOnlinePlayers() as $player) {
                $player->save();
            }
            foreach($sender->getServer()->getLevels() as $level) {
                $level->save(true);
            }
            $sender->sendMessage(Core::PREFIX . "Saved everything");
            return true;
        }
    }
}