<?php

declare(strict_types = 1);

namespace core\broadcast\command\subCommand;

use core\Core;
use core\CorePlayer;

use core\broadcast\Broadcast;

use core\utils\SubCommand;

use pocketmine\command\CommandSender;
use pocketmine\Server;

class SendMessage extends SubCommand {
	private $manager;

	public function __construct(Broadcast $manager) {
		$this->manager = $manager;
	}

	public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission("core.broadcast.subcommand.sendmessage");
    }

    public function getUsage() : string {
        return "<message>";
    }

    public function getName() : string {
        return "sendmessage";
    }

    public function getDescription() : string {
        return "Send a Message to the Server";
    }

    public function getAliases() : array {
        return ["sm"];
    }

    public function execute(CommandSender $sender, array $args) : bool {
        if(count($args) < 1) {
            return false;
        } else {
            if($sender instanceof CommandSender) {
                Server::getInstance()->broadcastMessage($this->manager->broadcastByConsole($sender, $args[0]));
                $sender->sendMessage(Core::PREFIX . "Sent Message: " . $args[0] . " to everyone");
            } else if($sender instanceof CorePlayer) {
                Server::getInstance()->broadcastMessage($sender->broadcast($args[0]));
                $sender->sendMessage(Core::PREFIX . "Sent Message: " . $args[0] . " to everyone");
            }
            return true;
        }
    }
}