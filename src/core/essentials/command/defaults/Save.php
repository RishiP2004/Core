<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Save extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("save", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.save.command");
        $this->setUsage("[on : off]");
        $this->setDescription("Save Command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
            if(isset($args[0])) {
                switch(strtolower($args[0])) {
                    case "on":
                        $this->core->getServer()->setAutoSave(true);
                        $sender->sendMessage($this->core->getPrefix() . "Auto Save Enabled");
                    break;
                    case "off":
                        $this->core->getServer()->setAutoSave(false);
                        $sender->sendMessage($this->core->getPrefix() . "Auto Save Disabled");
                    break;
                }
            }
            foreach($sender->getServer()->getOnlinePlayers() as $player) {
                $player->save();
            }
            foreach($sender->getServer()->getLevels() as $level) {
                $level->save(true);
            }
            $sender->sendMessage($this->core->getPrefix() . "Saved everything");
            return true;
        }
    }
}