<?php

declare(strict_types = 1);

namespace core\player\rank;

class Rank implements RankIds {
	private int $freePrice;
	private int $paidPrice;

    public function __construct(
		private string $name,
		private int $identifier,
		private string $color,
		private string $format,
		private string $chatFormat,
		private string $nameTagFormat,
		private array $permissions,
		private ?Rank $inheritance,
		private int $value,
		private float $chatTime
	) {
        assert($value === self::DONATOR_RANK || $value === self::STAFF_RANK);
    }

    public final function getName() : string {
        return $this->name;
    }

    public final function getIdentifier() : int {
    	return $this->identifier;
	}

	public final function getColor() : string {
    	return $this->color;
	}

    public final function getFormat() : string {
    	return $this->format;
	}

    public final function getChatFormat() : string {
    	return $this->chatFormat;
	}

    public final function getNameTagFormat() : string {
    	return $this->nameTagFormat;
	}

    public final function getPermissions() : array {
    	return $this->permissions;
	}

    public final function getInheritance() : ?Rank {
    	return $this->inheritance;
	}

    public final function getValue() : int {
    	return $this->value;
	}

    public final function getChatTime() : float {
    	return $this->chatTime;
	}

    public final function getFreePrice() : int {
        return $this->freePrice;
    }

    public final function setFreePrice(int $freePrice) {
    	$this->freePrice = $freePrice;
    }

    public final function getPaidPrice() : int {
        return $this->paidPrice;
    }

    public final function setPaidPrice(int $paidPrice) {
    	$this->paidPrice = $paidPrice;
    }
}