<?php

declare(strict_types = 1);

namespace core\essentials;

use core\Core;

use core\essentials\command\{
	Chat,
    ClearInventory,
    Fly,
    Hud,
    Jump,
	Location,
    Ping,
    Sudo,
    World
};

use core\essentials\command\defaults\{
    Ban,
    BanIp,
    BanList,
    Block,
    BlockIp,
    BlockList,
    DefaultGamemode,
    Deop,
    Difficulty,
    DumpMemory,
    Effect,
    Enchant,
    Gamemode,
    Help,
    Information,
    Item,
    Kick,
    Kill,
    Lists,
    Mute,
    MuteIp,
    MuteList,
    Op,
    Particle,
    Plugins,
    Reload,
    Save,
    Scoreboard,
    SetSpawn,
    Spawn,
    Status,
    Stop,
    Summon,
    Teleport,
    Tell,
    Time,
    Timings,
    Transfer,
    Unban,
    UnbanIp,
    Unblock,
    UnblockIp,
    Unmute,
    UnmuteIp,
    Whitelist
};

use pocketmine\command\Command;

class Essentials {
    private $core;

    public $timingStart = 0;

    const IP = "ip";
    const NAME = "name";

    const BAN = 0;
    const BLOCK = 1;
    const MUTE = 2;

    public function __construct(Core $core) {
        $this->core = $core;

        $this->core->getDatabase()->executeGeneric("sentences.init");

		$this->core->getServer()->getCommandMap()->register(Chat::class, new Chat($this->core));
        $this->core->getServer()->getCommandMap()->register(ClearInventory::class, new ClearInventory($this->core));
        $this->core->getServer()->getCommandMap()->register(Fly::class, new Fly($this->core));
        $this->core->getServer()->getCommandMap()->register(Hud::class, new Hud($this->core));
        $this->core->getServer()->getCommandMap()->register(Jump::class, new Jump($this->core));
		$this->core->getServer()->getCommandMap()->register(Location::class, new Location($this->core));
        $this->core->getServer()->getCommandMap()->register(Ping::class, new Ping($this->core));
        $this->core->getServer()->getCommandMap()->register(Sudo::class, new Sudo($this->core));
        $this->core->getServer()->getCommandMap()->register(World::class, new World($this->core));

        $commands = [
            "ban",
            "ban-ip",
            "banlist",
			"checkperm",
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
        $this->core->getServer()->getCommandMap()->register(Ban::class, new Ban($this->core));
        $this->core->getServer()->getCommandMap()->register(BanIp::class, new BanIp($this->core));
        $this->core->getServer()->getCommandMap()->register(BanList::class, new BanList($this->core));
        $this->core->getServer()->getCommandMap()->register(Block::class, new Block($this->core));
        $this->core->getServer()->getCommandMap()->register(BlockIp::class, new BlockIp($this->core));
        $this->core->getServer()->getCommandMap()->register(BlockList::class, new BlockList($this->core));
        $this->core->getServer()->getCommandMap()->register(DefaultGamemode::class, new DefaultGamemode($this->core));
        $this->core->getServer()->getCommandMap()->register(Deop::class, new Deop($this->core));
        $this->core->getServer()->getCommandMap()->register(Difficulty::class, new Difficulty($this->core));
        $this->core->getServer()->getCommandMap()->register(DumpMemory::class, new DumpMemory($this->core));
        $this->core->getServer()->getCommandMap()->register(Effect::class, new Effect($this->core));
        $this->core->getServer()->getCommandMap()->register(Enchant::class, new Enchant($this->core));
        $this->core->getServer()->getCommandMap()->register(Gamemode::class, new Gamemode($this->core));
        $this->core->getServer()->getCommandMap()->register(Help::class, new Help($this->core));
        $this->core->getServer()->getCommandMap()->register(Information::class, new Information($this->core));
        $this->core->getServer()->getCommandMap()->register(Item::class, new Item($this->core));
        $this->core->getServer()->getCommandMap()->register(Kick::class, new Kick($this->core));
        $this->core->getServer()->getCommandMap()->register(Kill::class, new Kill($this->core));
        $this->core->getServer()->getCommandMap()->register(Lists::class, new Lists($this->core));
        $this->core->getServer()->getCommandMap()->register(Mute::class, new Mute($this->core));
        $this->core->getServer()->getCommandMap()->register(MuteIp::class, new MuteIp($this->core));
        $this->core->getServer()->getCommandMap()->register(MuteList::class, new MuteList($this->core));
        $this->core->getServer()->getCommandMap()->register(Op::class, new Op($this->core));
        $this->core->getServer()->getCommandMap()->register(Particle::class, new Particle($this->core));
        $this->core->getServer()->getCommandMap()->register(Plugins::class, new Plugins($this->core));
        $this->core->getServer()->getCommandMap()->register(Reload::class, new Reload($this->core));
        $this->core->getServer()->getCommandMap()->register(Save::class, new Save($this->core));
        $this->core->getServer()->getCommandMap()->register(SetSpawn::class, new SetSpawn($this->core));
        $this->core->getServer()->getCommandMap()->register(Spawn::class, new Spawn($this->core));
		$this->core->getServer()->getCommandMap()->register(Scoreboard::class, new Scoreboard($this->core));
        $this->core->getServer()->getCommandMap()->register(Status::class, new Status($this->core));
        $this->core->getServer()->getCommandMap()->register(Stop::class, new Stop($this->core));
        $this->core->getServer()->getCommandMap()->register(Summon::class, new Summon($this->core));
        $this->core->getServer()->getCommandMap()->register(Teleport::class, new Teleport($this->core));
		$this->core->getServer()->getCommandMap()->register(Tell::class, new Tell($this->core));
        $this->core->getServer()->getCommandMap()->register(Time::class, new Time($this->core));
        $this->core->getServer()->getCommandMap()->register(Timings::class, new Timings($this->core));
        $this->core->getServer()->getCommandMap()->register(Transfer::class, new Transfer($this->core));
        $this->core->getServer()->getCommandMap()->register(Unban::class, new Unban($this->core));
        $this->core->getServer()->getCommandMap()->register(UnbanIp::class, new UnbanIp($this->core));
        $this->core->getServer()->getCommandMap()->register(Unblock::class, new Unblock($this->core));
        $this->core->getServer()->getCommandMap()->register(UnblockIp::class, new UnblockIp($this->core));
        $this->core->getServer()->getCommandMap()->register(Unmute::class, new Unmute($this->core));
        $this->core->getServer()->getCommandMap()->register(UnmuteIp::class, new UnmuteIp($this->core));
        $this->core->getServer()->getCommandMap()->register(Whitelist::class, new Whitelist($this->core));
    }

    public function getNameBans() : \core\essentials\permission\BanList {
        $banList = new \core\essentials\permission\BanList(self::NAME);

        $banList->load();
        return $banList;
    }

    public function getIpBans() : \core\essentials\permission\BanList {
        $banList = new \core\essentials\permission\BanList(self::IP);

        $banList->load();
        return $banList;
    }

    public function getNameBlocks() : \core\essentials\permission\BlockList {
        $blockList = new \core\essentials\permission\BlockList(self::NAME);

        $blockList->load();
        return $blockList;
    }

    public function getIpBlocks() : \core\essentials\permission\BlockList {
        $blockList = new \core\essentials\permission\BlockList(self::IP);

        $blockList->load();
        return $blockList;
    }

    public function getNameMutes() : \core\essentials\permission\MuteList {
        $muteList = new \core\essentials\permission\MuteList(self::NAME);

        $muteList->load();
        return $muteList;
    }

    public function getIpMutes() : \core\essentials\permission\MuteList {
        $muteList = new \core\essentials\permission\MuteList(self::IP);

        $muteList->load();
        return $muteList;
    }
}