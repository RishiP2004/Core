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

use pocketmine\plugin\Plugin;

use pocketmine\utils\TextFormat;

use pocketmine\network\mcpe\protocol\ProtocolInfo;

class InformationCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("information", $GPCore);

        $this->GPCore = $GPCore;

		$this->setAliases(["info", "about"]);
		$this->setUsage("[plugin]");
        $this->setPermission("GPCore.Essentials.Defaults.Command.Information");
        $this->setDescription("Check the Information of the Server or a Plugin");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
			if(isset($args[0])) {
				$pluginName = implode(" ", $args);
				$exactPlugin = $sender->getServer()->getPluginManager()->getPlugin($pluginName);

				if($exactPlugin instanceof Plugin) {
					return true;
				}
				$found = false;
				$pluginName = strtolower($pluginName);
				
				foreach($sender->getServer()->getPluginManager()->getPlugins() as $plugin) {
					if(stripos($plugin->getName(), $pluginName) !== false) {
						$description = $plugin->getDescription();
						
						$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $description->getDescription() . " Information:");
						$sender->sendMessage(TextFormat::GRAY . "Version " . $description->getVersion());

						if($description->getDescription() !== "") {
							$sender->sendMessage(TextFormat::GRAY . $description->getDescription());
						}
						if($description->getWebsite() !== "") {
							$sender->sendMessage(TextFormat::GRAY . "Website: " . $description->getWebsite());
						}
						if(count($authors = $description->getAuthors()) > 0) {
							if(count($authors) === 1) {
								$sender->sendMessage(TextFormat::GRAY . "Author: " . implode(", ", $authors));
							} else {
								$sender->sendMessage(TextFormat::GRAY . "Authors: " . implode(", ", $authors));
							}
						}
						$found = true;
					}
				}
				if(!$found) {
					$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Plugin");
					return true;
				}
			}
			$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Server Info:");
			$sender->sendMessage(TextFormat::GRAY . "Name: " . $sender->getServer()->getName());
			$sender->sendMessage(TextFormat::GRAY . "PocketMine Version: " . $sender->getServer()->getPocketMineVersion());
			$sender->sendMessage(TextFormat::GRAY . "Minecraft Version: " . $sender->getServer()->getVersion());
			$sender->sendMessage(TextFormat::GRAY . "Protocol Version: " . ProtocolInfo::CURRENT_PROTOCOL);
        }
        return false;
    }
}