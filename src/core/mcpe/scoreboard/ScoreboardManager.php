<?php

declare(strict_types = 1);

namespace core\mcpe\scoreboard;

use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;

class ScoreboardManager {
	/** @var array */
	private $entries, $scoreboards, $displaySlots, $sortOrders, $ids, $viewers;

	public function getEntries(string $objectiveName) : array {
		return $this->entries[$objectiveName];
	}

	public function entryExist(string $objectiveName, int $line) : bool {
		return isset($this->entries[$objectiveName][$line]);
	}

	public function addEntry(string $objectiveName, int $line, ScorePacketEntry $entry) {
		$this->entries[$objectiveName][$line] = $entry;
	}

	public function removeEntry(string $objectiveName, int $line) {
		unset($this->entries[$objectiveName][$line]);
	}

	public function removeEntries(string $objectiveName) {
		$this->entries[$objectiveName] = null;
	}

	public function registerScoreboard(string $objectiveName, string $displayName, string $displaySlot, int $sortOrder, int $scoreboardId) : void {
		$this->entries[$objectiveName] = null;
		$this->scoreboards[$displayName] = $objectiveName;
		$this->displaySlots[$objectiveName] = $displaySlot;
		$this->sortOrders[$objectiveName] = $sortOrder;
		$this->ids[$objectiveName] = $scoreboardId;
		$this->viewers[$objectiveName] = [];
	}

	public function getScoreboardName(string $displayName) : ?string {
		return $this->scoreboards[$displayName] ?? null;
	}

	public function getScoreboardId(string $objectiveName) : int {
		return $this->ids[$objectiveName];
	}

	public function getId(string $displayName) {
		return $this->scoreboards[$displayName] ?? null;
	}

	public function getDisplaySlot(string $objectiveName) : string {
		return $this->displaySlots[$objectiveName];
	}

	public function getSortOrder(string $objectiveName) : int {
		return $this->sortOrders[$objectiveName];
	}

	public function getViewers(string $objectiveName) : ?array {
		return $this->viewers[$objectiveName] ?? null;
	}

	public function addViewer(string $objectiveName, string $playerName) : void {
		if(!in_array($playerName, $this->viewers[$objectiveName])) {
			array_push($this->viewers[$objectiveName], $playerName);
		}
	}

	public function removeViewer(string $objectiveName, string $playerName) : void {
		if(in_array($playerName, $this->viewers[$objectiveName])) {
			if(($key = array_search($playerName, $this->viewers[$objectiveName])) !== false) {
				unset($this->viewers[$objectiveName][$key]);
			}
		}
	}

	public function removePotentialViewer(string $playerName) : void {
		foreach($this->viewers as $name => $data) {
			if(in_array($playerName, $data)) {
				if(($key = array_search($playerName, $data)) !== false) {
					unset($this->viewers[$name][$key]);
				}
			}
		}
	}

	public function rename(string $oldName, string $newName) : void {
		$this->scoreboards[$newName] = $this->scoreboards[$oldName];

		unset($this->scoreboards[$oldName]);
	}

	public function unregisterScoreboard(string $objectiveName, string $displayName) : void {
		unset($this->entries[$objectiveName]);
		unset($this->scoreboards[$displayName]);
		unset($this->displaySlots[$objectiveName]);
		unset($this->sortOrders[$objectiveName]);
		unset($this->ids[$objectiveName]);
		unset($this->viewers[$objectiveName]);
	}
}