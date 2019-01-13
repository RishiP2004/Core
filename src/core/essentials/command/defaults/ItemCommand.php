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

use pocketmine\item\ItemFactory;

use pocketmine\nbt\JsonNBTParser;

use pocketmine\nbt\tag\CompoundTag;

class ItemCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("item", $GPCore);

        $this->GPCore = $GPCore;

		$this->setAliases(["give"]);
        $this->setPermission("GPCore.Essentials.Defaults.Command.Item");
        $this->setUsage("<item [:damage]> [amount] [tags] [player]");
        $this->setDescription("Add an Item to yours or another Player's Inventory");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /item" . " " . $this->getUsage());
            return false;
        }
		$item = ItemFactory::fromString($args[1]);

		if(!isset($args[2])) {
			$item->setCount($item->getMaxStackSize());
		} else {
			$item->setCount($args[2]);
		}
		if(isset($args[3])) {
			$tags = $exception = \null;
			$data = \implode(" ", \array_slice($args, 3));
			
			try {
				$tags = JsonNBTParser::parseJSON($data);
			} catch(\Throwable $throwable) {
				$exception = $throwable;
			}
			if(!$tags instanceof CompoundTag or $exception !== null) {
				$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Invalid Tag");
				return true;
			}
			$item->setNamedTag($tags);
		}
		if(!$item->getId() === 0) {
			$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $item . " is not a valid Item");
            return false;
		}
        if(isset($args[3])) {
			if(!$sender->hasPermission($this->getPermission() . ".Other")) {
				$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
				return false;
			}
 			$user = $this->GPCore->getStats()->getGPUser($args[3]);

			if(!$user->hasAccount()) {
				$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[3] . " is not a valid Player");
				return false;
			}
            $player = $user->getGPPlayer();

            if($player instanceof GPPlayer) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $user->getUsername() . " is not Online");
                return false;
            } else {
				$player->getInventory()->addItem(clone $item);
				$player->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " Gave you the Item: " . $item->getName() . ", Id: " . $item->getId() . " Damage: " . $item->getDamage() . " and Count: " . $item->getCount());
				$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Gave the Item: " . $item->getName() . ", Id: " . $item->getId() . " Damage: " . $item->getDamage() . " and Count: " . $item->getCount() . " to " . $user->getUsername());
                return true;
            }
        }
        if(!$sender instanceof GPPlayer) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
			$sender->getInventory()->setItemInHand(clone $item);
			$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Gave yourself the Item: " . $item->getName() . ", Id: " . $item->getId() . " Damage: " . $item->getDamage() . " and Count: " . $item->getCount());
            return true;
        }
    }
}