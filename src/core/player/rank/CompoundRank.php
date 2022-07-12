<?php

declare(strict_types = 1);

namespace core\player\rank;

use core\player\CorePlayer;
use core\player\PlayerManager;

use pocketmine\utils\TextFormat;

// This is made up of two half ranks, a donator rank and a staff rank
// The id is 16 bits. First 8 are staff rank, then next 8 are donator 
class CompoundRank {
    public const DONATOR_RANK_MASK = 0b0000000011111111;

    public const STAFF_RANK_MASK   = 0b1111111100000000;
    
    protected int $identifier;

    protected string $chatFormat;
	
	protected int $chatTime;

    protected string $basicTagFormat;

    /** @var string[] */
    protected array $permissions;

    public function __construct(protected ?Rank $donatorRank, protected ?Rank $staffRank) {
        assert($donatorRank !== null || $staffRank !== null); 
        assert($donatorRank === null || $donatorRank->getValue() === Rank::DONATOR_RANK);
        assert($staffRank === null || $staffRank->getValue() === Rank::STAFF_RANK);

        $donatorId = $donatorRank !== null ? $donatorRank->getIdentifier() : 0;
        $staffId = $staffRank !== null ? $staffRank->getIdentifier() : 0;
        $this->identifier = ($staffId << 8) + $donatorId;
	
        $this->chatFormat = "";
		$this->basicTagFormat = "";
		$this->chatTime = 0;

        if ($staffRank !== null) {
			$this->chatTime += $staffRank->getChatTime();
        	$this->chatFormat .= $staffRank->getChatFormat();
			$this->basicTagFormat .= str_replace("{COLOR}", $staffRank->getColor(), $staffRank->getNameTagFormat());
		}
        if ($donatorRank !== null) {
			$this->chatTime += $donatorRank->getChatTime();
        	$this->chatFormat .= $donatorRank->getChatFormat();
			$this->basicTagFormat .= str_replace("{COLOR}", $donatorRank->getColor(), $donatorRank->getNameTagFormat());
		}
        
        $donatorPerms = $donatorRank !== null ? $donatorRank->getPermissions() : DefaultRank::DEFAULT_PERMISSIONS;
        $staffPerms = $staffRank !== null ? $staffRank->getPermissions() : DefaultRank::DEFAULT_PERMISSIONS;
        $this->permissions = array_merge($donatorPerms, $staffPerms);
    }

    public function getDonatorRank() : ?Rank {
        return $this->donatorRank;
    }

    public function getStaffRank() : ?Rank {
        return $this->staffRank;
    }

    public function getName() : string {
        return $this->staffRank !== null ? $this->staffRank->getName() : $this->donatorRank->getName();
    }

    public function getFormat() : string {
        return $this->staffRank !== null ? $this->staffRank->getFormat() : $this->donatorRank->getFormat();
    }

    public function getIdentifier() : int {
        return $this->identifier;
    }
    
    public function getChatFormatFor(CorePlayer $from, string $message, array $args = []) : string {
		$message = TextFormat::clean($message);
        $format = str_replace(["{DISPLAY_NAME}", "{MESSAGE}"], [$from->getNameTag(), $message], $this->chatFormat);
        return $format;
    }
    
    public function getNameTagFormatFor(CorePlayer $player) : string {
        $tagFormat = $this->basicTagFormat;
		str_replace("{DISPLAY_NAME}", $player->getName(), $tagFormat);
        return $tagFormat;
    }
    
    public function getInheritance() : Rank|DefaultRank {
		if($this->getStaffRank() !== null) {
			if($this->getStaffRank()->getIdentifier() == 1) {
				return PlayerManager::getInstance()->getRank(6);
			}
			return PlayerManager::getInstance()->getRank($this->getStaffRank()->getIdentifier() - 1);
		} else {
			if($this->getIdentifier() == 0 or $this->getIdentifier() == 1) {
				return PlayerManager::getInstance()->getDefaultRank();
			}
			return PlayerManager::getInstance()->getRank($this->getDonatorRank()->getIdentifier() - 1);
		}
	}
	
	public function getChatTime() : float {
    	return $this->chatTime;
	}

	public function getPermissions() : array {
		$permissions = [];

		$parentRank = $this->getInheritance();

		foreach($parentRank->getPermissions() as $parentPermission) {
			$permissions[] = $parentPermission;
		}
		foreach($this->getPermissions() as $permission) {
			$permissions[] = $permission;
		}
		return array_unique($permissions, SORT_STRING);
	}

    public function __toString() : string {
        return (string) $this->staffRank->getName() . "+" . (string)$this->donatorRank->getName();
    }
}
