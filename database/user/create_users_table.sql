-- ============================================================
--  Users Table — Backstage 2.0 / On-Site Studios
--  PostgreSQL
-- ============================================================

CREATE TABLE users (
    -- Primary key
    id                  SERIAL          PRIMARY KEY,

    -- Identity
    user_id             VARCHAR(50)     NOT NULL UNIQUE,          -- login handle / username
    email               VARCHAR(255)    NOT NULL UNIQUE,
    password_hash       VARCHAR(255)    NOT NULL,                 -- bcrypt / argon2 hash

    -- Profile
    first_name          VARCHAR(100)    NOT NULL DEFAULT '',
    last_name           VARCHAR(100)    NOT NULL DEFAULT '',
    display_name        VARCHAR(150)    GENERATED ALWAYS AS (
                            TRIM(first_name || ' ' || last_name)
                        ) STORED,
    avatar_url          TEXT,

    -- Role & status
    role                VARCHAR(50)     NOT NULL DEFAULT 'user'
                            CHECK (role IN ('admin', 'manager', 'user', 'viewer')),
    is_active           BOOLEAN         NOT NULL DEFAULT TRUE,
    is_email_verified   BOOLEAN         NOT NULL DEFAULT FALSE,

    -- Auth helpers
    email_verify_token  VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_at   TIMESTAMPTZ,
    last_login_at       TIMESTAMPTZ,
    failed_login_count  SMALLINT        NOT NULL DEFAULT 0,
    locked_until        TIMESTAMPTZ,

    -- Audit timestamps
    created_at          TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at          TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    deleted_at          TIMESTAMPTZ                               -- soft-delete
);

-- ============================================================
--  Indexes
-- ============================================================

CREATE INDEX idx_users_email        ON users (email);
CREATE INDEX idx_users_user_id      ON users (user_id);
CREATE INDEX idx_users_role         ON users (role);
CREATE INDEX idx_users_is_active    ON users (is_active) WHERE is_active = TRUE;
CREATE INDEX idx_users_deleted_at   ON users (deleted_at) WHERE deleted_at IS NULL;

-- ============================================================
--  Auto-update updated_at trigger
-- ============================================================

CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_users_updated_at
    BEFORE UPDATE ON users
    FOR EACH ROW
    EXECUTE FUNCTION set_updated_at();

-- ============================================================
--  Example: seed an admin account
--  (replace the hash with a real bcrypt/argon2 value)
-- ============================================================

-- INSERT INTO users (user_id, email, password_hash, first_name, last_name, role, is_email_verified)
-- VALUES ('admin', 'admin@onsitestudios.com', '$2y$12$...', 'Site', 'Admin', 'admin', TRUE);
