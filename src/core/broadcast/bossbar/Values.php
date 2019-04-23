<?php

declare(strict_types = 1);

namespace core\broadcast\bossbar;

use pocketmine\entity\Attribute;

class Values extends Attribute {
    protected $minimum = 0.0, $maximum = 0.0, $value = 0.0;

    protected $name = "";

    public function __construct(float $minimum, float $maximum, float $value, string $name) {
        $this->minimum = $minimum;
        $this->maximum = $maximum;
        $this->value = $value;
        $this->name = $name;
    }

    public function getMinValue() : float {
        return $this->minimum;
    }

    public function getMaxValue() : float {
        return $this->maximum;
    }

    public function getValue() : float {
        return $this->value;
    }

    public function getName() : string {
        return $this->name;
    }

    public function getDefaultValue() : float {
        return $this->minimum;
    }
}