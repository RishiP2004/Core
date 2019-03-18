-- #!mysql
-- #{ stats

-- # { init
CREATE TABLE IF NOT EXISTS stats (
    xuid VARCHAR(16) PRIMARY KEY,
    registerDate VARCHAR(11),
    username VARCHAR(16),
    ip VARCHAR(15),
    locale VARCHAR(6) DEFAULT 'eng',
	  coins BIGINT DEFAULT 0,
    rank VARCHAR(16),
	  permissions TEXT,
		server VARCHAR(32)
)
-- # }

-- # { get
SELECT xuid, registerDate, username, ip, locale, coins, rank, permissions, server FROM stats;
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
-- #    :server string
-- #    :xuid string
UPDATE stats SET username = :username, ip = :ip, locale = :locale, coins = :coins, rank = :rank, permissions = :permissions, server = :server WHERE xuid = :xuid;
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
    registerDate VARCHAR(11),
    type INT(1),
    username VARCHAR(16),
    sentencer VARCHAR(16),
    reason TEXT,
    expires TIME
)

-- # { get
SELECT xuid, registerDate, type, username, sentencer, reason, expires FROM stats;
-- # }

-- # }

-- # { register
-- #    :xuid string
-- #    :registerDate string
-- #    :type int
-- #    :username string
-- #    :sentencer string
-- #    :reason string
-- #    :expires int
INSERT INTO sentences (
    xuid,
    registerDate,
    type,
    username,
    sentencer,
    reason,
    expires
) VALUES (
    :xuid,
    :registerDate,
    :type,
    :username,
    :sentencer,
    :reason,
    :expires
)
-- # }

-- # { delete
-- # 	:xuid string
DELETE FROM sentences WHERE xuid = :xuid;
-- # }

-- #}