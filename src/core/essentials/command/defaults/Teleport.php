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

use pocketmine\math\Vector3;

class Teleport extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("teleport", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.teleport.command");
        $this->setUsage("[target : all] <player : x> <y> <z> [<y-rot> <x-rot>]");
        $this->setDescription("Teleport Command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 4) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /teleport" . " " . $this->getUsage());
            return false;
        }
        $target = null;
        $origin = $sender;

        if(count($args) === 1 or count($args) === 3) {
            if($sender instanceof CorePlayer) {
                $target = $sender;
            } else {
                $sender->sendMessage($this->core->getErrorPrefix() . "Provide a Player");
                return false;
            }
            if(count($args) === 1) {
                if($args[0] === "all") {
                    foreach($this->core->getServer()->getOnlinePlayers() as $onlinePlayer) {
                        $target = $onlinePlayer;
                    }
                } else {
                    $target = $sender->getServer()->getPlayer($args[0]);

                    if($target === null) {
                        $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
                        return false;
                    }
                }
            }
        } else {
            $target = $sender->getServer()->getPlayer($args[0]);

            if($target === null) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
                return false;
            }
            if(count($args) === 2) {
                $origin = $target;

                if($args[0] === "all") {
                    foreach($this->core->getServer()->getOnlinePlayers() as $onlinePlayer) {
                        $target = $onlinePlayer;
                    }
                } else {
                    $target = $sender->getServer()->getPlayer($args[0]);

                    if($target === null) {
                        $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
                        return false;
                    }
                }
            }
        }
        if(count($args) < 3) {
            $origin->teleport($target);
            $origin->sendMessage($this->core->getPrefix() . "Teleported to " . $target->getName());
            $target->sendMessage($this->core->getPrefix() . $sender->getName() . " Teleported to you");
            return true;
        } else if($target->getLevel() !== null) {
            if(count($args) === 4 or \count($args) === 6) {
                $pos = 1;
            } else {
                $pos = 0;
            }
            $x = PocketMine::getRelativeDouble((float) $target->x, $sender, $args[$pos++]);
            $y = PocketMine::getRelativeDouble((float) $target->y, $sender, $args[$pos++], 0, 256);
            $z = PocketMine::getRelativeDouble((float) $target->z, $sender, $args[$pos++]);
            $yaw = $target->getYaw();
            $pitch = $target->getPitch();

            if(count($args) === 6 or (count($args) === 5 and $pos === 3)) {
                $yaw = (float) $args[$pos++];
                $pitch = (float) $args[$pos++];
            }
            $target->teleport(new Vector3($x, $y, $z), $yaw, $pitch);
            $target->sendMessage($this->core->getPrefix() . "Teleported to X: " . round($x, 2) . " Y: " . round($y, 2) . " Z: " . round($z, 2));
            return true;
        }
        return true;
    }
}