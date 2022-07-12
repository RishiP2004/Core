<?php

namespace core\player\command\args;

use core\player\PlayerManager;

use CortexPE\Commando\args\StringEnumArgument;

use pocketmine\command\CommandSender;
//TODO
class RankArgument extends StringEnumArgument {
    public function __construct(string $name, bool $optional = false, protected bool $exact = true) {
        parent::__construct($name, $optional);
    }

	public function getEnumValues() : array {
		$val = ["0"];

		foreach(PlayerManager::getInstance()->getRanksFlat() as $rank) {
			$val[] = $rank->getName();
		}
		return array_keys($val);
	}

	public function parse(string $argument, CommandSender $sender) {
		return $this->getValue($argument);
	}

	public function getValue(string $string) {
		if($string == "0") {
			return $string;
		}
		return PlayerManager::getInstance()->getRankByName($string);
	}

    public function getTypeName() : string {
        return 'rank';
    }
}