<?php

namespace core\essentials;

use core\Core;

use core\essentials\command\{
    AFK,
    ClearInventory,
    Compass,
    Fly,
    Jump,
    Ping,
    Servers,
    Sudo,
    World
};

use pocketmine\command\Command;

use core\essentials\permission\{
    MuteList,
    BlockList
};

class Essentials {
    private $core;

    public $timingStart = 0;

    public function __construct(Core $core) {
        $this->core = $core;

        $this->core->getServer()->getCommandMap()->register(AFK::class, new AFK($this->core));
        $this->core->getServer()->getCommandMap()->register(ClearInventory::class, new ClearInventory($this->core));
        $this->core->getServer()->getCommandMap()->register(Compass::class, new Compass($this->core));
        $this->core->getServer()->getCommandMap()->register(Fly::class, new Fly($this->core));
        $this->core->getServer()->getCommandMap()->register(Jump::class, new Jump($this->core));
        $this->core->getServer()->getCommandMap()->register(Servers::class, new Servers($this->core));
        $this->core->getServer()->getCommandMap()->register(Ping::class, new Ping($this->core));
        $this->core->getServer()->getCommandMap()->register(Sudo::class, new Sudo($this->core));
        $this->core->getServer()->getCommandMap()->register(World::class, new World($this->core));

        $commands = [
            "ban",
            "ban-ip",
            "banlist",
            "defaultgamemode",
            "deop",
            "difficulty",
            "dumpmemory",
            "effect",
            "enchant",
            "gamemode",
            "garbagecollector",
            "give",
            "help",
            "kick",
            "kill",
            "list",
            "me",
            "op",
            "pardon",
            "pardon-ip",
            "particle",
            "plugins",
            "reload",
            "save",
            "save-off",
            "save-on",
            "say",
            "seed",
            "setworldspawn",
            "spawnpoint",
            "status",
            "stop",
            "teleport",
            "tell",
            "time",
            "timings",
            "title",
            "transferserver",
            "version",
            "whitelist"
        ];

        foreach($commands as $command) {
            $commandMap = $this->core->getServer()->getCommandMap();
            $command = $commandMap->getCommand($command);

            if($command instanceof Command) {
                $commandMap->unregister($command);
            }
        }
    }

    public function getNameMutes() : MuteList {
        $muteList = new MuteList("muted-players.txt");

        $muteList->load();
        return $muteList;
    }

    public function getIpMutes() : MuteList {
        $muteList = new MuteList("muted-ips.txt");

        $muteList->load();
        return $muteList;
    }

    public function getNameBlocks() : BlockList {
        $blockList = new BlockList("blocked-players.txt");

        $blockList->load();
        return $blockList;
    }

    public function getIpBlocks() : BlockList {
        $blockList = new BlockList("blocked-ips.txt");

        $blockList->load();
        return $blockList;
    }
}