<?php

namespace core\broadcast\bossbar;

use core\Core;

use core\CorePlayer;

use pocketmine\entity\Entity;

use pocketmine\network\mcpe\protocol\{
    AddEntityPacket,
    BossEventPacket,
    UpdateAttributesPacket,
    RemoveEntityPacket,
    SetEntityDataPacket
};

use pocketmine\level\Level;

class BossBar implements Messages {
    private $core;

    private $run = 0;

    public $entityRuntimeId = null;

    public $int = 0;

    public function __construct(Core $core) {
        $this->core = $core;
    }

    public function getEntity() : int {
        return self::ENTITY;
    }

    public function getMode() : int {
        return self::MODE;
    }

    public function getHeadMessage() : string {
        return self::HEAD_MESSAGE;
    }

    public function getNotRegisteredMessage() : string {
        return self::NOT_REGISTERED_MESSAGE;
    }

    public function getChanging(string $key) {
        return self::CHANGING[$key];
    }

    public function getWorlds() : array {
        return self::WORLDS;
    }

    public function tick() {
        if($this->getChanging("enabled")) {
            if($this->run === $this->getChanging("time") * 20) {
                $this->core->getBroadcast()->getBossBar()->send();
            }
        }
    }

    public function add($players, string $title) : ?int {
        if(empty($players)) {
            return null;
        }
        $eid = Entity::$entityCount++;
        $packet = new AddEntityPacket();
        $packet->entityRuntimeId = $eid;
        $packet->type = $this->getEntity();
        $packet->metadata = [
            Entity::DATA_LEAD_HOLDER_EID => [
                Entity::DATA_TYPE_LONG, -1
            ],
            Entity::DATA_FLAGS => [
                Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_SILENT ^ 1 << Entity::DATA_FLAG_INVISIBLE ^ 1 << Entity::DATA_FLAG_NO_AI
            ],
            Entity::DATA_SCALE => [
                Entity::DATA_TYPE_FLOAT, 0
            ],
            Entity::DATA_NAMETAG => [
                Entity::DATA_TYPE_STRING, $title
            ],
            Entity::DATA_BOUNDING_BOX_WIDTH => [
                Entity::DATA_TYPE_FLOAT, 0
            ],
            Entity::DATA_BOUNDING_BOX_HEIGHT => [
                Entity::DATA_TYPE_FLOAT, 0
            ]
        ];

        foreach($players as $player) {
            if($player instanceof CorePlayer) {
                $pk = clone $packet;
                $pk->position = $player->getPosition()->asVector3()->subtract(0, 28);

                $player->sendDataPacket($pk);
            }
        }
        $bpk = new BossEventPacket();
        $bpk->bossEid = $eid;
        $bpk->eventType = BossEventPacket::TYPE_SHOW;
        $bpk->title = $title;
        $bpk->healthPercent = 1;
        $bpk->unknownShort = 0;
        $bpk->color = 0;
        $bpk->overlay = 0;
        $bpk->playerEid = 0;

        $this->core->getServer()->broadcastPacket($players, $bpk);
        return $eid;
    }

    public function setPercentage(int $percentage, int $eid, array $players = []) {
        if(empty($players)) {
            $players = $this->core->getServer()->getOnlinePlayers();
        }
        if(!count($players) > 0) {
            return;
        }
        $upk = new UpdateAttributesPacket();
        $upk->entries[] = new Values(1, 600, max(1, min([$percentage, 100])) / 100 * 600, 'minecraft:health');
        $upk->entityRuntimeId = $eid;

        $this->core->getServer()->broadcastPacket($players, $upk);

        $bpk = new BossEventPacket();
        $bpk->bossEid = $eid;
        $bpk->eventType = BossEventPacket::TYPE_SHOW;
        $bpk->title = "";
        $bpk->healthPercent = $percentage / 100;
        $bpk->unknownShort = 0;
        $bpk->color = 0;
        $bpk->overlay = 0;
        $bpk->playerEid = 0;

        $this->core->getServer()->broadcastPacket($players, $bpk);
    }

    public function remove($players, int $eid) {
        if(empty($players)) {
            return;
        }
        $packet = new RemoveEntityPacket();
        $packet->entityUniqueId = $eid;

        $this->core->getServer()->broadcastPacket($players, $packet);
    }

    public function send() {
        if($this->entityRuntimeId === null) {
            return;
        }
        $this->int++;

        $worlds = $this->getWorlds();

        foreach($worlds as $world) {
            if($world instanceof Level) {
                foreach($world->getPlayers() as $player) {
                    if($player instanceof CorePlayer) {
                        $this->setTitle($player->getBossBarText(), $this->entityRuntimeId, [$player]);
                    }
                }
            }
        }
    }

    public function getWorld() : ?array {
        $mode = $this->getMode();
        $worldNames = $this->getWorlds();
        $worlds = [];

        switch($mode) {
            case 0:
                $worlds = $this->core->getServer()->getLevels();
            break;
            case 1:
                foreach($worldNames as $name) {
                    if(is_null($level = $this->core->getServer()->getLevelByName($name))) {
                        $this->core->getServer()->getLogger()->error($this->core->getPrefix() . "World provided in BossBar config does not exist");
                    } else {
                        $worlds[] = $level;
                    }
                }
            break;
            case 2:
                $worlds = $this->core->getServer()->getLevels();

                foreach($worlds as $world) {
                    if($world instanceof Level) {
                        if(!in_array(strtolower($world->getName()), $worldNames)) {
                            $worlds[] = $world;
                        }
                    }
                }
            break;
        }
        return $worlds;
    }

    public function setTitle(string $title, int $eid, array $players = []) {
        if(!count($this->core->getServer()->getOnlinePlayers()) > 0) {
            return;
        }
        $npk = new SetEntityDataPacket();
        $npk->metadata = [
            Entity::DATA_NAMETAG => [
                Entity::DATA_TYPE_STRING, $title
            ]
        ];
        $npk->entityRuntimeId = $eid;

        $this->core->getServer()->getInstance()->broadcastPacket($players, $npk);

        $bpk = new BossEventPacket();
        $bpk->bossEid = $eid;
        $bpk->eventType = BossEventPacket::TYPE_SHOW;
        $bpk->title = $title;
        $bpk->healthPercent = 1;
        $bpk->unknownShort = 0;
        $bpk->color = 0;
        $bpk->overlay = 0;
        $bpk->playerEid = 0;

        $this->core->getServer()->broadcastPacket($players, $bpk);
    }
}