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

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

use pocketmine\utils\Utils;

class StatusCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("status", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.Status");
        $this->setDescription("See the Server's Status");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
            $rUsage = Utils::getRealMemoryUsage();
            $mUsage = Utils::getMemoryUsage(true);

            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Server status:");

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
            $sender->sendMessage(TextFormat::GRAY . "Current TPS: " . $this->GPCore->getServer()->getTicksPerSecond() . "(" . $this->GPCore->getServer()->getTickUsage() . "%)");
            $sender->sendMessage(TextFormat::GRAY . "Average TPS: " . $this->GPCore->getServer()->getTicksPerSecondAverage() . "(" . $this->GPCore->getServer()->getTickUsageAverage() . "%)");
            $sender->sendMessage(TextFormat::GRAY . "Network Upload: " . round($this->GPCore->getServer()->getNetwork()->getUpload() / 1024, 2) . " kB/s");
            $sender->sendMessage(TextFormat::GRAY . "Network Download: " . round($this->GPCore->getServer()->getNetwork()->getDownload() / 1024, 2) . " kB/s");
            $sender->sendMessage(TextFormat::GRAY . "Thread Count: " . Utils::getThreadCount());
            $sender->sendMessage(TextFormat::GRAY . "Main Thread Memory: " . number_format(round(($mUsage[0] / 1024) / 1024, 2)) . " MB");
            $sender->sendMessage(TextFormat::GRAY . "Total Memory: " . number_format(round(($mUsage[1] / 1024) / 1024, 2)) . " MB");
            $sender->sendMessage(TextFormat::GRAY . "Total Virtual Memory: " . number_format(round(($mUsage[2] / 1024) / 1024, 2)) . " MB");
            $sender->sendMessage(TextFormat::GRAY . "Heap Memory: " . number_format(round(($rUsage[0] / 1024) / 1024, 2)) . " MB");
            $sender->sendMessage(TextFormat::GRAY . "Maximum Memory (System): " . number_format(round(($mUsage[2] / 1024) / 1024, 2)) . " MB");

            if($this->GPCore->getServer()->getProperty("memory.global-limit") > 0) {
                $sender->sendMessage(TextFormat::GRAY . "Maximum Memory (Manager): " . number_format(round($this->GPCore->getServer()->getProperty("memory.global-limit"), 2)) . " MB");
            }
            foreach($this->GPCore->getServer()->getLevels() as $level) {
                $levelName = $level->getFolderName() !== $level->getName() ? " (" . $level->getName() . ")" : "";
                $tickRate = $level->getTickRate() > 1 ? " (tick rate " . $level->getTickRate() . ")" : "";

                $sender->sendMessage(TextFormat::GRAY . "World /" . $level->getFolderName() . "/" . $levelName . ": " . number_format(count($level->getChunks())) . " Chunks, " . number_format(count($level->getEntities())) . " Entities, " . number_format(count($level->getTiles())) . " Tiles. " . "Time: " . round($level->getTickRateTime(), 2) . "ms" . $tickRate);
            }
            return true;
        }
    }
}