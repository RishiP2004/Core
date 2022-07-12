<?php

declare(strict_types = 1);

namespace scoreboard;

use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;

class ScoreboardManager {
	/** @var array */
	private static $entries, $scoreboards, $displaySlots, $sortOrders, $ids, $viewers;

	public static function getEntries(string $objectiveName) : array {
		return self::$entries[$objectiveName];
	}

	public static function entryExist(string $objectiveName, int $line) : bool {
		return isset(self::$entries[$objectiveName][$line]);
	}

	public static function addEntry(string $objectiveName, int $line, ScorePacketEntry $entry) {
		self::$entries[$objectiveName][$line] = $entry;
	}

	public static function removeEntry(string $objectiveName, int $line) {
		unset(self::$entries[$objectiveName][$line]);
	}

	public static function removeEntries(string $objectiveName) {
		self::$entries[$objectiveName] = null;
	}

	public static function registerScoreboard(string $objectiveName, string $displayName, string $displaySlot, int $sortOrder, int $scoreboardId) : void {
		self::$entries[$objectiveName] = null;
		self::$scoreboards[$displayName] = $objectiveName;
		self::$displaySlots[$objectiveName] = $displaySlot;
		self::$sortOrders[$objectiveName] = $sortOrder;
		self::$ids[$objectiveName] = $scoreboardId;
		self::$viewers[$objectiveName] = [];
	}

	public static function getScoreboardName(string $displayName) : ?string {
		return self::$scoreboards[$displayName] ?? null;
	}

	public static function getScoreboardId(string $objectiveName) : int {
		return self::$ids[$objectiveName];
	}

	public static function getId(string $displayName) {
		return self::$scoreboards[$displayName] ?? null;
	}

	public static function getDisplaySlot(string $objectiveName) : string {
		return self::$displaySlots[$objectiveName];
	}

	public static function getSortOrder(string $objectiveName) : int {
		return self::$sortOrders[$objectiveName];
	}

	public static function getViewers(string $objectiveName) : ?array {
		return self::$viewers[$objectiveName] ?? null;
	}

	public static function addViewer(string $objectiveName, string $playerName) : void {
		if(!in_array($playerName, self::$viewers[$objectiveName])) {
			array_push(self::$viewers[$objectiveName], $playerName);
		}
	}

	public static function removeViewer(string $objectiveName, string $playerName) : void {
		if(in_array($playerName, self::$viewers[$objectiveName])) {
			if(($key = array_search($playerName, self::$viewers[$objectiveName])) !== false) {
				unset(self::$viewers[$objectiveName][$key]);
			}
		}
	}

	public static function removePotentialViewer(string $playerName) : void {
		if(!empty(self::$viewers)) {
			foreach(self::$viewers as $name => $data) {
				if(in_array($playerName, $data)) {
					if(($key = array_search($playerName, $data)) !== false) {
						unset(self::$viewers[$name][$key]);
					}
				}
			}
		}
	}

	public static function rename(string $oldName, string $newName) : void {
		self::$scoreboards[$newName] = self::$scoreboards[$oldName];

		unset(self::$scoreboards[$oldName]);
	}

	public static function unregisterScoreboard(string $objectiveName, string $displayName) : void {
		unset(self::$entries[$objectiveName]);
		unset(self::$scoreboards[$displayName]);
		unset(self::$displaySlots[$objectiveName]);
		unset(self::$sortOrders[$objectiveName]);
		unset(self::$ids[$objectiveName]);
		unset(self::$viewers[$objectiveName]);
	}
}