<?php

namespace core\utils;

use pocketmine\item\enchantment\{
    EnchantmentInstance,
    Enchantment
};

use pocketmine\item\{
    TieredTool,
    ItemIds,
    Tool,
    FishingRod,
    Armor,
    Book,
    Sword,
    Bow,
    Durable
};

use pocketmine\utils\Color;

class Item extends \pocketmine\item\Item {
    public const DYE_BLACK = 0;
    public const DYE_RED = 1;
    public const DYE_GREEN = 2;
    public const DYE_BROWN = 3;
    public const DYE_BLUE = 4;
    public const DYE_PURPLE = 5;
    public const DYE_CYAN = 6;
    public const DYE_LIGHT_GRAY = 7, DYE_SILVER = 7;
    public const DYE_GRAY = 8;
    public const DYE_PINK = 9;
    public const DYE_LIME = 10;
    public const DYE_YELLOW = 11;
    public const DYE_LIGHT_BLUE = 12;
    public const DYE_MAGENTA = 13;
    public const DYE_ORANGE = 14;
    public const DYE_WHITE = 15;

    public const HELMET = [
        Item::LEATHER_HELMET,
        Item::CHAIN_HELMET,
        Item::IRON_HELMET,
        Item::GOLD_HELMET,
        Item::DIAMOND_HELMET,
    ],
        CHESTPLATE = [
        Item::LEATHER_CHESTPLATE,
        Item::CHAIN_CHESTPLATE,
        Item::IRON_CHESTPLATE,
        Item::GOLD_CHESTPLATE,
        Item::DIAMOND_CHESTPLATE,
        Item::ELYTRA,
    ],
        LEGGINGS = [
        Item::LEATHER_LEGGINGS,
        Item::CHAIN_LEGGINGS,
        Item::IRON_LEGGINGS,
        Item::GOLD_LEGGINGS,
        Item::DIAMOND_LEGGINGS,
    ],
        BOOTS = [
        Item::LEATHER_BOOTS,
        Item::CHAIN_BOOTS,
        Item::IRON_BOOTS,
        Item::GOLD_BOOTS,
        Item::DIAMOND_BOOTS,
    ];

    public const TYPE_HELMET = "HELMET", TYPE_CHESTPLATE = "CHESTPLATE", TYPE_LEGGINGS = "LEGGINGS", TYPE_BOOTS = "BOOTS", TYPE_NULL = "NIL";

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
    
    public static function parseItem(string $string) : ?\pocketmine\item\Item {
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
    
    public static function getRandomItems(array $items) : \pocketmine\item\Item {
        $items = self::parseItems($items);
        return $items[array_rand($items)];
    }
	
	public function getRandomEnchantment(int $experienceLevel, Item $item): EnchantmentInstance {
		$return = new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::SHARPNESS));
		
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
					$enchantability = 14;
				break;
			}
		} else if($item instanceof Tool) {
			$enchantability = 14;
		} else if($item instanceof FishingRod) {
			$enchantability = 14;
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
				$enchantability = 9;
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
		
		$enchantments = array_filter($enchantments, function($id) {
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
		return $return;
	}

    public static function getDyeColor(int $id) : Color {
        switch($id) {
            case self::DYE_BLACK:
                return new Color(30, 27, 27);
            case self::DYE_RED:
                return new Color(179, 49, 44);
            case self::DYE_GREEN:
                return new Color(61, 81, 26);
            case self::DYE_BROWN:
                return new Color(81, 48, 26);
            case self::DYE_BLUE:
                return new Color(37, 49, 146);
            case self::DYE_PURPLE:
                return new Color(123, 47, 190);
            case self::DYE_CYAN:
                return new Color(40, 118, 151);
            case self::DYE_SILVER:
                return new Color(153, 153, 153);
            case self::DYE_GRAY:
                return new Color(67, 67, 67);
            case self::DYE_PINK:
                return new Color(216, 129, 152);
            case self::DYE_LIME:
                return new Color(65, 205, 52);
            case self::DYE_YELLOW:
                return new Color(222, 207, 42);
            case self::DYE_LIGHT_BLUE:
                return new Color(102, 137, 211);
            case self::DYE_MAGENTA:
                return new Color(195, 84, 205);
            case self::DYE_ORANGE:
                return new Color(235, 136, 68);
            case self::DYE_WHITE:
                return new Color(240, 240, 240);
        }
        return new Color(0, 0, 0);
    }

    public static function getType(Item $armor) : string {
        if(in_array($armor->getId(), $type = self::HELMET)) {
            return self::TYPE_HELMET;
        }
        if(in_array($armor->getId(), self::CHESTPLATE)) {
            return self::TYPE_CHESTPLATE;
        }
        if(in_array($armor->getId(), self::LEGGINGS)) {
            return self::TYPE_LEGGINGS;
        }
        if(in_array($armor->getId(), self::BOOTS)) {
            return self::TYPE_BOOTS;
        }
        return self::TYPE_NULL;
    }
}