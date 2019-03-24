<?php

namespace core\mcpe\task;

use core\Core;

use core\CorePlayer;

use core\utils\Level;

use pocketmine\scheduler\Task;

use pocketmine\Player;

use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\{
    ChangeDimensionPacket,
    PlayStatusPacket
};

class DelayedCrossDimensionTeleport extends Task {
    protected $player;

    protected $dimension;

    protected $position;

    protected $respawn;

    public function __construct(Player $player, int $dimension, Vector3 $position, bool $respawn = false) {
        $this->player = $player;
        $this->dimension = $dimension;
        $this->position = $position;
        $this->respawn = $respawn;
    }

    public function onRun(int $currentTick) {
        if($this->player instanceof CorePlayer) {
            if(Level::isDelayedTeleportCancellable($this->player, $this->dimension)) {
                unset(Core::getInstance()->getMCPE()->onPortal[$this->player->getId()]);
                return false;
            }
            $packet = new ChangeDimensionPacket();
            $packet->dimension = $this->dimension;
            $packet->position = $this->position;
            $packet->respawn = $this->respawn;

            $this->player->dataPacket($packet);
            $this->player->sendPlayStatus(PlayStatusPacket::PLAYER_SPAWN);
            $this->player->teleport($this->position);
            unset(Core::getInstance()->getMCPE()->onPortal[$this->player->getId()]);
        }
        return true;
    }
}