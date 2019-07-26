<?php

declare(strict_types = 1);

namespace core\essence\npc;

use core\Core;
use core\CorePlayer;
use core\essence\EssenceData;
use pocketmine\level\Position;

use pocketmine\entity\{
    Skin,
    Entity
};

use pocketmine\item\Item;

use pocketmine\utils\UUID;

use pocketmine\network\mcpe\protocol\{
    AddPlayerPacket,
    MobArmorEquipmentPacket,
	PlayerListPacket,
    PlayerSkinPacket,
    RemoveActorPacket,
    MovePlayerPacket,
    MoveActorAbsolutePacket
};

use pocketmine\network\mcpe\protocol\types\PlayerListEntry;

use pocketmine\command\ConsoleCommandSender;

abstract class NPC {
    private $name = "";

    private $spawnedTo = [];

    private $int = 0;

	private $id;
	private $uuid;

    public function __construct(string $name) {
        $this->name = $name;
		$this->id = Entity::$entityCount++;
		$this->uuid = UUID::fromRandom();
    }

    public final function getName() : string {
        return $this->name;
    }

    public abstract function getPosition() : Position;

    public abstract function getSize() : float;

    public abstract function getNameTag() : string;

    public abstract function getSkin() : Skin;

    public abstract function getHeldItem() : Item;

    public abstract function getArmor() : array;

    public abstract function rotate() : bool;

    public abstract function getMovement() : array;

	public abstract function getMoveTime() : int;
	
    public abstract function getCommands() : array;

    public abstract function getMessages() : array;

    public function getUUID() : UUID {
        return $this->uuid;
    }

    public function getEID() : int {
        return $this->id;
    }

    public function isSpawnedTo(CorePlayer $player) {
        return isset($this->spawnedTo[$player->getName()]);
    }

    public function spawnTo(CorePlayer $player) {
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_ADD;
		$pk->entries = [PlayerListEntry::createAdditionEntry($this->getUUID(), $this->getEID(), $this->getName(), $this->getSkin())];
		$player->dataPacket($pk);

        $this->spawnedTo[$player->getName()] = true;
        $packet = new AddPlayerPacket();
        $packet->uuid = $this->getUUID();
        $nameTag = $this->getNameTag();

        if($this->getName() === "Factions" or $this->getName() === "Lobby") {
            $server = Core::getInstance()->getNetwork()->getServer($this->getName());

            if(!$server->isOnline()) {
                $onlinePlayers = "";
                $maxSlots = "";
                $online = "No";

                if($server->isWhitelisted()) {
                    $online = "Whitelisted";
                }
            } else {
                $onlinePlayers = "Players: " . count($server->getOnlinePlayers()) . "/";
                $maxSlots = $server->getMaxSlots();
                $online = "Yes";
            }
            $nameTag = str_replace([
                "{ONLINE_PLAYERS}",
                "{MAX_SLOTS}",
                "{ONLINE}"
            ], [
                $onlinePlayers,
                $maxSlots,
                $online
            ], $this->getNameTag());
        }
        $packet->username = $nameTag;
        $packet->entityRuntimeId = $this->getEID();
        $packet->position = $this->getPosition()->asVector3();
        $packet->pitch = $packet->headYaw = $packet->yaw = 0;
        $packet->item = $this->getHeldItem();
        $flags = 0;
        $flags |= 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG;
        $flags |= 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG;
        $flags |= 1 << Entity::DATA_FLAG_IMMOBILE;
        $packet->metadata = [
            Entity::DATA_FLAGS => [
                Entity::DATA_TYPE_LONG,
                $flags
            ],
            Entity::DATA_NAMETAG => [
                Entity::DATA_TYPE_STRING,
                $this->getNameTag()
            ],
            Entity::DATA_LEAD_HOLDER_EID => [
                Entity::DATA_TYPE_LONG,
                -1
            ],
            Entity::DATA_SCALE => [
                Entity::DATA_TYPE_FLOAT,
                $this->getSize()
            ],
        ];

        $player->sendDataPacket($packet);

        $maep = new MobArmorEquipmentPacket();
        $maep->entityRuntimeId = $this->getEID();

        $helmet = 0;
        $chestplate = 0;
        $leggings = 0;
        $boots = 0;

        if(!empty($this->getArmor()["helmet"])) {
            $helmet = $this->getArmor()["helmet"];
        }
        if(!empty($this->getArmor()["chestplate"])) {
            $chestplate = $this->getArmor()["chestplate"];
        }
        if(!empty($this->getArmor()["leggings"])) {
            $leggings = $this->getArmor()["leggings"];
        }
        if(!empty($this->getArmor()["boots"])) {
            $boots = $this->getArmor()["boots"];
        }
        $maep->head = Item::get($helmet);
		$maep->chest = Item::get($chestplate);
		$maep->legs = Item::get($leggings);
		$maep->feet = Item::get($boots);

        $player->sendDataPacket($maep);

		$psp = new PlayerSkinPacket();
		$psp->uuid = $this->getUUID();
		$psp->skin = $this->getSkin();
		$player->sendDataPacket($psp);

		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_REMOVE;
		$pk->entries = [PlayerListEntry::createRemovalEntry($this->getUUID())];
		$player->dataPacket($pk);
    }

    public function despawnFrom(CorePlayer $player) {
        unset($this->spawnedTo[$player->getName()]);

        $packet = new RemoveActorPacket();
        $packet->entityUniqueId = $this->getEID();

        $player->sendDataPacket($packet);
    }

    public function rotateTo(CorePlayer $player) {
        if($this->rotate()) {
            $NPCPos = $this->getPosition()->asVector3();

            if($this->isSpawnedTo($player) && $player->distance($NPCPos) <= EssenceData::MAX_DISTANCE) {
                $x = $NPCPos->x - $player->getX();
                $y = $NPCPos->y - $player->getY();
                $z = $NPCPos->z - $player->getZ();
                $yaw = asin($x / sqrt($x * $x + $z * $z)) / 3.14 * 180;
                $pitch = round(asin($y / sqrt($x * $x + $z * $z + $y * $y)) / 3.14 * 180);

                if($z > 0) {
                    $yaw = -$yaw + 180;
                }
                $packet = new MovePlayerPacket();
                $packet->entityRuntimeId = $this->getEID();
                $packet->position = $this->getPosition()->asVector3()->add(0, 1.62);
                $packet->yaw = $yaw;
                $packet->pitch = $pitch;
                $packet->headYaw = $yaw;
                $packet->mode = 0;
                $packet->onGround = true;

                $player->sendDataPacket($packet);
            }
        }
    }

    public function move(CorePlayer $player) {
        if(!empty($this->getMovement())) {
			$this->int++;
			
            $packet = new MoveActorAbsolutePacket();
            $packet->entityRuntimeId = $this->getEID();
            $array = explode(", ", $this->getMovement()[$this->int]);
            $position = new Position($array[0], $array[1], $array[2], Core::getInstance()->getServer()->getLevelByName($array[3]));
            $packet->position = $position;
            $packet->xRot = 0;
            $packet->yRot = 0;
            $packet->zRot = 0;

            $player->sendDataPacket($packet);

            if(end($array)) {
                $this->int--;
            }
        }
    }

    public function onInteract(CorePlayer $player) {
        if($this->getMessages() !== []) {
            foreach($this->getMessages() as $message) {
                $message = str_replace([
                    "{PLAYER}",
                ], [
                    $player->getName(),
                ], $message);

                $player->sendMessage($message);
            }
        }
        if($this->getCommands() !== []) {
            foreach($this->getCommands() as $command) {
                $command = str_replace([
                    "{PLAYER}",
                ], [
                    $player->getName(),
                ], $command);

                Core::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
            }
        }
    }
}
