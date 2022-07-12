<?php

declare(strict_types = 1);

namespace scoreboard;

use pocketmine\Server;
use pocketmine\player\Player;

use pocketmine\network\mcpe\protocol\{
	SetScorePacket,
	RemoveObjectivePacket,
	SetDisplayObjectivePacket
};
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;

class Scoreboard {
	/** @var string */
	private $objectiveName, $displayName, $displaySlot;
	/** @var int */
	private $sortOrder, $scoreboardId;
	/** @var bool */
	private $padding;
	
	const MAX_LINES = 15;

	public function __construct(string $title, int $action) {
		$this->displayName = $title;

		if($action === ScoreboardAction::CREATE) {
			if(ScoreboardManager::getId($title) === null) {
				$this->objectiveName = uniqid();
			} else {
				$this->objectiveName = ScoreboardManager::getId($title);
				$this->displaySlot = ScoreboardManager::getDisplaySlot($this->objectiveName);
				$this->sortOrder = ScoreboardManager::getSortOrder($this->objectiveName);
				$this->scoreboardId = ScoreboardManager::getScoreboardId($this->objectiveName);
			}
		} else {
			if(ScoreboardManager::getId($title) !== null) {
				$this->objectiveName = ScoreboardManager::getId($title);
				$this->displaySlot = ScoreboardManager::getDisplaySlot($this->objectiveName);
				$this->sortOrder = ScoreboardManager::getSortOrder($this->objectiveName);
				$this->scoreboardId = ScoreboardManager::getScoreboardId($this->objectiveName);
			} else {
				throw new \Exception("Scoreboard does not exist");
			}
		}
	}

	public function addDisplay(Player $player) : void {
		$pk = new SetDisplayObjectivePacket();
		$pk->displaySlot = $this->displaySlot;
		$pk->objectiveName = $this->objectiveName;
		$pk->displayName = $this->displayName;
		$pk->criteriaName = "dummy";
		$pk->sortOrder = $this->sortOrder;

		$player->getNetworkSession()->sendDataPacket($pk);
		ScoreboardManager::addViewer($this->objectiveName, $player->getName());

		if($this->displaySlot === "belowname") {
			$player->setScoreTag($this->displayName);
		}
	}

	public function removeDisplay(Player $player) : void {
		$pk = new RemoveObjectivePacket();
		$pk->objectiveName = $this->objectiveName;

		$player->getNetworkSession()->sendDataPacket($pk);
		ScoreboardManager::removeViewer($this->objectiveName, $player->getName());
	}

	public function setLine(int $line, string $message, bool $padding = true) : void {
		if(!ScoreboardManager::entryExist($this->objectiveName, ($line - 1)) && $line !== 1) {
			for($i = 1; $i <= ($line - 1); $i++) {
				if(!ScoreboardManager::entryExist($this->objectiveName, ($i - 1))) {
					$entry = new ScorePacketEntry();
					$entry->objectiveName = $this->objectiveName;
					$entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
					$entry->customName = str_repeat(" ", $i);
					$entry->score = self::MAX_LINES - $i;
					$entry->scoreboardId = ($this->scoreboardId + $i);
					$entry->entries[] = $entry;

					ScoreboardManager::addEntry($this->objectiveName, ($i - 1), $entry);
				}
			}
		}
		$entry = new ScorePacketEntry();
		$entry->objectiveName = $this->objectiveName;
		$entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
		$this->padding ? $entry->customName = str_pad($message, ((strlen($this->displayName) * 2) - strlen($message))) : $entry->customName = $message;
		$entry->score = self::MAX_LINES - $line;
		$entry->scoreboardId = ($this->scoreboardId + $line);
		$entry->entries[] = $entry;

		ScoreboardManager::addEntry($this->objectiveName, ($line - 1), $entry);

		foreach(ScoreboardManager::getViewers($this->objectiveName) as $name) {
			$player = Server::getInstance()->getPlayerByPrefix($name);

			if($player !== null) {
				$pk = new SetScorePacket();
				$pk->type = SetScorePacket::TYPE_CHANGE;

				foreach(ScoreboardManager::getEntries($this->objectiveName) as $index => $entry) {
					$pk->entries[$index] = $entry;
				}
				$player->getNetworkSession()->sendDataPacket($pk);
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

		foreach(ScoreboardManager::getViewers($this->objectiveName) as $name) {
			$player = Server::getInstance()->getPlayerByPrefix($name);
			$player->getNetworkSession()->sendDataPacket($pk);
		}
		ScoreboardManager::removeEntry($this->objectiveName, $line);
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
		foreach(ScoreboardManager::getViewers($this->objectiveName) as $name) {
			$player = Server::getInstance()->getPlayerByPrefix($name);
			$player->getNetworkSession()->sendDataPacket($pk);
		}
		ScoreboardManager::removeEntries($this->objectiveName);
	}

	public function create(string $displaySlot, int $sortOrder, bool $padding = true) : void {
		$this->displaySlot = $displaySlot;
		$this->sortOrder = $sortOrder;
		$this->padding = $padding;
		$this->scoreboardId = mt_rand(1, 100000);

		ScoreboardManager::registerScoreboard($this->objectiveName, $this->displayName, $this->displaySlot, $this->sortOrder, $this->scoreboardId);
	}

	public function rename(string $oldName, string $newName) : void {
		$this->displayName = $newName;

		ScoreboardManager::rename($oldName, $newName);

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

		foreach(ScoreboardManager::getEntries($this->objectiveName) as $index => $entry) {
			$pk3->entries[$index] = $entry;
		}
		foreach(ScoreboardManager::getViewers($this->objectiveName) as $name) {
			$player = Server::getInstance()->getPlayerByPrefix($name);
			$player->getNetworkSession()->sendDataPacket($pk);
			$player->getNetworkSession()->sendDataPacket($pk2);
			$player->getNetworkSession()->sendDataPacket($pk3);
		}
	}

	public function getViewers() : array {
		return ScoreboardManager::getViewers($this->objectiveName);
	}

	public function getEntries() : array {
		return ScoreboardManager::getEntries($this->objectiveName);
	}

	public function delete() : void {
		ScoreboardManager::unregisterScoreboard($this->objectiveName, $this->displayName);
	}
}
