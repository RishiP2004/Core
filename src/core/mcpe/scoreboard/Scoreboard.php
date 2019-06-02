<?php

declare(strict_types = 1);

namespace core\mcpe\scoreboard;

use core\Core;
use core\CorePlayer;

use pocketmine\network\mcpe\protocol\{
	SetScorePacket,
	RemoveObjectivePacket,
	SetDisplayObjectivePacket
};
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;

class Scoreboard {
	/**
	 * @var \core\mcpe\scoreboard\ScoreboardManager
	 */
	private $manager;
	/** @var string */
	private $objectiveName, $displayName, $displaySlot;
	/** @var int */
	private $sortOrder, $scoreboardId;
	/** @var bool */
	private $padding;

	public function __construct(string $title, int $action) {
		$this->displayName = $title;
		$manager = Core::getInstance()->getMCPE()->getScoreboardManager();

		$this->manager = $manager;

		if($action === ScoreboardAction::CREATE) {
			if($manager->getId($title) === null) {
				$this->objectiveName = uniqid();
			} else {
				Core::getInstance()->getLogger()->info("The scoreboard $title already exists ! Therefore we are going to use its already existing data.");

				$this->objectiveName = $manager->getId($title);
				$this->displaySlot = $manager->getDisplaySlot($this->objectiveName);
				$this->sortOrder = $manager->getSortOrder($this->objectiveName);
				$this->scoreboardId = $manager->getScoreboardId($this->objectiveName);
			}
		} else {
			if($manager->getId($title) !== null) {
				$this->objectiveName = $manager->getId($title);
				$this->displaySlot = $manager->getDisplaySlot($this->objectiveName);
				$this->sortOrder = $manager->getSortOrder($this->objectiveName);
				$this->scoreboardId = $manager->getScoreboardId($this->objectiveName);
			} else {
				Core::getInstance()->getLogger()->info("The scoreboard $title doesn't exist.");
			}
		}
	}

	public function addDisplay(CorePlayer $player) : void {
		$pk = new SetDisplayObjectivePacket();
		$pk->displaySlot = $this->displaySlot;
		$pk->objectiveName = $this->objectiveName;
		$pk->displayName = $this->displayName;
		$pk->criteriaName = "dummy";
		$pk->sortOrder = $this->sortOrder;

		$player->sendDataPacket($pk);
		$this->manager->addViewer($this->objectiveName, $player->getName());

		if($this->displaySlot === "belowname") {
			$player->setScoreTag($this->displayName);
		}
	}

	public function removeDisplay(CorePlayer $player) : void {
		$pk = new RemoveObjectivePacket();
		$pk->objectiveName = $this->objectiveName;

		$player->sendDataPacket($pk);
		$this->manager->removeViewer($this->objectiveName, $player->getName());
	}

	public function setLine(int $line, string $message, bool $padding = true) : void {
		if(!$this->manager->entryExist($this->objectiveName, ($line - 1)) && $line !== 1) {
			for($i = 1; $i <= ($line - 1); $i++) {
				if(!$this->manager->entryExist($this->objectiveName, ($i - 1))) {
					$entry = new ScorePacketEntry();
					$entry->objectiveName = $this->objectiveName;
					$entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
					$entry->customName = str_repeat(" ", $i);
					$entry->score = self::MAX_LINES - $i;
					$entry->scoreboardId = ($this->scoreboardId + $i);
					$pk->entries[] = $entry;

					$this->manager->addEntry($this->objectiveName, ($i - 1), $entry);
				}
			}
		}
		$entry = new ScorePacketEntry();
		$entry->objectiveName = $this->objectiveName;
		$entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
		$this->padding ? $entry->customName = str_pad($message, ((strlen($this->displayName) * 2) - strlen($message))) : $entry->customName = $message;
		$entry->score = self::MAX_LINES - $line;
		$entry->scoreboardId = ($this->scoreboardId + $line);
		$pk->entries[] = $entry;

		$this->manager->addEntry($this->objectiveName, ($line - 1), $entry);

		foreach($this->manager->getViewers($this->objectiveName) as $name) {
			$player = Core::getInstance()->getServer()->getPlayer($name);

			if($player !== null) {
				$pk = new SetScorePacket();
				$pk->type = SetScorePacket::TYPE_CHANGE;

				foreach($this->manager->getEntries($this->objectiveName) as $index => $entry) {
					$pk->entries[$index] = $entry;
				}
				$player->sendDataPacket($pk);
			}
		}
	}

	public function removeLine(int $line) : void {
		$pk = new SetScorePacket();
		$pk->type = SetScorePacket::TYPE_REMOVE;
		$entry = new ScorePacketEntry();
		$entry->objectiveName = $this->objectiveName;
		$entry->score = self::MAX_LINES - $line;
		$entry->scoreboardId = ($this->scoreboardId + $line);
		$pk->entries[] = $entry;

		foreach($this->manager->getViewers($this->objectiveName) as $name) {
			$player = Core::getInstance()->getServer()->getPlayer($name);
			$player->sendDataPacket($pk);
		}
		$this->manager->removeEntry($this->objectiveName, $line);
	}

	public function removeLines() : void {
		$pk = new SetScorePacket();
		$pk->type = SetScorePacket::TYPE_REMOVE;

		for($line = 0; $line <= self::MAX_LINES; $line++) {
			$entry = new ScorePacketEntry();
			$entry->objectiveName = $this->objectiveName;
			$entry->score = $line;
			$entry->scoreboardId = ($this->scoreboardId + $line);
			$pk->entries[] = $entry;
		}
		foreach($this->manager->getViewers($this->objectiveName) as $name) {
			$player = Core::getInstance()->getServer()->getPlayer($name);
			$player->sendDataPacket($pk);
		}
		$this->manager->removeEntries($this->objectiveName);
	}

	public function create(string $displaySlot, int $sortOrder, bool $padding = true) : void {
		$this->displaySlot = $displaySlot;
		$this->sortOrder = $sortOrder;
		$this->padding = $padding;
		$this->scoreboardId = mt_rand(1, 100000);

		$this->manager->registerScoreboard($this->objectiveName, $this->displayName, $this->displaySlot, $this->sortOrder, $this->scoreboardId);
	}

	public function rename(string $oldName, string $newName) : void {
		$this->displayName = $newName;

		$this->manager->rename($oldName, $newName);

		$pk = new RemoveObjectivePacket();
		$pk->objectiveName = $this->objectiveName;
		$pk2 = new SetDisplayObjectivePacket();
		$pk2->displaySlot = $this->displaySlot;
		$pk2->objectiveName = $this->objectiveName;
		$pk2->displayName = $this->displayName;
		$pk2->criteriaName = "dummy";
		$pk2->sortOrder = $this->sortOrder;
		$pk3 = new SetScorePacket();
		$pk3->type = SetScorePacket::TYPE_CHANGE;

		foreach($this->manager->getEntries($this->objectiveName) as $index => $entry) {
			$pk3->entries[$index] = $entry;
		}
		foreach($this->manager->getViewers($this->objectiveName) as $name) {
			$player = Core::getInstance()->getServer()->getPlayer($name);
			$player->sendDataPacket($pk);
			$player->sendDataPacket($pk2);
			$player->sendDataPacket($pk3);
		}
	}

	public function getViewers() : array {
		return $this->manager->getViewers($this->objectiveName);
	}

	public function getEntries() : array {
		return $this->manager->getEntries($this->objectiveName);
	}

	public function delete() : void {
		$this->manager->unregisterScoreboard($this->objectiveName, $this->displayName);
	}
}
