<?php

declare(strict_types = 1);

namespace core\essentials;

use core\Core;
use core\CorePlayer;

use core\utils\Manager;

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

use pocketmine\Server;

class Essentials extends Manager {
	public static $instance = null;

    public $timingStart = 0;

    const IP = "ip";
    const NAME = "name";

    const BAN = 0;
    const BLOCK = 1;
    const MUTE = 2;

    public function init() {
    	self::$instance = $this;

        Core::getInstance()->getDatabase()->executeGeneric("sentences.init");

        $this->registerCommand(Chat::class, new Chat($this));
		$this->registerCommand(ClearInventory::class, new ClearInventory($this));
		$this->registerCommand(Fly::class, new Fly($this));
		$this->registerCommand(Hud::class, new Hud($this));
		$this->registerCommand(Jump::class, new Jump($this));
		$this->registerCommand(Location::class, new Location($this));
		$this->registerCommand(Ping::class, new Ping($this));
		$this->registerCommand(Sudo::class, new Sudo($this));
		$this->registerCommand(World::class, new World($this));

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
            $commandMap = Server::getInstance()->getCommandMap();
            $command = $commandMap->getCommand($command);

            if($command instanceof Command) {
                $commandMap->unregister($command);
            }
        }
		foreach([$this->getNameBans(), $this->getIpBans(), $this->getNameBlocks(), $this->getIpBlocks(), $this->getNameMutes(), $this->getIpMutes()] as $type) {
			$type->load();
		}
		$this->registerCommand(Ban::class, new Ban($this));
		$this->registerCommand(BanIp::class, new BanIp($this));
		$this->registerCommand(BanList::class, new BanList($this));
		$this->registerCommand(Block::class, new Block($this));
		$this->registerCommand(BlockIp::class, new BlockIp($this));
		$this->registerCommand(BlockList::class, new BlockList($this));
		$this->registerCommand(DefaultGamemode::class, new DefaultGamemode($this));
		$this->registerCommand(Deop::class, new Deop($this));
		$this->registerCommand(Difficulty::class, new Difficulty($this));
		$this->registerCommand(DumpMemory::class, new DumpMemory($this));
		$this->registerCommand(Effect::class, new Effect($this));
		$this->registerCommand(Enchant::class, new Enchant($this));
		$this->registerCommand(Gamemode::class, new Gamemode($this));
		$this->registerCommand(Help::class, new Help($this));
		$this->registerCommand(Information::class, new Information($this));
		$this->registerCommand(Item::class, new Item($this));
		$this->registerCommand(Kick::class, new Kick($this));
		$this->registerCommand(Kill::class, new Kill($this));
		$this->registerCommand(Lists::class, new Lists($this));
		$this->registerCommand(Mute::class, new Mute($this));
		$this->registerCommand(MuteIp::class, new MuteIp($this));
		$this->registerCommand(MuteList::class, new MuteList($this));
		$this->registerCommand(Op::class, new Op($this));
		$this->registerCommand(Particle::class, new Particle($this));
		$this->registerCommand(Plugins::class, new Plugins($this));
		$this->registerCommand(Reload::class, new Reload($this));
		$this->registerCommand(Save::class, new Save($this));
		$this->registerCommand(SetSpawn::class, new SetSpawn($this));
		$this->registerCommand(Spawn::class, new Spawn($this));
		$this->registerCommand(Scoreboard::class, new Scoreboard($this));
		$this->registerCommand(Status::class, new Status($this));
		$this->registerCommand(Stop::class, new Stop($this));
		$this->registerCommand(Teleport::class, new Teleport($this));
		$this->registerCommand(Tell::class, new Tell($this));
		$this->registerCommand(Time::class, new Time($this));
		$this->registerCommand(Timings::class, new Timings($this));
		$this->registerCommand(Transfer::class, new Transfer($this));
		$this->registerCommand(Unban::class, new Unban($this));
		$this->registerCommand(UnbanIp::class, new UnbanIp($this));
		$this->registerCommand(Unblock::class, new Unblock($this));
		$this->registerCommand(UnblockIp::class, new UnblockIp($this));
		$this->registerCommand(Unmute::class, new Unmute($this));
		$this->registerCommand(UnmuteIp::class, new UnmuteIp($this));
		$this->registerCommand(Whitelist::class, new Whitelist($this));

		$this->registerListener(new EssentialsListener($this), Core::getInstance());
    }

	public static function getInstance() : self {
		return self::$instance;
	}

	public function tick() : void {
    	foreach(Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
			if($onlinePlayer instanceof CorePlayer) {
				if($onlinePlayer->hasHud($onlinePlayer::SCOREBOARD)) {
					$onlinePlayer->setHud($onlinePlayer::SCOREBOARD, false);
					$onlinePlayer->setHud($onlinePlayer::SCOREBOARD);
				}
			}
			if($onlinePlayer instanceof CorePlayer) {
				if($onlinePlayer->hasHud($onlinePlayer::POPUP)) {
					$onlinePlayer->setHud($onlinePlayer::POPUP);
				}
			}
    	}
	}

    public function getNameBans() : \core\essentials\permission\BanList {
        $banList = new \core\essentials\permission\BanList(self::NAME);
        return $banList;
    }

    public function getIpBans() : \core\essentials\permission\BanList {
        $banList = new \core\essentials\permission\BanList(self::IP);
        return $banList;
    }

    public function getNameBlocks() : \core\essentials\permission\BlockList {
        $blockList = new \core\essentials\permission\BlockList(self::NAME);
        return $blockList;
    }

    public function getIpBlocks() : \core\essentials\permission\BlockList {
        $blockList = new \core\essentials\permission\BlockList(self::IP);
        return $blockList;
    }

    public function getNameMutes() : \core\essentials\permission\MuteList {
        $muteList = new \core\essentials\permission\MuteList(self::NAME);
        return $muteList;
    }

    public function getIpMutes() : \core\essentials\permission\MuteList {
        $muteList = new \core\essentials\permission\MuteList(self::IP);
        return $muteList;
    }
}