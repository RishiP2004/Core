-- #!mysql
-- #{stats

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

-- # { get
SELECT xuid, registerDate, username, ip, locale, coins, rank, permissions, server FROM stats;
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
-- # 	:xuid string
DELETE FROM stats WHERE xuid = :xuid;
-- # }

-- #}

-- #{sentenced

-- #}