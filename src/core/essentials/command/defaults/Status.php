<?php

namespace core\essentials\command\defaults;

use core\Core;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

use pocketmine\utils\Utils;

class Status extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("status", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.status.command");
        $this->setDescription("Check the Server's Status");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
            $rUsage = Utils::getRealMemoryUsage();
            $mUsage = Utils::getMemoryUsage(true);

            $sender->sendMessage($this->core->getPrefix() . "Server status:");

            $time = \microtime(\true) - \pocketmine\START_TIME;
            $seconds = \floor($time % 60);
            $minutes = null;
            $hours = null;
            $days = null;

            if($time >= 60) {
                $minutes = \floor(($time % 3600) / 60);

                if($time >= 3600) {
                    $hours = \floor(($time % (3600 * 24)) / 3600);

                    if($time >= 3600 * 24) {
                        $days = \floor($time / (3600 * 24));
                    }
                }
            }
            $uptime = ($minutes !== null ? ($hours !== null ? ($days !== null ? $days . " days " : "") . $hours . " hours " : "") . $minutes . " minutes " : "") . $seconds . " seconds";

            $sender->sendMessage(TextFormat::GRAY . "Uptime: " . $uptime);
            $sender->sendMessage(TextFormat::GRAY . "Current TPS: " . $this->core->getServer()->getTicksPerSecond() . "(" . $this->core->getServer()->getTickUsage() . "%)");
            $sender->sendMessage(TextFormat::GRAY . "Average TPS: " . $this->core->getServer()->getTicksPerSecondAverage() . "(" . $this->core->getServer()->getTickUsageAverage() . "%)");
            $sender->sendMessage(TextFormat::GRAY . "Network Upload: " . round($this->core->getServer()->getNetwork()->getUpload() / 1024, 2) . " kB/s");
            $sender->sendMessage(TextFormat::GRAY . "Network Download: " . round($this->core->getServer()->getNetwork()->getDownload() / 1024, 2) . " kB/s");
            $sender->sendMessage(TextFormat::GRAY . "Thread Count: " . Utils::getThreadCount());
            $sender->sendMessage(TextFormat::GRAY . "Main Thread Memory: " . number_format(round(($mUsage[0] / 1024) / 1024, 2)) . " MB");
            $sender->sendMessage(TextFormat::GRAY . "Total Memory: " . number_format(round(($mUsage[1] / 1024) / 1024, 2)) . " MB");
            $sender->sendMessage(TextFormat::GRAY . "Total Virtual Memory: " . number_format(round(($mUsage[2] / 1024) / 1024, 2)) . " MB");
            $sender->sendMessage(TextFormat::GRAY . "Heap Memory: " . number_format(round(($rUsage[0] / 1024) / 1024, 2)) . " MB");
            $sender->sendMessage(TextFormat::GRAY . "Maximum Memory (System): " . number_format(round(($mUsage[2] / 1024) / 1024, 2)) . " MB");

            if($this->core->getServer()->getProperty("memory.global-limit") > 0) {
                $sender->sendMessage(TextFormat::GRAY . "Maximum Memory (Manager): " . number_format(round($this->core->getServer()->getProperty("memory.global-limit"), 2)) . " MB");
            }
            foreach($this->core->getServer()->getLevels() as $level) {
                $levelName = $level->getFolderName() !== $level->getName() ? " (" . $level->getName() . ")" : "";
                $timeColor = $level->getTickRateTime() > 40 ? TextFormat::RED : TextFormat::YELLOW;

                $sender->sendMessage(TextFormat::GRAY . "World " . $level->getFolderName() . "$levelName: " . number_format(count($level->getChunks())) . " chunks, " . number_format(count($level->getEntities())) .  " entities. " . "Time $timeColor" . round($level->getTickRateTime(), 2) . "ms");
            }
            return true;
        }
    }
}