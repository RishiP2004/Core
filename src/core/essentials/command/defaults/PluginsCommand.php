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

class PluginsCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("plugins", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.Plugins");
        $this->setDescription("See the Server's Plugins");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
            $list = "";

            foreach(($plugins = $sender->getServer()->getPluginManager()->getPlugins()) as $plugin) {
                if(\strlen($list) > 0) {
                    $list .= ", ";
                }
                $list .= $plugin->isEnabled() ? TextFormat::GREEN : TextFormat::RED;
                $list .= $plugin->getDescription()->getFullName();
            }
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Plugins (" . count($plugins) . "): " . $list);
            return true;
        }
    }
}