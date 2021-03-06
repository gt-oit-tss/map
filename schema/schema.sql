PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS computers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,                    -- Autogenerated ROWID
  name TEXT NOT NULL,                                      -- Computer's hostname (e.g., TSS-LWC001)
  location TEXT NOT NULL,                                  -- Location of the computer - either 'lwc' or 'lec'
  status TEXT DEFAULT 'unavailable' NOT NULL,              -- Can be 'available', 'unavailable', or 'in-use'
  last_updated DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL -- Timestamp of the last status change - UTC
);

CREATE VIEW status_counts
  AS SELECT location, status, COUNT(*) AS count
  FROM computers
  GROUP BY location, status;

PRAGMA user_version = 1;