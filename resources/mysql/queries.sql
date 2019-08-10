-- #!mysql
-- #{ stats

-- # { init
CREATE TABLE IF NOT EXISTS stats (
    xuid VARCHAR(16) PRIMARY KEY,
    registerDate VARCHAR(18),
    username VARCHAR(16),
    ip VARCHAR(15),
    locale VARCHAR(6) DEFAULT 'en_us',
	  coins BIGINT DEFAULT 0,
	  balance BIGINT DEFAULT 0,
    rank VARCHAR(16) DEFAULT 'Player',
    permissions TEXT,
    cheatHistory TEXT,
		server VARCHAR(32) DEFAULT NULL
)
-- # }

-- # { get
-- # 	:key string
SELECT * FROM stats WHERE xuid = :key OR username = :key;
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
-- #    :balance int
-- #    :rank string
-- #    :permissions string
-- #    :cheatHistory string
-- #    :server string | null
-- #    :xuid string
UPDATE stats SET username = :username, ip = :ip, locale = :locale, coins = :coins, balance = :balance, rank = :rank, permissions = :permissions, cheatHistory = :cheatHistory, server = :server WHERE xuid = :xuid;
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