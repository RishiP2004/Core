<?php

namespace core\social\discord\command;

use core\Core;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Discord extends PluginCommand {
    private $core;
    
    public function __construct(Core $core) {
        parent::__construct("discord", $core);
        
        $this->core = $core;
        
        $this->setPermission("core.social.discord");
        $this->setUsage("<message>");
        $this->setDescription("discord Command");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /discord" . " " . $this->getUsage());
            return false;
        } else {
            $chatFormat = str_replace(["{PLAYER}", "{MESSAGE}"], [$sender->getName(), implode(" ", $args)], $this->core->getSocial()->getDiscord()->getChatFormat());
           
            $this->core->getSocial()->getDiscord()->sendMessageToDiscord($this->core->getSocial()->getDiscord()->getChatURL(), $chatFormat, $sender->getName(), $this->core->getSocial()->getDiscord()->getChatUsername());
            $sender->sendMessage($this->core->getPrefix() . "The Message: " . $args[0] . " was sent to discord");
            return true;
        }
    }
}