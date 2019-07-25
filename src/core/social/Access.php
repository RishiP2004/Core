<?php

declare(strict_types = 1);

namespace core\social;

interface Access {
	const WEB_HOOK_URL = "https://discordapp.com/api/webhooks/603954996891090958/9i9KegHOHVrkPtH7Rh8WUmxsjd7NgWuB5GyXo60SowpKHKYk9t4HEz2QY6O5QARV5PWl";
	const USERNAME = "Athena";

	const KEY = "";
	const SECRET = "";
	const TOKEN = "";
	const TOKEN_SECRET = "";

	const PREFIX = [
		"discord" => ".d",
		"twitter" => ".t"
	];
}