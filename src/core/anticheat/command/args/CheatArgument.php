<?php

namespace core\anticheat\command\args;

use core\anticheat\AntiCheatManager;

use CortexPE\Commando\args\StringEnumArgument;

use pocketmine\command\CommandSender;

class CheatArgument extends StringEnumArgument {
    public function __construct(string $name, bool $optional = false, protected bool $exact = true) {
        parent::__construct($name, $optional);
    }

	public function getEnumValues() : array {
		$val = ["all"];

		foreach(AntiCheatManager::getInstance()->getCheats() as $cheat) {
			$val[] = $cheat->getName();
		}
		return array_keys($val);
	}

	public function parse(string $argument, CommandSender $sender) {
		return $this->getValue($argument);
	}

	public function getValue(string $string) {
		if($string == "all") {
			return $string;
		}
		return AntiCheatManager::getInstance()->getCheat($string);
	}

    public function getTypeName() : string {
        return 'cheat';
    }
}