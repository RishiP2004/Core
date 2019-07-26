<?php

declare(strict_types = 1);

namespace core\broadcast;

use core\Core;

use core\broadcast\bossbar\BossBar;
use core\broadcast\task\DurationSend;

use pocketmine\command\CommandSender;

class Broadcast implements Broadcasts {
    private $core;

    private $bossBar;

    private $runs = 0, $length = -1;

    const POPUP = 0;
    const TITLE = 1;

    public function __construct(Core $core) {
        $this->core = $core;
        $this->bossBar = new BossBar($core);

        $core->getServer()->getCommandMap()->register(\core\broadcast\command\Broadcast::class, new \core\broadcast\command\Broadcast($core));
		$core->getServer()->getPluginManager()->registerEvents(new BroadcastListener($core), $core);
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
                $messages = self::Messages;
                $messageKey = $this->length;
                $message = $messages[$messageKey];

                if($this->length === count($messages) - 1) {
                    $this->length = -1;
                }
                $this->core->getServer()->broadcastMessage($this->broadcast($message));
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
                $this->core->getScheduler()->scheduleRepeatingTask(new DurationSend($this->core, self::POPUP, null, self::DURATIONS["popup"], $this->broadcast($popup)), 10);
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

                $this->core->getScheduler()->scheduleRepeatingTask(new DurationSend($this->core, self::TITLE, null, self::DURATIONS["title"], $this->broadcast($title), $this->broadcast($subTitle)), 10);
            }
        }
    }

    public function broadcast(string $broadcast) : string {
        $broadcast = str_replace([
            "{PREFIX}",
            "{TIME}",
            "{MAX_PLAYERS}",
            "{TOTAL_PLAYERS}"
        ], [
            $this->core->getPrefix(),
            date(self::FORMATS["date_time"]),
            $this->core->getServer()->getMaxPlayers(),
            count($this->core->getServer()->getOnlinePlayers())
        ], $broadcast);
        return $broadcast;
    }

    public function broadcastByConsole(CommandSender $sender, string $broadcast) : string {
        $format = self::FORMATS["broadcast"];
        $format = str_replace([
            "{PREFIX}",
            "{TIME}",
            "{MESSAGE}",
            "{SENDER}"
        ], [
            $this->core->getPrefix(),
            date(self::FORMATS["date_time"]),
            $broadcast,
            $sender->getName()
        ], $format);
        return $format;
    }
}