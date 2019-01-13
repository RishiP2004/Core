<?php

namespace GPCore\Essentials\Defaults\Commands;

use GPCore\GPCore;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class SaveCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("save", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.Save");
        $this->setUsage("[on : off]");
        $this->setDescription("Save Command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /save" . " " . $this->getUsage());
            return false;
        } else {
            if($args[0]) {
                switch(strtolower($args[0])) {
                    case "on":
                        $this->GPCore->getServer()->setAutoSave(true);
                        $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Auto Save Enabled");
                    break;
                    case "off":
                        $this->GPCore->getServer()->setAutoSave(false);
                        $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Auto Save Disabled");
                    break;
                }
            }
            foreach($sender->getServer()->getOnlinePlayers() as $player) {
                $player->save();
            }
            foreach($sender->getServer()->getLevels() as $level) {
                $level->save(true);
            }
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Saved everything");
            return true;
        }
    }
}