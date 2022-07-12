<?php

declare(strict_types = 1);

namespace core\essence\npc;

use core\Core;

use core\player\CorePlayer;

use core\essence\EssenceData;

use core\network\NetworkManager;

use pocketmine\item\{
	ItemFactory,
	ItemIds
};

use pocketmine\network\mcpe\convert\{
	SkinAdapterSingleton,
	TypeConverter
};

use pocketmine\network\mcpe\protocol\types\GameMode;

use pocketmine\Server;

use pocketmine\entity\{
	EntitySizeInfo,
	Location,
	Skin,
	Entity
};

use pocketmine\item\Item;

use pocketmine\network\mcpe\protocol\{
	AddPlayerPacket,
	AdventureSettingsPacket,
	MobArmorEquipmentPacket,
	PlayerListPacket,
	RemoveActorPacket,
	MovePlayerPacket,
	MoveActorAbsolutePacket,
	SetActorDataPacket,
	AddActorPacket};
use pocketmine\network\mcpe\protocol\types\{
	DeviceOS,
	entity\EntityMetadataCollection,
	entity\EntityMetadataFlags,
	entity\EntityMetadataProperties,
	entity\MetadataProperty,
	entity\StringMetadataProperty,
	PlayerListEntry
};

use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;

use pocketmine\console\ConsoleCommandSender;

use Ramsey\Uuid\Uuid;

abstract class NPC {
    private array $spawnedTo = [];

    private int $int = 0;

	private int $entityId;
	private $uuid;

	private EntityMetadataCollection $networkProperties;

    public function __construct(private string $name = "") {
		$this->entityId = Entity::nextRuntimeId();
		$this->uuid = Uuid::uuid4();
		$this->networkProperties = new EntityMetadataCollection();
    }

    public final function getName() : string {
        return $this->name;
    }

    public abstract function getLocation() : Location;

    public abstract function getNameTag() : string;

    public abstract function getSkin() : Skin;

	public abstract function getSize() : EntitySizeInfo;

	public abstract function getScale() : float;

    public abstract function getHeldItem() : Item;

    public abstract function getArmor() : array;

    public abstract function rotate() : bool;

    public abstract function getMovement() : array;

	public abstract function getMoveTime() : int;

	public abstract function onInteract(CorePlayer $player) : void;

    public function getUuid() {
        return $this->uuid;
    }

    public function getEntityId() : int {
        return $this->entityId;
    }

	//array | CorePlayer
	public function sendData(CorePlayer $player, ?array $data = null) : void{
		if(!is_array($player)) {
			$player = [$player];
		}
		$pk = SetActorDataPacket::create($this->getEntityId(), $data ?? $this->getSyncedNetworkData(false), 0);

		foreach($player as $p){
			$p->getNetworkSession()->sendDataPacket(clone $pk);
		}
	}

    public function isSpawnedTo(CorePlayer $player) : bool {
        return isset($this->spawnedTo[$player->getName()]);
    }

    public function spawnTo(CorePlayer $player) : void {
		//needed?
    	$player->getNetworkSession()->sendDataPacket(AddActorPacket::create($this->getEntityId(), $this->getEntityId(), 'custom:entity', $this->getLocation(), null, $this->getLocation()->pitch, $this->getLocation()->yaw, 0.0, [], $this->getSyncedNetworkData(false), []));

		$player->getNetworkSession()->sendDataPacket(PlayerListPacket::add([PlayerListEntry::createAdditionEntry($this->getUuid(), $this->getEntityId(), $this->getName(), SkinAdapterSingleton::get()->toSkinData($this->getSkin()))]));

		$nameTag = $this->getNameTag();

		if($this->getName() === "HCF" or $this->getName() === "Lobby") {
			$server = NetworkManager::getInstance()->getServer($this->getName());

			if(is_null($server)) {
				return;
			}
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
    	$player->getNetworkSession()->sendDataPacket(AddPlayerPacket::create($this->getUuid(), $nameTag, $this->getEntityId(), $this->getEntityId(), "", $this->getLocation()->asVector3(), null, $this->getLocation()->pitch, $this->getLocation()->yaw, 0.0, ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->getHeldItem())), GameMode::SURVIVAL, $this->getSyncedNetworkData(false), AdventureSettingsPacket::create(0, 0, 0, 0, 0, $this->getEntityId()), [], "", DeviceOS::UNKNOWN));
		$this->sendData($player, [EntityMetadataProperties::NAMETAG => new StringMetadataProperty($nameTag)]);
		$player->getNetworkSession()->sendDataPacket(PlayerListPacket::remove([PlayerListEntry::createRemovalEntry($this->uuid)]));
    	/**
		 * if($this->animation !== ""){
			PracticeCore::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player){
			if($player->isOnline()){
				$player->getNetworkSession()->sendDataPacket(AnimateEntityPacket::create($this->animation, "", "", 0, "", 0, [$this->getId()]));
			}
			}), 40);
		 */
        $this->spawnedTo[$player->getName()] = true;

		$helmet = ItemFactory::getInstance()->get(ItemIds::AIR);
		$chestplate = ItemFactory::getInstance()->get(ItemIds::AIR);
		$leggings = ItemFactory::getInstance()->get(ItemIds::AIR);
		$boots = ItemFactory::getInstance()->get(ItemIds::AIR);

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
		$head = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($helmet));
		$chest = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($chestplate));
		$legs = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($leggings));
		$feet = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($boots));
        $maep = MobArmorEquipmentPacket::create($this->getEntityId(), $head, $chest, $legs, $feet);
        $player->getNetworkSession()->sendDataPacket($maep);
    }

    public function rotateTo(CorePlayer $player) : void {
        if($this->rotate()) {
            $NPCPos = $this->getLocation()->asVector3();

            if($this->isSpawnedTo($player) && $player->getPosition()->distance($NPCPos) <= EssenceData::MAX_DISTANCE) {
                $x = $NPCPos->x - $player->getPosition()->getX();
                $y = $NPCPos->y - $player->getPosition()->getY();
                $z = $NPCPos->z - $player->getPosition()->getZ();
                $yaw = asin($x / sqrt($x * $x + $z * $z)) / 3.14 * 180;
                $pitch = round(asin($y / sqrt($x * $x + $z * $z + $y * $y)) / 3.14 * 180);

                if($z > 0) {
                    $yaw = -$yaw + 180;
                }
                $packet = MovePlayerPacket::create($this->getEntityId(), $this->getLocation()->add(0, 1.62, 0), $pitch, $yaw, $yaw, MovePlayerPacket::MODE_NORMAL, true, 0, 0, 0, 0);

                $player->getNetworkSession()->sendDataPacket($packet);
            }
        }
    }

    public function move(CorePlayer $player) : void {
        if(!empty($this->getMovement())) {
			$this->int++;
			$array = explode(", ", $this->getMovement()[$this->int]);
			//change yaw and pitch later
			$position = new Location((float) $array[0], (float) $array[1], (float) $array[2], Core::getInstance()->getServer()->getWorldManager()->getWorldByName($array[3]), 0, 0);

			//head yaw?
			$packet = MoveActorAbsolutePacket::create($this->getEntityId(), $position->asVector3(), $this->getLocation()->getPitch(), $this->getLocation()->getYaw(), 0, 0);
            $player->getNetworkSession()->sendDataPacket($packet);

            if(end($array)) {
                $this->int--;
            }
        }
    }

	/**
	 * @return MetadataProperty[]
	 */
	final public function getSyncedNetworkData(bool $dirtyOnly) : array {
		$this->syncNetworkData();
		return $dirtyOnly ? $this->networkProperties->getDirty() : $this->networkProperties->getAll();
	}

	public function syncNetworkData() : void {
		$this->networkProperties->setByte(EntityMetadataProperties::ALWAYS_SHOW_NAMETAG, 1);
		$this->networkProperties->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, $this->getSize()->getHeight() / $this->getScale());
		$this->networkProperties->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, $this->getSize()->getWidth() / $this->getScale());
		$this->networkProperties->setFloat(EntityMetadataProperties::SCALE, $this->getScale());
		$this->networkProperties->setLong(EntityMetadataProperties::LEAD_HOLDER_EID, -1);
		$this->networkProperties->setLong(EntityMetadataProperties::OWNER_EID, $this->ownerId ?? -1);
		$this->networkProperties->setLong(EntityMetadataProperties::TARGET_EID, $this->targetId ?? 0);
		$this->networkProperties->setString(EntityMetadataProperties::NAMETAG, $this->getNameTag());

		$this->networkProperties->setGenericFlag(EntityMetadataFlags::AFFECTED_BY_GRAVITY, false);
		$this->networkProperties->setGenericFlag(EntityMetadataFlags::CAN_SHOW_NAMETAG, true);
		$this->networkProperties->setGenericFlag(EntityMetadataFlags::HAS_COLLISION, true);
		$this->networkProperties->setGenericFlag(EntityMetadataFlags::IMMOBILE, false);
		$this->networkProperties->setGenericFlag(EntityMetadataFlags::INVISIBLE, false);
		$this->networkProperties->setGenericFlag(EntityMetadataFlags::ONFIRE, false);
		$this->networkProperties->setGenericFlag(EntityMetadataFlags::SNEAKING, false);
		$this->networkProperties->setGenericFlag(EntityMetadataFlags::WALLCLIMBING, false);
	}

	public function despawnFrom(CorePlayer $player) : void {
		unset($this->spawnedTo[$player->getName()]);

		//needed?
		$player->getNetworkSession()->sendDataPacket(RemoveActorPacket::create($this->getEntityId()));
		
		$player->getNetworkSession()->sendDataPacket(PlayerListPacket::remove([PlayerListEntry::createAdditionEntry($this->getUuid(), $this->getEntityId(), $this->getName(), \pocketmine\network\mcpe\convert\SkinAdapterSingleton::get()->toSkinData($this->getSkin()))]));
	}
}
