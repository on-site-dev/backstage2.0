CREATE TABLE framepresets (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    preset_id  VARCHAR(64)  NOT NULL,
    label      VARCHAR(255) NOT NULL,
    icon       VARCHAR(8)   NOT NULL DEFAULT '?',
    url        VARCHAR(2048) NOT NULL,
    color      VARCHAR(32)  NOT NULL DEFAULT '#5A3D96',
    note       VARCHAR(255) NOT NULL DEFAULT '',
    default_w  SMALLINT UNSIGNED NOT NULL DEFAULT 700,
    default_h  SMALLINT UNSIGNED NOT NULL DEFAULT 480,
    tab        VARCHAR(32)  NOT NULL DEFAULT 'home',
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
