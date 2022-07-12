<?php

declare(strict_types = 1);

namespace core\social;

use core\Core;
use core\database\Database;
use core\utils\Manager;

use core\social\command\DiscordCommand;

use CortexPE\DiscordWebHookAPI\{
	Message,
	Webhook,
	Embed
};
use hcf\command\types\DiscordSeeCommand;

class SocialManager extends Manager implements Access {
	//TODO
	public static $instance = null;

	private static $embeds = [];
	
    public function init() : void {
    	self::$instance = $this;

		//Core::getInstance()->getDatabase()->executeGeneric("discord.init");
		if(!empty(self::WEB_HOOK_URL)) {
			$this->registerPermissions([
				"discord.command" => [
					"default" => "op",
					"description" => "Discord command"
				],
			]);
			$this->registerCommands($this, new DiscordCommand(Core::getInstance(), "discord", "Discord command"));
			//$this->registerCommand(DiscordSeeCommand::class, new DiscordSeeCommand(Core::getInstance(), "discordsee", "See a linked Discord account"));
			//$this->registerCommand(LinkCommand::class, new LinkCommand(Core::getInstance(), "link", "Link your Discord account"));
		}
		$this->registerListener(new SocialListener($this), Core::getInstance());
    }
    
    public static function getInstance() : self {
		return self::$instance;
	}

	public function sendToDiscord(Message $message) {
    	if(!empty($this))
		$webHook = new Webhook(self::WEB_HOOK_URL);

		$webHook->send($message);
	}

	public static function sendLog($channel, string $description, $color): void {
		self::createEmbed($channel, null, $description, $color);
	}

	public static function sendPunishment(string $punishment, string $player, string $staff, string $duration, string $reason) : void{
		$webhook = new Webhook(self::PUNISHMENT_LOG);
		$msg = new Message();
		$embed = new Embed();
		$embed->setColor(0xEA6C15);
		$embed->setTitle($punishment);
		$embed->addField("Player", $player);
		$embed->addField("Moderator", $staff);
		$embed->addField("Reason", $reason);
		$embed->addField("Duration", $duration);
		$msg->addEmbed($embed);
		$webhook->send($msg);
	}

	public static function sendPardon(string $punishment, string $player, string $staff) : void{
		$webhook = new Webhook(self::PUNISHMENT_LOG);
		$msg = new Message();
		$embed = new Embed();
		$embed->setColor( 0x5EAA54 );
		$embed->setTitle($punishment);
		$embed->addField("Player", $player);
		$embed->addField("Moderator", $staff);
		$msg->addEmbed($embed);
		$webhook->send($msg);
	}

	public static function createEmbed(string $channel, ?string $title, ?string $description, ?int $color = 0xEA6C15): void {
		$embed = new Embed();
		$embed->setColor($color);
		if ($title) {
			$embed->setTitle($title);
		}
		$embed->setDescription($description);
		self::$embeds[] = $embed;
	}

	public function getDiscordId(string $uuid, callable $callback) : void {
		Database::get()->executeSelect("discord.get", ['uuid' => $uuid], function(array $rows) use($callback) {
			if(count($rows) === 0) {
				$callback(null);
				return;
			}
			$data = $rows[0];
			$callback($data["discordUUID"], $data["code"]);
		});
	}
}