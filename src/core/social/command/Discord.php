<?php

declare(strict_types = 1);

namespace core\social\command;

use core\Core;

use core\social\{
	Social,
	Access
};

use CortexP\DiscordWebHookAPI\Message;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Discord extends PluginCommand {
    private $manager;
    
    public function __construct(Social $manager) {
        parent::__construct("discord", Core::getInstance());
        
        $this->manager = $manager;
        
        $this->setPermission("core.social.discord");
        $this->setUsage("<message> [name]");
        $this->setDescription("discord Command");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /discord " . $this->getUsage());
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
            	$msg->setUsername(Access::USERNAME);
			}
            $this->manager->sendToDiscord($msg);
            $sender->sendMessage(Core::PREFIX . "The Message was sent to discord");
            return true;
        }
    }
}