<?php

declare(strict_types = 1);

namespace core\broadcast;

use core\Core;

use core\utils\Manager;

use core\broadcast\bossbar\BossBar;
use core\broadcast\task\DurationSendTask;
use core\broadcast\command\BroadcastCommand;

use pocketmine\Server;

use pocketmine\command\CommandSender;

//Todo: Network synced messages
class BroadcastManager extends Manager implements Broadcasts {
	public static ?self $instance = null;

    private BossBar $bossBar;

    private int $runs = 0;
	private int $length = -1;

    const POPUP = 0;
    const TITLE = 1;

	public function init() : void {
		self::$instance = $this;
        $this->bossBar = new BossBar();

		$this->registerPermissions([
			"broadcast.command" => [
				"default" => "op",
				"description" => "Broadcast command"
			],
			"broadcast.subcommand.help" => [
				"default" => "op",
				"description" => "See available Broadcast commands"
			],
			"broadcast.subcommand.sendmessage" => [
				"default" => "true",
				"description" => "Send a message"
			],
			"broadcast.subcommand.sendpopup" => [
				"default" => "op",
				"description" => "Send a popup"
			],
			"broadcast.subcommand.sendtitle" => [
				"default" => "op",
				"description" => "Send a title"
			],
		]);
        $this->registerCommands("broadcast", new BroadcastCommand(Core::getInstance(), "broadcast", "Broadcast Command", ["bc"]));
        $this->registerListener(new BroadcastListener($this), Core::getInstance());
    }

	public static function getInstance() : self {
		return self::$instance;
	}

	public function getBossBar() : BossBar {
        return $this->bossBar;
    }

    public function tick() : void {
        $this->getBossBar()->tick();
        $this->runs++;

        if(self::AUTOS["message"]) {
            if($this->runs === self::TIMES["message"] * 20) {
                $this->runs = $this->length + 1;
                $messages = self::MESSAGES;
                $messageKey = $this->length;
                $message = $messages[$messageKey];

                if($this->length === count($messages) - 1) {
                    $this->length = -1;
                }
                Server::getInstance()->broadcastMessage($this->broadcast($message));
            }
        }
        if(self::AUTOS["popup"]) {
            if($this->runs === self::TIMES["popup"] * 20) {
                $this->length = $this->length + 1;
                $popups = self::POPUPS;
                $popupKey = $this->length;
                $popup = $popups[$popupKey];
                $player = null;

                if($this->length === count($popups) - 1) {
                    $this->length = -1;
                }
                $this->registerRepeatingTask(new DurationSendTask(self::POPUP, null, self::DURATIONS["popup"], $this->broadcast($popup)), 10);
            }
        }
        if(self::AUTOS["title"]) {
            if($this->runs === self::TIMES["title"] * 20) {
                $this->length = $this->length + 1;
                $titles = self::TITLES;
                $titleKey = $this->length;
                $title = $titles[$titleKey];

                if($this->length === count($titles) - 1) {
                    $this->length = -1;
                }
                $subTitle = str_replace(array_shift($title), ":", "");

                $this->registerRepeatingTask(new DurationSendTask(self::TITLE, null, self::DURATIONS["title"], $this->broadcast($title), $this->broadcast($subTitle)), 10);
            }
        }
    }

    public function broadcast(string $broadcast) : string {
		return str_replace([
			"{PREFIX}",
			"{TIME}",
			"{MAX_PLAYERS}",
			"{TOTAL_PLAYERS}"
		], [
			Core::PREFIX,
			date(self::FORMATS["date_time"]),
			Server::getInstance()->getMaxPlayers(),
			count(Server::getInstance()->getOnlinePlayers())
		], $broadcast);
    }

    public function broadcastByConsole(CommandSender $sender, string $broadcast) : string {
        $format = self::FORMATS["broadcast"];
		return str_replace([
			"{PREFIX}",
			"{TIME}",
			"{MESSAGE}",
			"{SENDER}"
		], [
			Core::PREFIX,
			date(self::FORMATS["date_time"]),
			$broadcast,
			$sender->getName()
		], $format);
    }
}