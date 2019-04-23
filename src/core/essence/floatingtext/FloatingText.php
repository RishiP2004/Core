<?php

declare(strict_types = 1);

namespace core\essence\floatingtext;

use core\Core;
use core\CorePlayer;

use pocketmine\level\Position;
use pocketmine\level\particle\FloatingTextParticle;

abstract class FloatingText {
    private $name = "";

    public function __construct(string $name) {
        $this->name = $name;
    }

    public final function getName() : string {
        return $this->name;
    }

    public abstract function getPosition() : Position;

    public abstract function getText() : string;

    public function spawnTo(CorePlayer $player) {
        $text = str_replace([
            "{TOTAL_ONLINE_PLAYERS}",
            "{TOTAL_MAX_SLOTS}"
        ], [
            count(Core::getInstance()->getNetwork()->getTotalOnlinePlayers()),
            Core::getInstance()->getNetwork()->getTotalMaxSlots()
        ], $this->getText());
        $player->getLevel()->addParticle(new FloatingTextParticle($this->getPosition()->asVector3()->add(0.5, 0, 0.5), "", $text));
    }
}