<?php
declare(strict_types = 1);

namespace core\social\task;

use core\social\command\DiscordCommand;

use pocketmine\scheduler\AsyncTask;

use pocketmine\Server;

use function base64_decode;
use function curl_exec;
use function curl_init;
use function curl_setopt;
use function json_decode;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_RETURNTRANSFER;
use const JSON_THROW_ON_ERROR;

class GetDiscordUsernameTask extends AsyncTask {
    private string $discordUID;
    private string $playerUsername;
    private string $senderUsername;
    private string $uuid;

    public function __construct(string $discordUID, string $playerUsername, string $senderUsername, string $uuid) {
        $this->discordUID = $discordUID;
        $this->playerUsername = $playerUsername;
        $this->senderUsername = $senderUsername;
        $this->uuid = $uuid;
    }

    public function onRun(): void {
        $curl_h = curl_init('https://discord.com/api/users/' . $this->discordUID);

        if ($curl_h === false) {
            $this->setResult("");
            return;
        }
        curl_setopt($curl_h, CURLOPT_HTTPHEADER,
            [
                'Authorization: Bot ' . base64_decode('T0RJd016RXlPRGMwTXpreE5ETTNNelF6LllFelY1US5mbDNlbTVKNGtkTE8zRXh5clJoMUZLSDV4N1U='),
                'User-Agent: InverseHCF (inversehcf.net, 1.0)',
            ]
        );
        curl_setopt($curl_h, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_h, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl_h, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl_h);
        $this->setResult($response);
    }

    public function onCompletion(): void {
    	$user = json_decode($this->getResult(), true, 512, JSON_THROW_ON_ERROR);
	    unset(DiscordCommand::$running[$this->uuid]);

	    $sender = Server::getInstance()->getPlayerExact($this->senderUsername);
	    if ($sender === null || !$sender->isOnline()) {
		    return;
	    }
	    if ($user === null) {
		    $sender->sendMessage("An unexpected error occurred while fetching Discord username.");
		    return;
	    }
	    $sender->sendMessage(Translation::getMessage("discordUser", ["name" => $this->playerUsername, "discord" => $user["username"] . "#" . $user["discriminator"]]));
    }
}