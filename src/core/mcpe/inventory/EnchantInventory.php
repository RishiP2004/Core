<?php

namespace core\mcpe\inventory;

use pocketmine\Player;

class EnchantInventory extends \pocketmine\inventory\EnchantInventory {
	public $random = null;

	public $bookshelfAmount = 0;

	public $levels = null;
	public $entries = null;

	public function onClose(Player $who) : void {
		$this->dropContents($this->holder->getLevel(), $this->holder->add(0.5, 0.5, 0.5));
		return;
	}
}