<?php
/**
 *    ___________________________
 *   /  _____/\______   \_   ___ \  ___________   ____
 *  /   \  ___ |     ___/    \  \/ /  _ \_  __ \_/ __ \
 *  \    \_\  \|    |   \     \___(  <_> )  | \/\  ___/
 *   \______  /|____|    \______  /\____/|__|    \___  >
 *          \/                  \/                   \/
 */
namespace GPCore\Essentials\Defaults\Commands;

use GPCore\GPCore;

use GPCore\Stats\Objects\GPPlayer;

use GPCore\Utils\PocketMineUtils;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\math\Vector3;

class TeleportCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("teleport", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.Teleport");
        $this->setUsage("[target : all] <player : x> <y> <z> [<y-rot> <x-rot>]");
        $this->setDescription("Teleport Command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /teleport" . " " . $this->getUsage());
            return false;
        }
        $target = null;
        $origin = $sender;

        if(count($args) === 1 or count($args) === 3) {
            if($sender instanceof GPPlayer) {
                $target = $sender;
            } else {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Provide a Player");
                return false;
            }
            if(count($args) === 1) {
                if($args[0] === "all") {
                    foreach($this->GPCore->getServer()->getOnlinePlayers() as $onlinePlayer) {
                        $target = $onlinePlayer;
                    }
                } else {
                    $target = $sender->getServer()->getPlayer($args[0]);

                    if($target === null) {
                        $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
                        return false;
                    }
                }
            }
        } else {
            $target = $sender->getServer()->getPlayer($args[0]);

            if($target === null) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
                return false;
            }
            if(count($args) === 2) {
                $origin = $target;

                if($args[0] === "all") {
                    foreach($this->GPCore->getServer()->getOnlinePlayers() as $onlinePlayer) {
                        $target = $onlinePlayer;
                    }
                } else {
                    $target = $sender->getServer()->getPlayer($args[0]);

                    if($target === null) {
                        $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
                        return false;
                    }
                }
            }
        }
        if(count($args) < 3) {
            $origin->teleport($target);
            $origin->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Teleported to " . $target->getName());
            $target->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " Teleported to you");
            return true;
        } else if($target->getLevel() !== null) {
            if(count($args) === 4 or \count($args) === 6) {
                $pos = 1;
            } else {
                $pos = 0;
            }
            $x = PocketMineUtils::getRelativeDouble($target->x, $sender, $args[$pos++]);
            $y = PocketMineUtils::getRelativeDouble($target->y, $sender, $args[$pos++], 0, 256);
            $z = PocketMineUtils::getRelativeDouble($target->z, $sender, $args[$pos++]);
            $yaw = $target->getYaw();
            $pitch = $target->getPitch();

            if(count($args) === 6 or (count($args) === 5 and $pos === 3)) {
                $yaw = (float) $args[$pos++];
                $pitch = (float) $args[$pos++];
            }
            $target->teleport(new Vector3($x, $y, $z), $yaw, $pitch);
            $target->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Teleported to X: " . round($x, 2) . " Y: " . round($y, 2) . " Z: " . round($z, 2));
            return true;
        }
        return true;
    }
}