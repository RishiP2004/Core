<?php

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
    }

    public function getBossBar() : BossBar {
        return $this->bossBar;
    }

    public function getName() : string {
        return self::NAME;
    }

    public function getFormats(string $key) {
        return self::FORMATS[$key];
    }

    public function getAutos(string $key) {
        return self::AUTOS[$key];
    }

    public function getTimes(string $key) {
        return self::TIMES[$key];
    }

    public function getDurations(string $key) {
        return self::DURATIONS[$key];
    }

    public function getMessages() : array {
        return self::Messages;
    }

    public function getPopups() : array {
        return self::POPUPS;
    }

    public function getTitles() : array {
        return self::TITLES;
    }

    public function getJoins(string $key) {
        return self::JOINS[$key];
    }

    public function getDeaths(string $key) {
        return self::DEATHS[$key];
    }

    public function getQuits(string $key) {
        return self::QUITS[$key];
    }

    public function getDimensions(string $key) {
        return self::DIMENSIONS[$key];
    }

    public function getKicks(string $key) {
        return self::KICKS[$key];
    }

    public function tick() {
        $this->getBossBar()->tick();
        $this->runs++;

        if($this->getAutos("message")) {
            if($this->runs === $this->getTimes("message") * 20) {
                $this->runs = $this->length + 1;
                $messages = $this->getMessages();
                $messageKey = $this->length;
                $message = $messages[$messageKey];

                if($this->length === count($messages) - 1) {
                    $this->length = -1;
                }
                $this->core->getServer()->broadcastMessage($this->broadcast($message));
            }
        }
        if($this->getAutos("popup")) {
            if($this->runs === $this->getTimes("popup") * 20) {
                $this->length = $this->length + 1;
                $popups = $this->getPopups();
                $popupKey = $this->length;
                $popup = $popups[$popupKey];
                $player = null;

                if($this->length === count($popups) - 1) {
                    $this->length = -1;
                }
                $this->core->getScheduler()->scheduleRepeatingTask(new DurationSend($this->core, self::POPUP, null, $this->getDurations("popup"), $this->broadcast($popup)), 10);
            }
        }
        if($this->getAutos("title")) {
            if($this->runs === $this->getTimes("title") * 20) {
                $this->length = $this->length + 1;
                $titles = $this->getTitles();
                $titleKey = $this->length;
                $title = $titles[$titleKey];

                if($this->length === count($titles) - 1) {
                    $this->length = -1;
                }
                $subTitle = str_replace(array_shift($title), ":", "");

                $this->core->getScheduler()->scheduleRepeatingTask(new DurationSend($this->core, self::TITLE, null, $this->getDurations("title"), $this->broadcast($title), $this->broadcast($subTitle)), 10);
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
            date($this->getFormats("date_time")),
            $this->core->getServer()->getMaxPlayers(),
            count($this->core->getServer()->getOnlinePlayers())
        ], $broadcast);
        return $broadcast;
    }

    public function broadcastByConsole(CommandSender $sender, string $broadcast) : string {
        $format = $this->getFormats("broadcast");
        $format = str_replace([
            "{PREFIX}",
            "{TIME}",
            "{MESSAGE}",
            "{SENDER}"
        ], [
            $this->core->getPrefix(),
            date($this->getFormats("date_time")),
            $broadcast,
            $sender->getName()
        ], $format);
        return $format;
    }
}