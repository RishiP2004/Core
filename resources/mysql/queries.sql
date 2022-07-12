-- #!mysql
-- #{ player

-- # { init
CREATE TABLE IF NOT EXISTS stats (
    xuid VARCHAR(16) PRIMARY KEY,
    registerDate VARCHAR(18),
    username VARCHAR(16),
    ip VARCHAR(15),
    locale VARCHAR(6) DEFAULT 'en_us',
	coins BIGINT DEFAULT 0,
    rank VARCHAR(16) DEFAULT 0,
    permissions TEXT,
    cheatHistory TEXT,
	server VARCHAR(32) DEFAULT NULL,
	dm INT DEFAULT 0
)
-- # }

-- # { get
-- # 	:key string
SELECT * FROM stats WHERE xuid = :key OR username = :key;
-- # }

-- # { allCoins
SELECT coins, username FROM stats;
-- # }

-- # { getAll
SELECT * FROM stats;
-- # }

-- # { register
-- #    :xuid string
-- #    :registerDate string
-- #    :username string
-- #    :ip string
-- #    :locale string
INSERT INTO stats (
    xuid,
    registerDate,
    username,
    ip,
    locale
) VALUES (
    :xuid,
    :registerDate,
    :username,
    :ip,
    :locale
)
-- # }

-- # { update
-- #    :username string
-- #    :ip string
-- #    :locale string
-- #    :coins int
-- #    :rank string
-- #    :permissions string
-- #    :cheatHistory string
-- #    :server string | null
-- #    :dm int
-- #    :xuid string
UPDATE stats SET username = :username, ip = :ip, locale = :locale, coins = :coins, rank = :rank, permissions = :permissions, cheatHistory = :cheatHistory, server = :server, dm = :dm WHERE xuid = :xuid;
-- # }

-- # { delete
-- # 	  :xuid string
DELETE FROM stats WHERE xuid = :xuid;
-- # }

-- #}

-- #{ sentences

-- # { init
CREATE TABLE IF NOT EXISTS sentences (
    xuid VARCHAR(16) PRIMARY KEY,
    registerDate VARCHAR(18),
	  listType VARCHAR(4),
    type INT(1),
    username VARCHAR(16),
    sentencer VARCHAR(16),
    reason TEXT,
    expires VARCHAR(18) DEFAULT NULL
)
-- # }

-- # { get
SELECT xuid, registerDate, listType, type, username, sentencer, reason, expires FROM sentences;
-- # }

-- # { register
-- #    :xuid string
-- #    :registerDate string
-- #    :listType string
-- #    :type int
-- #    :username string
-- #    :sentencer string
-- #    :reason string
-- #    :expires string | null
INSERT INTO sentences (
    xuid,
    registerDate,
	  listType,
    type,
    username,
    sentencer,
    reason,
    expires
) VALUES (
    :xuid,
    :registerDate,
	  :listType,
    :type,
    :username,
    :sentencer,
    :reason,
    :expires
)
-- # }

-- # { delete
-- # 	:xuid string,
-- #  :listType string,
-- #  :type string
DELETE FROM sentences WHERE xuid = :xuid AND listType = :listType AND type = :type;
-- # }
-- #}

-- #{ discord

-- # { init
CREATE TABLE IF NOT EXISTS discord (
    xuid VARCHAR(16) PRIMARY KEY,
)
-- # }

-- # { get
SELECT xuid FROM discord;
-- # }

-- # { register
-- #    :xuid string
INSERT INTO discord (
    xuid
) VALUES (
    :xuid
)
-- # }

-- # { unlink
-- #  :xuid string,
DELETE FROM discord WHERE xuid = :xuid;
-- # }

-- #}