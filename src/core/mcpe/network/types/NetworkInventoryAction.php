<?php

declare(strict_types = 1);

namespace core\mcpe\network\types;

use core\mcpe\network\InventoryTransactionPacket;
use core\mcpe\inventory\EnchantInventory;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\inventory\AnvilInventory;

use pocketmine\inventory\transaction\action\{
	InventoryAction,
	SlotChangeAction,
	DropItemAction,
	CreativeInventoryAction
};

use pocketmine\network\mcpe\protocol\types\WindowTypes;

class NetworkInventoryAction {
	public const SOURCE_CONTAINER = 0;
	public const SOURCE_WORLD = 2;
	public const SOURCE_CREATIVE = 3;
	public const SOURCE_CRAFTING_GRID = 100;
	public const SOURCE_TODO = 99999;

	public const SOURCE_TYPE_CRAFTING_ADD_INGREDIENT = -2;
	public const SOURCE_TYPE_CRAFTING_REMOVE_INGREDIENT = -3;
	public const SOURCE_TYPE_CRAFTING_RESULT = -4;
	public const SOURCE_TYPE_CRAFTING_USE_INGREDIENT = -5;

	public const SOURCE_TYPE_ANVIL_INPUT = -10;
	public const SOURCE_TYPE_ANVIL_MATERIAL = -11;
	public const SOURCE_TYPE_ANVIL_RESULT = -12;
	public const SOURCE_TYPE_ANVIL_OUTPUT = -13;

	public const SOURCE_TYPE_ENCHANT_INPUT = -15;
	public const SOURCE_TYPE_ENCHANT_MATERIAL = -16;
	public const SOURCE_TYPE_ENCHANT_OUTPUT = -17;

	public const SOURCE_TYPE_TRADING_INPUT_1 = -20;
	public const SOURCE_TYPE_TRADING_INPUT_2 = -21;
	public const SOURCE_TYPE_TRADING_USE_INPUTS = -22;
	public const SOURCE_TYPE_TRADING_OUTPUT = -23;

	public const SOURCE_TYPE_BEACON = -24;

	public const SOURCE_TYPE_CONTAINER_DROP_CONTENTS = -100;

	public const ACTION_MAGIC_SLOT_CREATIVE_DELETE_ITEM = 0;
	public const ACTION_MAGIC_SLOT_CREATIVE_CREATE_ITEM = 1;

	public const ACTION_MAGIC_SLOT_DROP_ITEM = 0;
	public const ACTION_MAGIC_SLOT_PICKUP_ITEM = 1;
	/** @var int */
	public $sourceType, $windowId, $sourceFlags = 0, $inventorySlot;
	/** @var Item */
	public $oldItem, $newItem;

	public function read(InventoryTransactionPacket $packet) : self {
		$this->sourceType = $packet->getUnsignedVarInt();

		switch($this->sourceType) {
			case self::SOURCE_CONTAINER:
				$this->windowId = $packet->getVarInt();
			break;
			case self::SOURCE_WORLD:
				$this->sourceFlags = $packet->getUnsignedVarInt();
			break;
			case self::SOURCE_CREATIVE:
			break;
			case self::SOURCE_CRAFTING_GRID:
			case self::SOURCE_TODO:
				$this->windowId = $packet->getVarInt();

				switch($this->windowId) {
					/** @noinspection PhpMissingBreakStatementInspection */
					case self::SOURCE_TYPE_CRAFTING_RESULT:
						$packet->isFinalCraftingPart = true;
					case self::SOURCE_TYPE_CRAFTING_USE_INGREDIENT:
						$packet->isCraftingPart = true;
					break;
				}
				break;
			default:
				throw new UnexpectedValueException("Unknown inventory action source type $this->sourceType");
		}
		$this->inventorySlot = $packet->getUnsignedVarInt();
		$this->oldItem = $packet->getSlot();
		$this->newItem = $packet->getSlot();
		return $this;
	}

	public function write(InventoryTransactionPacket $packet) {
		$packet->putUnsignedVarInt($this->sourceType);

		switch($this->sourceType) {
			case self::SOURCE_CONTAINER:
				$packet->putVarInt($this->windowId);
			break;
			case self::SOURCE_WORLD:
				$packet->putUnsignedVarInt($this->sourceFlags);
			break;
			case self::SOURCE_CREATIVE:
			break;
			case self::SOURCE_CRAFTING_GRID:
			case self::SOURCE_TODO:
				$packet->putVarInt($this->windowId);
			break;
			default:
				throw new InvalidArgumentException("Unknown inventory action source type $this->sourceType");
		}
		$packet->putUnsignedVarInt($this->inventorySlot);
		$packet->putSlot($this->oldItem);
		$packet->putSlot($this->newItem);
	}
	/**
	 * @param Player $player
	 *
	 * @return InventoryAction|null
	 *
	 * @throws UnexpectedValueException
	 */
	public function createInventoryAction(Player $player) : ?InventoryAction {
		switch($this->sourceType) {
			case self::SOURCE_CONTAINER:
				$window = $player->getWindow($this->windowId);

				if($window !== null) {
					return new SlotChangeAction($window, $this->inventorySlot, $this->oldItem, $this->newItem);
				}
				throw new UnexpectedValueException("Player " . $player->getName() . " has no open container with window ID $this->windowId");
			case self::SOURCE_WORLD:
				if($this->inventorySlot !== self::ACTION_MAGIC_SLOT_DROP_ITEM) {
					throw new UnexpectedValueException("Only expecting drop-item world actions from the client!");
				}
				return new DropItemAction($this->newItem);
			case self::SOURCE_CREATIVE:
				switch($this->inventorySlot) {
					case self::ACTION_MAGIC_SLOT_CREATIVE_DELETE_ITEM:
						$type = CreativeInventoryAction::TYPE_DELETE_ITEM;
					break;
					case self::ACTION_MAGIC_SLOT_CREATIVE_CREATE_ITEM:
						$type = CreativeInventoryAction::TYPE_CREATE_ITEM;
					break;
					default:
						throw new UnexpectedValueException("Unexpected creative action type $this->inventorySlot");
				}
				return new CreativeInventoryAction($this->oldItem, $this->newItem, $type);
			case self::SOURCE_CRAFTING_GRID:
			case self::SOURCE_TODO:
				switch($this->windowId) {
					case self::SOURCE_TYPE_CRAFTING_ADD_INGREDIENT:
					case self::SOURCE_TYPE_CRAFTING_REMOVE_INGREDIENT:
					case self::SOURCE_TYPE_CONTAINER_DROP_CONTENTS:
						return new SlotChangeAction($player->getCraftingGrid(), $this->inventorySlot, $this->oldItem, $this->newItem);
					case self::SOURCE_TYPE_CRAFTING_RESULT:
						return null;
					case self::SOURCE_TYPE_CRAFTING_USE_INGREDIENT:
						return null;
					case self::SOURCE_TYPE_ENCHANT_INPUT:
					case self::SOURCE_TYPE_ENCHANT_MATERIAL:
					case self::SOURCE_TYPE_ENCHANT_OUTPUT:
						$inv = $player->getWindow(WindowTypes::ENCHANTMENT);

						if(!($inv instanceof EnchantInventory)) {
							return null;
						}
						switch($this->windowId){
							case self::SOURCE_TYPE_ENCHANT_INPUT:
								$this->inventorySlot = 0;
								$local = $inv->getItem(0);
								if($local->equals($this->newItem, true, false)){
									$inv->setItem(0, $this->newItem);
								}
							break;
							case self::SOURCE_TYPE_ENCHANT_MATERIAL:
								$this->inventorySlot = 1;
								$inv->setItem(1, $this->oldItem);
							break;
							case self::SOURCE_TYPE_ENCHANT_OUTPUT:
							break;
						}
						return new SlotChangeAction($inv, $this->inventorySlot, $this->oldItem, $this->newItem);
					case self::SOURCE_TYPE_BEACON:
						$inv = $player->getWindow(WindowTypes::BEACON);

						if(!($inv instanceof EnchantInventory)) {
							return null;
						}
						$this->inventorySlot = 0;
						return new SlotChangeAction($inv, $this->inventorySlot, $this->oldItem, $this->newItem);
					case self::SOURCE_TYPE_ANVIL_INPUT:
					case self::SOURCE_TYPE_ANVIL_MATERIAL:
					case self::SOURCE_TYPE_ANVIL_RESULT:
					case self::SOURCE_TYPE_ANVIL_OUTPUT:
						$inv = $player->getWindow(WindowTypes::ANVIL);

						if(!($inv instanceof AnvilInventory)){
							return null;
						}
						switch($this->windowId) {
							case self::SOURCE_TYPE_ANVIL_INPUT:
								$this->inventorySlot = 0;
							break;
							case self::SOURCE_TYPE_ANVIL_MATERIAL:
								$this->inventorySlot = 1;
							break;
							case self::SOURCE_TYPE_ANVIL_OUTPUT:
								$inv->sendSlot(2, $inv->getViewers());
							break;
							case self::SOURCE_TYPE_ANVIL_RESULT:
								$this->inventorySlot = 2;
								$cost = $inv->getItem(2)->getNamedTag()->getInt("RepairCost", 1); //TODO

								if($player->isSurvival() && $player->getXpLevel() < $cost) {
									return null;
								}
								$inv->clear(0);

								if(!($material = $inv->getItem(1))->isNull()) {
									$material = clone $material;
									$material->count -= 1;

									$inv->setItem(1, $material);
								}
								$inv->setItem(2, $this->oldItem, false);

								if($player->isSurvival()) {
									$player->subtractXpLevels($cost);
								}
						}
						return new SlotChangeAction($inv, $this->inventorySlot, $this->oldItem, $this->newItem);
				}
				throw new UnexpectedValueException("Player " . $player->getName() . " has no open container with window ID $this->windowId");
			default:
				throw new UnexpectedValueException("Unknown inventory source type $this->sourceType");
		}
	}
}