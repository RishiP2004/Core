<?php

namespace core\essentials\command\defaults;

use core\Core;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\plugin\Plugin;

use pocketmine\utils\TextFormat;

use pocketmine\network\mcpe\protocol\ProtocolInfo;

class Information extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("information", $core);

        $this->core = $core;

		$this->setAliases(["info", "about"]);
		$this->setUsage("[plugin]");
        $this->setPermission("core.essentials.defaults.information.command");
        $this->setDescription("Check the Information of the Server or a Plugin");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
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

                        $sender->sendMessage($this->core->getPrefix() . $description->getDescription() . " Information:");
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
                    $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Plugin");
                    return true;
                }
			}
			$sender->sendMessage($this->core->getPrefix() . "Server Info:");
			$sender->sendMessage(TextFormat::GRAY . "Name: " . $sender->getServer()->getName());
			$sender->sendMessage(TextFormat::GRAY . "PocketMine Version: " . $sender->getServer()->getPocketMineVersion());
			$sender->sendMessage(TextFormat::GRAY . "Minecraft Version: " . $sender->getServer()->getVersion());
			$sender->sendMessage(TextFormat::GRAY . "Protocol Version: " . ProtocolInfo::CURRENT_PROTOCOL);
        }
        return false;
    }
}