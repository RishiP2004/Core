<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use core\utils\PocketMine;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\entity\Entity;

use pocketmine\math\Vector3;

class Summon extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("summon", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.command.summon");
        $this->setUsage("<entityType> [x] [y] [z] [level]");
        $this->setDescription("Summon an Entity");
    }

    public function execute(CommandSender $sender, string $label, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(!count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /particle" . " " . $this->getUsage());
            return false;
        }
        $entityId = 0;

        foreach(array_keys($this->core->getMCPE()->getRegisteredEntities()) as $class) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $reflectionClass = new \ReflectionClass($class);

            if(is_numeric($args[0]) && $reflectionClass->getConstant("NETWORK_ID") === (int) $args[0]) {
                $entityId = $reflectionClass->getConstant("NETWORK_ID");
                break;
            } else if(strtolower($args[0]) === strtolower($reflectionClass->getShortName())) {
                $entityId = $reflectionClass->getConstant("NETWORK_ID");
                break;
            }
        }
        if($entityId <= 0) {
            $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Entity Type");
            return true;
        }
        if(count($args) > 1 and count($args) < 4 && $sender instanceof CorePlayer) {
            $x = PocketMine::getRelativeDouble($sender->x, $sender, $args[$pos = 2]);
            $y = PocketMine::getRelativeDouble($sender->y, $sender, $args[++$pos], 0, $sender->getLevel()->getWorldHeight());
            $z = PocketMine::getRelativeDouble($sender->z, $sender, $args[++$pos]);
        } else {
            $x = $sender->x;
            $y = $sender->y;
            $z = $sender->z;
        }
        $entity = Entity::createEntity($entityId, $sender->getLevel(), Entity::createBaseNBT(new Vector3($x, $y, $z)));

        $entity->spawnToAll();
        $sender->sendMessage($args[0] . " spawned at X: " . $x . ", Y: " . $y . ", Z: " . $z);
        return true;
    }
}