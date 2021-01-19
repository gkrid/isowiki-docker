CREATE TABLE log (
    id INTEGER PRIMARY KEY,
    timestamp TEXT NOT NULL,
    user TEXT NOT NULL,
    message TEXT NOT NULL,
    message_html TEXT NOT NULL
);

CREATE TABLE config (
    key TEXT PRIMARY KEY,
    value TEXT NULL
);
