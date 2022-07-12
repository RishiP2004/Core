<?php

declare(strict_types = 1);

namespace core\database;

use core\Core;

use pocketmine\Server;

use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Database {
	private static DataConnector $database;

	public static function initialize() : void {
		Core::getInstance()->saveResource("mysql/queries.sql");

		try {
			self::$database = libasynql::create(Core::getInstance(), Core::getInstance()->getConfig()->get("database"), [
				"mysql" => "mysql/queries.sql"
			]);
		} catch(\Exception $exception) {
			Server::getInstance()->getLogger()->error(Core::ERROR_PREFIX . "Core Database connection failed: " . $exception->getMessage());
			Server::getInstance()->shutdown();
		}
	}

	public static function get() : DataConnector {
		return self::$database;
	}
}