<?php

declare(strict_types = 1);

namespace core\broadcast\command\subCommand;

use core\Core;
use core\CorePlayer;

use core\utils\SubCommand;

use pocketmine\command\CommandSender;

class SendMessage extends SubCommand {
    private $core;

    public function __construct(Core $core) {
        $this->core = $core;
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
        if(count($args) < 2) {
            return false;
        } else {
            if($sender instanceof CommandSender) {
                $this->core->getServer()->broadcastMessage($this->core->getBroadcast()->broadcastByConsole($sender, $args[0]));
                $sender->sendMessage($this->core->getPrefix() . "Sent Message: " . $args[0] . " to everyone");
            } else if($sender instanceof CorePlayer) {
                $this->core->getServer()->broadcastMessage($sender->broadcast($args[0]));
                $sender->sendMessage($this->core->getPrefix() . "Sent Message: " . $args[0] . " to everyone");
            }
            return true;
        }
    }
}