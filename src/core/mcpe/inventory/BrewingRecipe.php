<?php

namespace core\mcpe\inventory;

use core\Core;

use pocketmine\inventory\{
	Recipe,
	CraftingManager
};

use pocketmine\item\Item;

use pocketmine\utils\UUID;

class BrewingRecipe implements Recipe {
	private $id = null;
	/** @var Item */
	private $output, $ingredient, $potion;

	public function __construct(Item $result, Item $ingredient, Item $potion) {
		$this->output = clone $result;
		$this->ingredient = clone $ingredient;
		$this->potion = clone $potion;
	}

	public function getPotion() {
		return clone $this->potion;
	}

	public function getId() {
		return $this->id;
	}

	public function setId(UUID $id) {
		if($this->id !== null) {
			throw new \InvalidStateException("ID is already set");
		}
		$this->id = $id;
	}

	public function setInput(Item $item) {
		$this->ingredient = clone $item;
	}

	public function getInput() : Item {
		return clone $this->ingredient;
	}

	public function getResult() : Item {
		return clone $this->output;
	}

	public function registerToCraftingManager(CraftingManager $manager) : void {
		Core::getInstance()->getMCPE()->getBrewingManager()->registerBrewingRecipe($this);
	}
}