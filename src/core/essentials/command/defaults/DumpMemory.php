<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;

use core\essentials\Essentials;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class DumpMemory extends PluginCommand {
    private $manager;

    private static $executions = 0;

	public function __construct(Essentials $manager) {
        parent::__construct("dumpmemory", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.dumpmemory");
        $this->setUsage("<token> [path]");
        $this->setDescription("Dump the Server's Memory");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /dumpmemory " . $this->getUsage());
            return false;
        }
        $token = strtoupper(substr(sha1(BOOTUP_RANDOM . ":" . $sender->getServer()->getServerUniqueId() . ":" . self::$executions), 6, 6));

        if(\strtoupper($args[0]) !== $token) {
            $sender->sendMessage(Core::ERROR_PREFIX . $token . " is not this Server's Token");
            return false;
        } else {
            ++self::$executions;

            $sender->getServer()->getMemoryManager()->dumpServerMemory($args[1] ?? ($sender->getServer()->getDataPath() . "/memoryDump_$token"), 48, 80);
            return true;
        }
    }
}