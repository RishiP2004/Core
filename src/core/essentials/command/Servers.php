<?php
namespace core\essentials\command;

use core\Core;

use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Servers extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("servers", $core);

        $this->core = $core;

        $this->setAliases(["srvrs", "server"]);
        $this->setPermission("core.essentials.servers.command");
        $this->setUsage("");
        $this->setDescription("Go to a Server or send a Player there");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        }
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
            $sender->sendServerSelectorForm();
            return true;
        }
    }
}