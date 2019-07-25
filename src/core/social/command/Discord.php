<?php

declare(strict_types = 1);

namespace core\social\command;

use core\Core;
use discord\Embed;
use discord\Message;
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
        $this->setUsage("<message> [name]");
        $this->setDescription("discord Command");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /discord " . $this->getUsage());
            return false;
        } else {
			$msg = new Message();

			if(strtolower($args[0]) === ".") {
				$msg->setTextToSpeech($args[0]);
			} else {
				$msg->setContent($args[0]);
			}
            if(isset($args[1])) {
            	$msg->setUsername($args[1]);
			} else {
            	$msg->setUsername($this->core->getSocial()->getUsername());
			}
            $msg->setUsername($args[1]);
            $this->core->getSocial()->sendToDiscord($msg);
            $sender->sendMessage($this->core->getPrefix() . "The Message was sent to discord");
            return true;
        }
    }
}