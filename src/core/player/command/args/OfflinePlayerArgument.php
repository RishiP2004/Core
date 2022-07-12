<?php

namespace core\player\command\args;

use CortexPE\Commando\args\BaseArgument;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\player\Player;
use pocketmine\Server;

class OfflinePlayerArgument extends BaseArgument {
    protected bool $exact;

    public function __construct(string $name, bool $optional = false, bool $exact = true) {
        parent::__construct($name, $optional);
        $this->exact = $exact;
    }
	
    public function getNetworkType() : int {
        return AvailableCommandsPacket::ARG_TYPE_TARGET;
    }
	
    public function canParse(string $testString, CommandSender $sender) : bool {
        return (bool)preg_match("/^(?!rcon|console)[a-zA-Z0-9_ ]{1,16}$/i", $testString);
    }

    public function parse(string $argument, CommandSender $sender) {
        return Server::getInstance()->getOfflinePlayer($argument);
    }

    public function getTypeName() : string {
        return 'player';
    }
}