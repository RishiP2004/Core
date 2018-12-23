<?php

namespace core\utils;

class Math {
    public static function calculateBytes($toCheck) : int {
        $byteLimit = substr(trim($toCheck), 0, 1);

        switch(strtoupper(substr($toCheck, -1))) {
            case "P":
                return $byteLimit * pow(1024, 5);
            break;
            case "T":
                return $byteLimit * pow(1024, 4);
            break;
            case "G":
                return $byteLimit * pow(1024, 3);
            break;
            case "M":
                return $byteLimit * pow(1024, 2);
            break;
            case "K":
                return $byteLimit * 1024;
            break;
            case "B":
                return $byteLimit;
            break;
            default:
                return $byteLimit;
            break;
        }
    }

    public static function isOverloaded($toCheck) : bool {
        return memory_get_usage(true) > self::calculateBytes($toCheck);
    }

    public static function toArray(int $time) : array {
        if(is_int($time)) {
            return [
                floor($time / 3600),
                floor(($time / 60) - (floor($time / 3600) * 60)),
                floor($time % 60)
            ];
        } else {
            throw new \Exception("Expected integer, " . gettype($time) . " given");
        }
    }

    public static function getFormattedTime(int $time) : string {
        $time = self::toArray($time);
        return $time[0] . " hour(s), " . $time[1] . " minute(s), and " . $time[2] . " second(s)";
    }
	
    public static function validateObjectArray(array $array, string $class) : bool {
        foreach($array as $key => $item) {
            if(!$item instanceof $class) {
                throw new \TypeError("element \"$key\" is not an instance of $class");
            }
        }
        return true;
    }
}