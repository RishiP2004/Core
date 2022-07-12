<?php

declare(strict_types = 1);

namespace core\social;

interface Access {
	const WEB_HOOK_URL = "";//"https://discordapp.com/api/webhooks/603954996891090958/9i9KegHOHVrkPtH7Rh8WUmxsjd7NgWuB5GyXo60SowpKHKYk9t4HEz2QY6O5QARV5PWl";
	const PUNISHMENT_LOG = "";//"https://discordapp.com/api/webhooks/603954996891090958/9i9KegHOHVrkPtH7Rh8WUmxsjd7NgWuB5GyXo60SowpKHKYk9t4HEz2QY6O5QARV5PWl";\
	const JOIN_LOG = "";

	const USERNAME = "Athena";

	const PREFIX = [
		"discord" => ".d",
	];
}