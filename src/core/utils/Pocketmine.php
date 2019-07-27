<?php

declare(strict_types = 1);

namespace core\utils;

use pocketmine\permission\{
    DefaultPermissions,
    PermissionManager
};

use pocketmine\utils\{
	TextFormat,
	Utils
};

use pocketmine\command\CommandSender;

class PocketMine extends Utils {
    public const MAX_COORD = 30000000;
    public const MIN_COORD = -30000000;

    public static function getPocketMinePermissions() : array {
        $pmPerms = [];
        
        foreach(PermissionManager::getInstance()->getPermissions() as $permission) {
            if(strpos($permission->getName(), DefaultPermissions::ROOT) !== false) {
                $pmPerms[] = $permission;
            }
        }
        return $pmPerms;
    }

	public static function center($input) : string {
		$clear = TextFormat::clean($input);
		$lines = explode("\n", $clear);
		$max = max(array_map("strlen", $lines));
		$lines = explode("\n", $input);

		foreach($lines as $key => $line) {
			$lines[$key] = str_pad($line, $max + self::colorCount($line), " ", STR_PAD_BOTH);
		}
		return implode("\n", $lines);
	}

    public static function colorCount($input) : int {
        $colors = "abcdef0123456789lo";
        $count = 0;

        for($i = 0; $i < strlen($colors); $i++) {
            $count += substr_count($input, "§" . $colors{$i});
        }
        return $count;
    }

    public static function getRelativeDouble(float $original, CommandSender $sender, string $input, float $min = self::MIN_COORD, float $max = self::MAX_COORD) : float {
        if($input{0} === "~")  {
            $value = self::getDouble($sender, substr($input, 1));

            return $original + $value;
        }
        return self::getDouble($sender, $input, $min, $max);
    }

    public static function getDouble(CommandSender $sender, $value, float $min = self::MIN_COORD, float $max = self::MAX_COORD) : float {
        $i = (double) $value;

        if($i < $min) {
            $i = $min;
        } else if($i > $max) {
            $i = $max;
        }
        return $i;
    }

    public static function getInteger(CommandSender $sender, $value, int $min = self::MIN_COORD, int $max = self::MAX_COORD) : int {
        $i = (int) $value;

        if($i < $min) {
            $i = $min;
        } else if($i > $max) {
            $i = $max;
        }
        return $i;
    }
}