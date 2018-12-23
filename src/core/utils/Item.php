<?php

namespace core\utils;

use pocketmine\item\enchantment\{
    EnchantmentInstance,
    Enchantment
};

class Item extends \pocketmine\item\Item {
    public static function parseItems($array) : array {
        $items = [];
        
        foreach($array as $item) {
            $item = self::parseItem($item);
            
            if($item instanceof Item) {
                $items[] = $item;
            }
        }
        return $items;
    }
    
    public static function parseItem(string $string) {
        $array = explode(",", $string);
        
        foreach($array as $key => $value) {
            $array[$key] = $value;
        }
        if(isset($array[1])) {
            $item = \pocketmine\item\Item::get($array[0], 0, $array[1]);
            
            if(isset($array[3])) {
                $item->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment($array[2]), $array[3]));
            }
            return $item;
        }
        return null;
    }
    
    public static function getRandomItems(array $items) {
        $items = self::parseItems($items);
        return $items[array_rand($items)];
    }
	
	public function getRandomEnchantment(int $experienceLevel, Item $item): EnchantmentInstance {
		$return = new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::SHARPNESS)); // default
		
		if($experienceLevel <= 8) {
			$bookShelves = 0;
		} else if($experienceLevel <= 9) {
			$bookShelves = 1;
		} else if($experienceLevel <= 11) {
			$bookShelves = 2;
		} else if($experienceLevel <= 12) {
			$bookShelves = 3;
		} else if($experienceLevel <= 14) {
			$bookShelves = 4;
		} else if($experienceLevel <= 15) {
			$bookShelves = 5;
		} else if($experienceLevel <= 17) {
			$bookShelves = 6;
		} else if($experienceLevel <= 18) {
			$bookShelves = 7;
		} else if($experienceLevel <= 20) {
			$bookShelves = 8;
		} else if($experienceLevel <= 21) {
			$bookShelves = 9;
		} else if($experienceLevel <= 23) {
			$bookShelves = 10;
		} else if($experienceLevel <= 24) {
			$bookShelves = 11;
		} else if($experienceLevel <= 26) {
			$bookShelves = 12;
		} else if($experienceLevel <= 27) {
			$bookShelves = 13;
		} else if($experienceLevel <= 29) {
			$bookShelves = 14;
		} else if($experienceLevel <= 30) {
			$bookShelves = 15;
		} else {
			$bookShelves = 15;
		}
		if($item instanceof TieredTool) {
			switch($item->getTier()) {
				case TieredTool::TIER_WOODEN:
					$enchantability = 15;
				break;
				case TieredTool::TIER_STONE:
					$enchantability = 5;
				break;
				case TieredTool::TIER_IRON:
					$enchantability = 14;
				break;
				case TieredTool::TIER_GOLD:
					$enchantability = 22;
				break;
				case TieredTool::TIER_DIAMOND:
					$enchantability = 10;
				break;
				default:
					$enchantability = 14; // default to iron
				break;
			}
		} else if($item instanceof Tool) {
			$enchantability = 14; // default to iron
		} else if($item instanceof FishingRod) {
			$enchantability = 14; // default to iron
		} else if($item instanceof Armor) {
			if($item->getId() === ItemIds::LEATHER_BOOTS or $item->getId() === ItemIds::LEATHER_LEGGINGS or $item->getId() === ItemIds::LEATHER_CHESTPLATE or $item->getId() === ItemIds::LEATHER_HELMET) {
				$enchantability = 15;
			} else if($item->getId() === ItemIds::CHAIN_BOOTS or $item->getId() === ItemIds::CHAIN_LEGGINGS or $item->getId() === ItemIds::CHAIN_CHESTPLATE or $item->getId() === ItemIds::CHAIN_HELMET) {
				$enchantability = 12;
			} else if($item->getId() === ItemIds::IRON_BOOTS or $item->getId() === ItemIds::IRON_LEGGINGS or $item->getId() === ItemIds::IRON_CHESTPLATE or $item->getId() === ItemIds::IRON_HELMET) {
				$enchantability = 9;
			} else if($item->getId() === ItemIds::GOLD_BOOTS or $item->getId() === ItemIds::GOLD_LEGGINGS or $item->getId() === ItemIds::GOLD_CHESTPLATE or $item->getId() === ItemIds::GOLD_HELMET) {
				$enchantability = 25;
			} else if($item->getId() === ItemIds::DIAMOND_BOOTS or $item->getId() === ItemIds::DIAMOND_LEGGINGS or $item->getId() === ItemIds::DIAMOND_CHESTPLATE or $item->getId() === ItemIds::DIAMOND_HELMET) {
				$enchantability = 10;
			} else {
				$enchantability = 9; // default to iron
			}
		} else if($item instanceof Book) {
			$enchantability = 1;
		} else {
			throw new \RuntimeException("Cannot enchant that item");
		}
		$baseEnchantmentLevel = (mt_rand(1, 8) + floor($bookShelves / 2) + mt_rand(0, $bookShelves));
		$topSlotEnchantmentLevel = max($baseEnchantmentLevel / 3, 1);
		$middleSlotEnchantmentLevel = ($baseEnchantmentLevel * 2) / 3 + 1;
		$bottomSlotEnchantmentLevel = max($baseEnchantmentLevel, $bookShelves * 2);
		$modifiedEnchantmentLevel = $baseEnchantmentLevel + mt_rand(0, $enchantability / 4) + mt_rand(0, $enchantability / 4) + 1;
		$randomEnchantability = 1 + mt_rand(($enchantability / 2) / 2 + 1, (($enchantability / 2) / 2 + 1) - 1) + mt_rand(($enchantability / 2) / 2 + 1, (($enchantability / 2) / 2 + 1) - 1);
		
		switch(mt_rand(1, 3)) {
			case 1:
				$chosenEnchantmentLevel = $topSlotEnchantmentLevel;
			break;
			case 2:
				$chosenEnchantmentLevel = $middleSlotEnchantmentLevel;
			break;
			case 3:
				$chosenEnchantmentLevel = $bottomSlotEnchantmentLevel;
			break;
			default:
		}
		$totalLevel = $chosenEnchantmentLevel + $randomEnchantability;
		$randomBonus = 1 + (lcg_value() + lcg_value() - 1) * 0.15;
		$finalLevel = (int)($totalLevel * $randomBonus + 0.5);
		
		if($finalLevel < 1) {
			$finalLevel = 1;
		}
		$enchantments = [];
		
		if($item instanceof Sword or $item instanceof Book) {
			$enchantments[Enchantment::SHARPNESS] = 10;
			$enchantments[Enchantment::BANE_OF_ARTHROPODS] = 5;
			$enchantments[Enchantment::KNOCKBACK] = 5;
			$enchantments[Enchantment::SMITE] = 5;
			$enchantments[Enchantment::FIRE_ASPECT] = 2;
			$enchantments[Enchantment::LOOTING] = 2;
		}
		if(($item instanceof Tool and !$item instanceof Sword) or $item instanceof Book) {
			$enchantments[Enchantment::EFFICIENCY] = 10;
			$enchantments[Enchantment::FORTUNE] = 2;
			$enchantments[Enchantment::SILK_TOUCH] = 1;
		}
		if($item instanceof Armor or $item instanceof Book) {
			$enchantments[Enchantment::PROTECTION] = 10;
			$enchantments[Enchantment::BINDING] = 1;
			$enchantments[Enchantment::FIRE_PROTECTION] = 5;
			$enchantments[Enchantment::PROJECTILE_PROTECTION] = 5;
			$enchantments[Enchantment::BLAST_PROTECTION] = 2;
			$enchantments[Enchantment::THORNS] = 1;
			
			if($item->getId() === ItemIds::LEATHER_BOOTS or $item->getId() === ItemIds::CHAIN_BOOTS or $item->getId() === ItemIds::IRON_BOOTS or $item->getId() === ItemIds::GOLD_BOOTS or $item->getId() === ItemIds::DIAMOND_BOOTS) {
				$enchantments[Enchantment::FEATHER_FALLING] = 5;
				$enchantments[Enchantment::FROST_WALKER] = 2;
				$enchantments[Enchantment::DEPTH_STRIDER] = 2;
			}elseif($item->getId() === ItemIds::LEATHER_HELMET or $item->getId() === ItemIds::CHAIN_HELMET or $item->getId() === ItemIds::IRON_HELMET or $item->getId() === ItemIds::GOLD_HELMET or $item->getId() === ItemIds::DIAMOND_HELMET) {
				$enchantments[Enchantment::RESPIRATION] = 2;
				$enchantments[Enchantment::AQUA_AFFINITY] = 2;
			}
		}
		if($item instanceof Bow or $item instanceof Book) {
			$enchantments[Enchantment::POWER] = 10;
			$enchantments[Enchantment::FLAME] = 2;
			$enchantments[Enchantment::PUNCH] = 2;
			$enchantments[Enchantment::INFINITY] = 1;
		}
		if($item instanceof FishingRod or $item instanceof Book) {
			$enchantments[Enchantment::LUCK_OF_THE_SEA] = 2;
			$enchantments[Enchantment::LURE] = 2;
		}
		if($item instanceof Durable or $item instanceof Book) {
			$enchantments[Enchantment::UNBREAKING] = 5;
			$enchantments[Enchantment::MENDING] = 2;
		}
		$enchantments[Enchantment::VANISHING] = 1;
		
		$enchantments = array_filter($enchantments, function($id) { // TODO: remove when all enchantments implemented
			return Enchantment::getEnchantment($id) !== null;
		}, ARRAY_FILTER_USE_KEY); // filter unregistered enchantments
		$totalWeight = 0;
		
		foreach($enchantments as $weight) {
			$totalWeight += $weight;
		}
		$random = mt_rand(1, $totalWeight);
		
		foreach($enchantments as $id => $weight) {
			if($random - $weight <= 0) {
				$return = new EnchantmentInstance(Enchantment::getEnchantment($id));
				
				$return->setLevel($finalLevel);
				break;
			}
		}
		// TODO: filter valid enchantments based on $modifiedEnchantmentLevel https://minecraft.gamepedia.com/Enchanting/Levels
		return $return;
	}
}