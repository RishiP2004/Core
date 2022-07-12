<?php

declare(strict_types = 1);

namespace core\social\command;

use core\Core;

use core\social\{
	SocialManager,
	Access
};

use CortexPE\Commando\BaseCommand;
use CortexPE\DiscordWebHookAPI\Message;

use pocketmine\command\{
    CommandSender
};

class DiscordCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("discord.command");
		$this->registerArgument(0, new RawStringArgument("message", false));
		$this->registerArgument(1, new RawStringArgument("name", true));
	}
    
    public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
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
		SocialManager::getInstance()->sendToDiscord($msg);
		$sender->sendMessage(Core::PREFIX . "The Message was sent to discord");
		return;
    }
}