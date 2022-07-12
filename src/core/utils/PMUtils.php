<?php

declare(strict_types = 1);

namespace core\utils;

use pocketmine\permission\{
    DefaultPermissions,
    PermissionManager
};

final class PMUtils  {
    public static function getPocketMinePermissions() : array {
        $pmPerms = [];
        
        foreach(PermissionManager::getInstance()->getPermissions() as $permission) {
            if(strpos($permission->getName(), DefaultPermissions::ROOT_OPERATOR) !== false) {
                $pmPerms[] = $permission;
            }
        }
        return $pmPerms;
    }
}