<?php

namespace core\mcpe;

class MinecraftQuery {
    const STATISTIC = 0x00;
    const HANDSHAKE = 0x09;

    private $socket;

    private $info = [];

	public function __construct(string $ip, int $port, int $timeout = 3) {
		if(!is_int($timeout) or $timeout < 0) {
            throw new \InvalidArgumentException("Timeout must be an integer");
        }
        $this->socket = @fsockopen('udp://' . $ip, $port, $error, $errorReason, $timeout);
		
        if($error or $this->socket === false) {
            throw new MinecraftQueryException("Could not create socket: " . $errorReason);
        }
        stream_set_timeout($this->socket, $timeout);
        stream_set_blocking($this->socket, true);

        try {
            $challenge = $this->getChallenge();

            $this->getStatus($challenge);
        } catch(MinecraftQueryException $exception) {
            FClose($this->socket);
            throw new MinecraftQueryException($exception->getMessage());
        }
        FClose($this->socket);
	}

    public function getInfo() {
        return $this->info;
    }

    private function getChallenge() {
        $data = $this->writeData(self::HANDSHAKE);

        if($data === false) {
            throw new MinecraftQueryException("Failed to receive challenge");
        }
        return Pack("N", $data);
    }

    private function getStatus($challenge) {
        $data = $this->writeData(self::STATISTIC, $challenge . Pack("c*", 0x00, 0x00, 0x00, 0x00));

        if(!$data) {
            throw new MinecraftQueryException("Failed to receive status");
        }
        $last = '';
        $info = [];
        $data = substr($data, 11);
        $data = explode("\x00\x00\x01player_\x00\x00", $data);

        if(count($data) !== 2) {
            throw new MinecraftQueryException("Failed to parse server's response");
        }
        $players = substr($data[1], 0, -2);
        $data = explode("\x00", $data[0]);
        $keys = [
            "hostname" => "hostName",
            "gametype" => "gameType",
            "version" => "version",
            "plugins" => "plugins",
            "map" => "map",
            "numplayers" => "players",
            "maxplayers" => "maxPlayers",
            "hostport" => "hostPort",
            "hostip" => "hostIp",
            "game_id" => "gameName"
        ];
        foreach($data as $key => $value) {
            if(~$key & 1) {
                if(!array_key_exists($value, $keys)) {
                    $last = false;
                    continue;
                }
                $last = $keys[$value];
                $info[$last] = "";
            } else if($last != false) {
                $last[$last] = mb_convert_encoding($value, "UTF-8");
            }
        }
        $info["numPlayers"] = intval($info["players"]);
        $info["maxPlayers"] = intval($info["maxPlayers"]);
        $info["hostPort"] = intval($info["hostPort"]);
        $info["players"] = explode("\x00", $players);

        if($info["plugins"]) {
            $data = explode(": ", $info["plugins"], 2);
            $info["rawPlugins"] = $info["plugins"];
            $info["software"] = $data[0];

            if(count($data) === 2) {
                $info["plugins"] = explode("; ", $data[1]);
            }
        } else {
            $info["software"] = "vanilla";
        }
        $this->info = $info;
    }

    private function writeData($command, string $append = "") {
        $command = Pack("c*", 0xFE, 0xFD, $command, 0x01, 0x02, 0x03, 0x04) . $append;
        $length = strlen($command);

        if($length !== fwrite($this->socket, $command, $length)) {
            throw new MinecraftQueryException("Failed to write on socket");
        }
        $data = fread($this->socket, 4096);

        if($data === false) {
            throw new MinecraftQueryException("Failed to read from socket");
        }
        if(strlen($data) < 5 or $data[0] != $command[2]) {
            return false;
        }
        return substr($data, 5);
    }
}
