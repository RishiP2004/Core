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

use GPCore\Stats\Objects\GPPlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\item\enchantment\{
	Enchantment,
	EnchantmentInstance
};

class EnchantCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("enchant", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.Enchant");
        $this->setUsage("<enchant> [level] [player]");
        $this->setDescription("Add an Enchantment to yours or another Player's Item");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /enchant" . " " . $this->getUsage());
            return false;
        }
        if(!is_numeric($args[0])) {
			$enchantment = Enchantment::getEnchantmentByName($args[0]);
        } else {
			$enchantment = Enchantment::getEnchantment($args[0]);
		}
		if(!$enchantment instanceof Enchantment) {
			$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Enchantment");
            return false;
		}
        if(isset($args[2])) {
			if(!$sender->hasPermission($this->getPermission() . ".Other")) {
				$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
				return false;
			}
 			$user = $this->GPCore->getStats()->getGPUser($args[2]);

			if(!$user->hasAccount()) {
				$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[2] . " is not a valid Player");
				return false;
			}
            $player = $user->getGPPlayer();

            if(!$player instanceof GPPlayer) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $user->getUsername() . " is not Online");
                return false;
			}
            $item = $player->getInventory()->getItemInHand();

			if($item->getId() <= 0) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $user->getUsername() . " doesn't have an Item in their hand");
                return false;
            } else {
                $item->addEnchantment(new EnchantmentInstance($enchantment, $args[1] ?? 1));
                $player->getInventory()->setItemInHand($item);
				$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Enchanted the Item in " . $args[2] . "'s hand with " . $enchant . " and Level " . $args[1]);
                $player->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " Enchanted the Item in your hand with " . $enchant . " and Level " . $args[1]);
                return true;
            }
        }
        if(!$sender instanceof GPPlayer) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } 			
		$item = $sender->getInventory()->getItemInHand();

		if($item->getId() <= 0) {
			$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You don't have an Item in your hand");
			return false;
        } else {
			$item->addEnchantment(new EnchantmentInstance($enchantment, $args[1] ?? 1));
			$sender->getInventory()->setItemInHand($item);
			$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Enchanted the Item in your hand with " . $enchant . " and Level " . $args[1]);
            return true;
        }
    }
}