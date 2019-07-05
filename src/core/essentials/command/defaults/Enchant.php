<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\item\enchantment\{
	Enchantment,
	EnchantmentInstance
};

class Enchant extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("enchant", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.enchant.command");
        $this->setUsage("<enchant> [level] [player]");
        $this->setDescription("Add an Enchantment to yours or another Player's Item");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /enchant" . " " . $this->getUsage());
            return false;
        }
        if(!is_numeric($args[0])) {
			$enchantment = Enchantment::getEnchantmentByName($args[0]);
        } else {
			$enchantment = Enchantment::getEnchantment($args[0]);
		}
		if(!$enchantment instanceof Enchantment) {
			$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Enchantment");
            return false;
		}
        if(isset($args[2])) {
			if(!$sender->hasPermission($this->getPermission() . ".Other")) {
				$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
				return false;
			}
            $player = $this->core->getServer()->getPlayer($args[0]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not Online");
                return false;
            }
            $item = $player->getInventory()->getItemInHand();

			if($item->getId() <= 0) {
                $sender->sendMessage($this->core->getErrorPrefix() . $player->getName() . " doesn't have an Item in their hand");
                return false;
            } else {
                $item->addEnchantment(new EnchantmentInstance($enchantment, $args[1] ?? 1));
                $player->getInventory()->setItemInHand($item);
                $sender->sendMessage($this->core->getPrefix() . "Enchanted the Item in " . $args[2] . "'s hand with " . $enchantment->getName() . " and Level " . $args[1]);
                $player->sendMessage($this->core->getPrefix() . $sender->getName() . " Enchanted the Item in your hand with " . $enchantment->getName() . " and Level " . $args[1]);
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } 			
		$item = $sender->getInventory()->getItemInHand();

		if($item->getId() <= 0) {
			$sender->sendMessage($this->core->getErrorPrefix() . "You don't have an Item in your hand");
			return false;
        } else {
			$item->addEnchantment(new EnchantmentInstance($enchantment, $args[1] ?? 1));
			$sender->getInventory()->setItemInHand($item);
			$sender->sendMessage($this->core->getPrefix() . "Enchanted the Item in your hand with " . $enchantment->getName() . " and Level " . $args[1]);
            return true;
        }
    }
}