<?php

declare(strict_types = 1);

namespace core\essential;

use core\Core;
use core\database\Database;
use core\player\CorePlayer;

use core\utils\Manager;

use core\essential\command\{
	ChatCommand,
    FlyCommand,
    HudCommand,
    JumpCommand,
	LocationCommand,
	//NickCommand,
    PingCommand,
    ReplyCommand,
	RepopulateChunkCommand,
	TogglePMCommand,
    WorldCommand
};
use core\essential\command\defaults\{
    BanCommand,
    BanIpCommand,
    BanListCommand,
    BlockCommand,
    BlockIpCommand,
    BlockList,
    HelpCommand,
    ListCommand,
    MuteCommand,
    MuteIpCommand,
    MuteListCommand,
    SpawnCommand,
    TellCommand,
    UnbanCommand,
    UnbanIpCommand,
    UnblockCommand,
    UnblockIpCommand,
    UnmuteCommand,
    UnmuteIpCommand,
    WhitelistCommand
};

use pocketmine\command\Command;

use pocketmine\Server;
//TODO: EXTEND ORIGINAL COMMAND FOR DIFF MSGS, ARGUMENTS
class EssentialManager extends Manager {
	public static ?self $instance = null;

    public int $timingStart = 0;

	private array $lists = [];

    const IP = "ip";
    const NAME = "name";

    const BAN = 0;
    const BLOCK = 1;
    const MUTE = 2;

    public function init() : void {
    	self::$instance = $this;

        Database::get()->executeGeneric("sentences.init");
		//NEEDED? STOP SOME COMMANDS FROM SHOWING OR WHAT
		/**
		SimplePacketHandler::createInterceptor($this->getCore(), EventPriority::HIGH)
			->interceptOutgoing(function(AvailableCommandsPacket $pk, NetworkSession $dst): bool {
				if($this->getServer()->isOp($dst->getPlayer()->getName())) return true;
				foreach($pk->commandData as $commandName => $commandData){
					if(count($this->shownCommands) > 0 && !isset($this->shownCommands[$commandName])){
						unset($pk->commandData[$commandName]);
					}
					if(count($this->hiddenCommands) > 0 && isset($this->hiddenCommands[$commandName])){
						unset($pk->commandData[$commandName]);
					}
				}
				return true;
		});*/
		$this->registerPermissions([
			"chat.command" => [
				"default" => "op",
				"description" => "Chat command"
			],
			"fly.command" => [
				"default" => "op",
				"description" => "Fly command"
			],
			"hud.command" => [
				"default" => "true",
				"description" => "Hud command"
			],
			"jump.command" => [
				"default" => "op",
				"description" => "Jump command"
			],
			"location.command" => [
				"default" => "true",
				"description" => "Location command"
			],
			"ping.command" => [
				"default" => "true",
				"description" => "Ping command"
			],
			"reply.command" => [
				"default" => "true",
				"description" => "Reply command"
			],
			"repopulatechunk.command" => [
				"default" => "op",
				"description" => "Repopulate command"
			],
			"togglepm.command" => [
				"default" => "true",
				"description" => "Toggle PM command"
			],
			"world.command" => [
				"default" => "op",
				"description" => "World command"
			],
		]);
        $this->registerCommands("essential",
        	new ChatCommand(Core::getInstance(), "chat", "Chat Command"),
			new FlyCommand(Core::getInstance(), "fly", "Fly Command"),
			new HudCommand(Core::getInstance(), "hud", "Hud Command"),
			new JumpCommand(Core::getInstance(), "jump", "Jump Command"),
			new LocationCommand(Core::getInstance(), "location", "Location Command"),
			//new NickCommand($this)
			new PingCommand(Core::getInstance(), "ping", "Ping Command"),
			new ReplyCommand(Core::getInstance(), "reply", "Reply Command"),
			new RepopulateChunkCommand(Core::getInstance(), "repopulatechunk", "Repopulate Chunk Command"),
			new TogglePMCommand(Core::getInstance(), "togglepm", "Toggle PM Command"),
			new WorldCommand(Core::getInstance(), "world", "World Command")
		);

        $commands = [
            "ban",
            "ban-ip",
            "banlist",
            "help",
            "list",
            "me",
            "pardon",
            "pardon-ip",
            "say",
            "spawnpoint",
            "tell",
            "version",
            //"whitelist"
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
		$this->registerPermissions([
			"ban.command" => [
				"default" => "op",
				"description" => "Ban command"
			],
			"ban-ip.command" => [
				"default" => "op",
				"description" => "Ban-Ip command"
			],
			"banlist.command" => [
				"default" => "op",
				"description" => "Banlist command"
			],
			"block.command" => [
				"default" => "op",
				"description" => "Block command"
			],
			"block-ip.command" => [
				"default" => "op",
				"description" => "Block-Ip command"
			],
			"blocklist.command" => [
				"default" => "op",
				"description" => "Block-List command"
			],
			"help.command" => [
				"default" => "true",
				"description" => "Help command"
			],
			"list.command" => [
				"default" => "true",
				"description" => "List command"
			],
			"mute.command" => [
				"default" => "op",
				"description" => "Mute command"
			],
			"mute-ip.command" => [
				"default" => "op",
				"description" => "Mute-Ip command"
			],
			"mutelist.command" => [
				"default" => "op",
				"description" => "Mute-List command"
			],
			"spawn.command" => [
				"default" => "true",
				"description" => "Spawn command"
			],
			"tell.command" => [
				"default" => "true",
				"description" => "Tell command"
			],
			"unban.command" => [
				"default" => "op",
				"description" => "Unban command"
			],
			"unban-ip.command" => [
				"default" => "op",
				"description" => "Unban-Ip command"
			],
			"unblock.command" => [
				"default" => "op",
				"description" => "Unblock command"
			],
			"unblock-ip.command" => [
				"default" => "op",
				"description" => "Unblock-Ip command"
			],
			"unmute.command" => [
				"default" => "op",
				"description" => "Unmute command"
			],
			"unmute-ip.command" => [
				"default" => "op",
				"description" => "Unmute-Ip command"
			],
		]);
		$this->registerCommands("essential",
			new BanCommand(Core::getInstance(), "ban", "Ban Command"),
			new BanIpCommand(Core::getInstance(), "ban-ip", "Ban-Ip Command"),
			new BanListCommand(Core::getInstance(), "banlist", "Ban List Command"),
			new BlockCommand(Core::getInstance(), "block", "Block Command"),
			new BlockIpCommand(Core::getInstance(), "block-ip", "Block-Ip Command"),
			new BlockList(Core::getInstance(), "blocklist", "Block List Command"),
			new HelpCommand(Core::getInstance(), "help", "Help Command"),
			new ListCommand(Core::getInstance(), "list", "List Command"),
			new MuteCommand(Core::getInstance(), "mute", "Mute Command"),
			new MuteIpCommand(Core::getInstance(), "mute-ip", "Mute-Ip Command"),
			new MuteListCommand(Core::getInstance(), "mutelist", "Mute List Command"),
			new SpawnCommand(Core::getInstance(), "spawn", "Spawn Command"),
			new TellCommand(Core::getInstance(), "tell", "Tell Command"),
			new UnbanCommand(Core::getInstance(), "unban", "Unban Command"),
			new UnbanIpCommand(Core::getInstance(), "unban-ip", "Unban-Ip Command"),
			new UnblockCommand(Core::getInstance(), "unblock", "Unblock Command"),
			new UnblockIpCommand(Core::getInstance(), "unblock-ip", "Unblock-Ip Command"),
			new UnmuteCommand(Core::getInstance(), "unmute", "Unmute Command"),
			new UnmuteIpCommand(Core::getInstance(), "unmute-ip", "Unmute-Ip Command"),
			//new WhitelistCommand($this)
		);
		$this->registerListener(new EssentialListener($this), Core::getInstance());
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

    public function getNameBans() : \core\essential\permission\BanList {
        return $this->lists["nameBan"] = $this->lists["nameBan"] ?? new \core\essential\permission\BanList(self::NAME);
	}

    public function getIpBans() : \core\essential\permission\BanList {
        return $this->lists["ipBan"] = $this->lists["ipBan"] ?? new \core\essential\permission\BanList(self::IP);
    }

    public function getNameBlocks() : \core\essential\permission\BlockList {
        return $this->lists["nameBlock"] = $this->lists["nameBlock"] ??  new \core\essential\permission\BlockList(self::NAME);
    }

    public function getIpBlocks() : \core\essential\permission\BlockList {
        return $this->lists["ipBlock"] = $this->lists["ipBlock"] ?? new \core\essential\permission\BlockList(self::IP);
    }

    public function getNameMutes() : \core\essential\permission\MuteList {
        return $this->lists["nameMute"] = $this->lists["nameMute"] ?? new \core\essential\permission\MuteList(self::NAME);
    }

    public function getIpMutes() : \core\essential\permission\MuteList {
        return $this->lists["ipMute"] = $this->lists["ipMute"] ?? new \core\essential\permission\MuteList(self::IP);
    }
}