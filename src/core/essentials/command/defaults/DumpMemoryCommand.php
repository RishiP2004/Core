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

class DumpMemoryCommand extends PluginCommand {
    private $GPCore;

    private static $executions = 0;

    public function __construct(GPCore $GPCore) {
        parent::__construct("dumpmemory", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.DumpMemory");
        $this->setUsage("<token> [path]");
        $this->setDescription("Dump the Server's Memory");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /dumpmemory" . " " . $this->getUsage());
            return false;
        }
        $token = strtoupper(substr(sha1(BOOTUP_RANDOM . ":" . $sender->getServer()->getServerUniqueId() . ":" . self::$executions), 6, 6));

        if(\strtoupper($args[0]) !== $token) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $token . " is not this Server's Token");
            return false;
        } else {
            ++self::$executions;

            $sender->getServer()->getMemoryManager()->dumpServerMemory($args[1] ?? ($sender->getServer()->getDataPath() . "/memoryDump_$token"), 48, 80);
            return true;
        }
    }
}