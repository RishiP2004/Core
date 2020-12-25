<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use core\essentials\Essentials;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\item\ItemFactory;

use pocketmine\nbt\JsonNBTParser;

use pocketmine\nbt\tag\CompoundTag;

class Item extends PluginCommand {
    private $manager;

    public function __construct(Essentials $manager) {
        parent::__construct("item", Core::getInstance());

        $this->manager = $manager;

		$this->setAliases(["give"]);
        $this->setPermission("core.essentials.defaults.command.item");
        $this->setUsage("<player> <item [:damage]> [amount] [tags]");
        $this->setDescription("Add an Item to yours or another Player's Inventory");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /item " . $this->getUsage());
            return false;
        }
		$player = $sender->getServer()->getPlayer($args[0]);

		if(!$player instanceof CorePlayer) {
			$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
			return true;
		}
		try {
			$item = ItemFactory::fromString($args[1]);
		} catch(\InvalidArgumentException $exception) {
			$sender->sendMessage(Core::ERROR_PREFIX . $args[1] . " is not a valid Item");
			return true;
		}
		if(!isset($args[2])) {
			$item->setCount($item->getMaxStackSize());
		} else {
			$item->setCount((int) $args[2]);
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
				$sender->sendMessage(Core::ERROR_PREFIX . "Invalid Tag");
				return true;
			}
			$item->setNamedTag($tags);
		}
		if(!$player->getInventory()->canAddItem($item)) {
			$player->sendMessage(Core::PREFIX . $sender->getName() . " Gave you the Item: " . $item->getName() . ", Id: " . $item->getId() . " Damage: " . $item->getDamage() . " and Count: " . $item->getCount());
			$sender->sendMessage(Core::PREFIX . "Gave the Item: " . $item->getName() . ", Id: " . $item->getId() . " Damage: " . $item->getDamage() . " and Count: " . $item->getCount() . " to " . $player->getName());
			$player->getInventory()->getLevel()->dropItem($item, $player);
			return true;
		}
		$player->getInventory()->addItem(clone $item);
		$player->sendMessage(Core::PREFIX . $sender->getName() . " Gave you the Item: " . $item->getName() . ", Id: " . $item->getId() . " Damage: " . $item->getDamage() . " and Count: " . $item->getCount());
		$sender->sendMessage(Core::PREFIX . "Gave the Item: " . $item->getName() . ", Id: " . $item->getId() . " Damage: " . $item->getDamage() . " and Count: " . $item->getCount() . " to " . $player->getName());
		return true;
    }
}