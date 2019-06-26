<?php

declare(strict_types = 1);

namespace core\stats\rank;

use pocketmine\permission\PermissionManager;

abstract class Rank {
    const DEFAULT = 0;
    const FREE = 1;
    const PAID = 2;
    const STAFF = 3;

    private $name = "";

    private $freePrice = 0, $paidPrice = 0;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public final function getName() : string {
        return $this->name;
    }

    public abstract function getFormat() : string;

    public abstract function getChatFormat() : string;

    public abstract function getNameTagFormat() : string;

    public abstract function getPermissions() : array;

    public abstract function getInheritance() : ?Rank;

    public abstract function getValue() : int;

    public abstract function getChatTime() : float;

    public function setFreePrice(int $freePrice) {
        if($this->getValue() === self::FREE) {
            $this->freePrice = $freePrice;
        } else {
            throw new \Exception("Rank is not Free");
        }
    }

    public final function getFreePrice() : int {
        return $this->freePrice;
    }

    public function setPaidPrice(int $paidPrice) {
        if($this->getValue() === self::PAID) {
            $this->paidPrice = $paidPrice;
        } else {
            throw new \Exception("Rank is not Paid");
        }
    }

    public final function getPaidPrice() : int {
        return $this->paidPrice;
    }

    public function getInheritedPermissions() : array {
        $permissions = [];

        $parentRank = $this->getInheritance();

        foreach($parentRank->getInheritedPermissions() as $parentPermission) {
                $permissions[] = $parentPermission;
        }
        foreach($this->getPermissions() as $permission) {
            $permissions[] = $permission;
        }
        if(($key = array_search("-*", $permissions)) !== false) {
            unset($permissions[$key]);

            foreach(PermissionManager::getInstance()->getPermissions() as $permission) {
                $permissions[] = "-" . $permission->getName();
            }
        }
        if(($key = array_search("*", $permissions)) !== false) {
            unset($permissions[$key]);

            foreach(PermissionManager::getInstance()->getPermissions() as $permission) {
                $permissions[] = $permission->getName();
            }
        }
        return array_unique($permissions, SORT_STRING);
    }

    public function getRecursiveInheritance() {
        $ranks = [];

        $parentRank = $this->getInheritance();

        foreach($parentRank->getRecursiveInheritance() as $rank) {
            $ranks[] = $rank;
        }
        return array_unique($ranks);
    }
}