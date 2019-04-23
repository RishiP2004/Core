<?php

declare(strict_types = 1);

namespace core\mcpe\network;

use pocketmine\item\Item;
use pocketmine\item\enchantment\EnchantmentList;

use pocketmine\network\mcpe\NetworkBinaryStream;

use pocketmine\inventory\{
	ShapelessRecipe,
	ShapedRecipe,
	FurnaceRecipe
};

class CraftingDataPacket extends \pocketmine\network\mcpe\protocol\CraftingDataPacket {
	public const ENTRY_ENCHANT_LIST = 4;
	public const ENTRY_SHULKER_BOX = 5;

	protected function decodePayload() : void {
		$this->decodedEntries = [];
		$recipeCount = $this->getUnsignedVarInt();

		for($i = 0; $i < $recipeCount; ++$i) {
			$entry = [];
			$entry["type"] = $recipeType = $this->getVarInt();

			switch($recipeType) {
				case self::ENTRY_SHAPELESS:
				case self::ENTRY_SHULKER_BOX:
					$ingredientCount = $this->getUnsignedVarInt();
					/** @var Item */
					$entry["input"] = [];

					for($j = 0; $j < $ingredientCount; ++$j) {
						$entry["input"][] = $this->getSlot();
					}
					$resultCount = $this->getUnsignedVarInt();
					$entry["output"] = [];

					for($k = 0; $k < $resultCount; ++$k) {
						$entry["output"][] = $this->getSlot();
					}
					$entry["uuid"] = $this->getUUID()->toString();
				break;
				case self::ENTRY_SHAPED:
					$entry["width"] = $this->getVarInt();
					$entry["height"] = $this->getVarInt();
					$count = $entry["width"] * $entry["height"];
					$entry["input"] = [];

					for($j = 0; $j < $count; ++$j) {
						$entry["input"][] = $this->getSlot();
					}
					$resultCount = $this->getUnsignedVarInt();
					$entry["output"] = [];

					for($k = 0; $k < $resultCount; ++$k) {
						$entry["output"][] = $this->getSlot();
					}
					$entry["uuid"] = $this->getUUID()->toString();
				break;
				case self::ENTRY_FURNACE:
				case self::ENTRY_FURNACE_DATA:
					$entry["inputId"] = $this->getVarInt();

					if($recipeType === self::ENTRY_FURNACE_DATA) {
						$entry["inputDamage"] = $this->getVarInt();
					}
					$entry["output"] = $this->getSlot();
				break;
				case self::ENTRY_ENCHANT_LIST:
					$entry["uuid"] = $this->getUUID()->toString();
				break;
				default:
					throw new \UnexpectedValueException("Unhandled recipe type $recipeType!");
			}
			$this->decodedEntries[] = $entry;
		}
		$this->getBool();
	}

	protected function encodePayload() : void {
		$this->putUnsignedVarInt(count($this->entries));

		$writer = new NetworkBinaryStream();

		foreach($this->entries as $d) {
			$entryType = self::writeEntry($d, $writer);

			if($entryType >= 0) {
				$this->putVarInt($entryType);
				$this->put($writer->getBuffer());
			} else {
				$this->putVarInt(-1);
			}
			$writer->reset();
		}
		$this->putBool($this->cleanRecipes);
	}

	private static function writeEntry($entry, NetworkBinaryStream $stream) {
		if($entry instanceof ShapelessRecipe) {
			return self::writeShapelessRecipe($entry, $stream);
		} else if($entry instanceof ShapedRecipe) {
			return self::writeShapedRecipe($entry, $stream);
		} else if($entry instanceof FurnaceRecipe) {
			return self::writeFurnaceRecipe($entry, $stream);
		} else if($entry instanceof EnchantmentList) {
			return self::writeEnchantList($entry, $stream);
		}
		//TODO: add MultiRecipe
		return -1;
	}

	private static function writeShapelessRecipe(ShapelessRecipe $recipe, NetworkBinaryStream $stream) {
		$stream->putUnsignedVarInt($recipe->getIngredientCount());

		foreach($recipe->getIngredientList() as $item) {
			$stream->putSlot($item);
		}
		$results = $recipe->getResults();

		$stream->putUnsignedVarInt(count($results));

		foreach($results as $item) {
			$stream->putSlot($item);
		}
		$stream->put(str_repeat("\x00", 16));
		return CraftingDataPacket::ENTRY_SHAPELESS;
	}

	private static function writeShapedRecipe(ShapedRecipe $recipe, NetworkBinaryStream $stream) {
		$stream->putVarInt($recipe->getWidth());
		$stream->putVarInt($recipe->getHeight());

		for($z = 0; $z < $recipe->getHeight(); ++$z) {
			for($x = 0; $x < $recipe->getWidth(); ++$x) {
				$stream->putSlot($recipe->getIngredient($x, $z));
			}
		}
		$results = $recipe->getResults();

		$stream->putUnsignedVarInt(count($results));

		foreach($results as $item) {
			$stream->putSlot($item);
		}
		$stream->put(str_repeat("\x00", 16));
		return CraftingDataPacket::ENTRY_SHAPED;
	}

	private static function writeFurnaceRecipe(FurnaceRecipe $recipe, NetworkBinaryStream $stream) {
		if(!$recipe->getInput()->hasAnyDamageValue()) {
			$stream->putVarInt($recipe->getInput()->getId());
			$stream->putVarInt($recipe->getInput()->getDamage());
			$stream->putSlot($recipe->getResult());
			return CraftingDataPacket::ENTRY_FURNACE_DATA;
		} else {
			$stream->putVarInt($recipe->getInput()->getId());
			$stream->putSlot($recipe->getResult());
			return CraftingDataPacket::ENTRY_FURNACE;
		}
	}

	private static function writeEnchantList(EnchantmentList $list, NetworkBinaryStream $stream) {
		$stream->putByte($list->getSize());

		for($i = 0; $i < $list->getSize(); $i++) {
			$entry = $list->getSlot($i);

			$stream->putUnsignedVarInt($entry->getCost());
			$stream->putUnsignedVarInt(count($entry->getEnchantments()));
			/** @var Enchantment $enchantment */
			foreach($entry->getEnchantments() as $enchantment) {
				$stream->putUnsignedVarInt($enchantment->getId());
				$stream->putUnsignedVarInt(mt_rand(1, $enchantment->getMaxLevel()));
			}
			$stream->putString($entry->getRandomName());
		}
		return CraftingDataPacket::ENTRY_ENCHANT_LIST;
	}
}