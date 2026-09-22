CREATE TABLE IF NOT EXISTS widgets (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED  DEFAULT NULL,  -- owning user, if logged in (NULL for guests)
    session_id  VARCHAR(64)   NOT NULL,      -- PHP session that owns this widget
    widget_uid  INT           NOT NULL,      -- client-side widget id (state.idCounter)
    preset_id   VARCHAR(64)   DEFAULT NULL,  -- e.g. 'google', 'custom_1720012345'
    label       VARCHAR(255)  DEFAULT NULL,
    url         TEXT          DEFAULT NULL,
    pos_x       DECIMAL(10,2) NOT NULL DEFAULT 0,
    pos_y       DECIMAL(10,2) NOT NULL DEFAULT 0,
    width       DECIMAL(10,2) NOT NULL DEFAULT 0,
    height      DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_session_widget (session_id, widget_uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
