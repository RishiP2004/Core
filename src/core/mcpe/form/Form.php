<?php

namespace core\mcpe\form;

use pocketmine\Player;

interface Form extends \JsonSerializable {
	public function handleResponse(Player $player, $data) : void;
}
