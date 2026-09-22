-- ============================================================
--  Seed: 20 Test Users — Backstage 2.0 / On-Site Studios
--  PostgreSQL
--
--  Password hashes: Argon2id  (m=65536, t=2, p=1)
--  Plaintext passwords are listed in the comment next to each
--  INSERT row — for development / QA use ONLY.
--  Remove or rotate all credentials before going to production.
--
--  Run after create_users_table.sql:
--    psql -U <user> -d <db> -f seed_users.sql
-- ============================================================

-- Optional: wipe existing seed data cleanly before re-seeding
-- DELETE FROM users WHERE user_id IN (
--   'janderson','mthompson','rgarcia','dwilliams','smartinez',
--   'bjohnson','lwhite','cjackson','aharris','tlee',
--   'pwalker','nroberts','oclark','jlewis','krobinson',
--   'ewright','dscott','ykim','fmartini','zpatel'
-- );

INSERT INTO users (
    user_id,
    email,
    password_hash,
    first_name,
    last_name,
    role,
    is_active,
    is_email_verified,
    created_at,
    last_login_at
) VALUES

-- ── Admins ────────────────────────────────────────────────────
(
    'janderson',                                         -- pw: Pr0ject$Admin!
    'james.anderson@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$5Yk5ps7YrUMPVVHeIxrGHA$5Yk5ps7YrUMPVVHeIxrGHJdwk249lAF2w8M4T5IgFGTliTmmztitQw9VUd4jGsYc',
    'James', 'Anderson',
    'admin', TRUE, TRUE,
    '2023-11-01 09:00:00+00',
    '2024-06-10 08:32:11+00'
),
(
    'ewright',                                           -- pw: Emma$Wr1ght!
    'emma.wright@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$9krv/s1tpwzL1roSIdwIUQ$9krv/s1tpwzL1roSIdwIUahz0PBnRHb3Kox2q+Jywsr2Su/+zW2nDMvWuhIh3AhR',
    'Emma', 'Wright',
    'admin', TRUE, TRUE,
    '2023-12-08 08:00:00+00',
    '2024-06-14 08:10:55+00'
),

-- ── Managers ──────────────────────────────────────────────────
(
    'mthompson',                                         -- pw: Mgr$ecure42!
    'maria.thompson@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$xCt9pZW1KyurBoudMuqOZg$xCt9pZW1KyurBoudMuqOZlTsqI37BbS9gD4AVGJVKrjEK32llbUrK6sGi50y6o5m',
    'Maria', 'Thompson',
    'manager', TRUE, TRUE,
    '2023-11-03 10:15:00+00',
    '2024-06-12 14:05:44+00'
),
(
    'rgarcia',                                           -- pw: RosaM$2024!
    'rosa.garcia@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$gzKrRZhjJwbV0opGmC5umw$gzKrRZhjJwbV0opGmC5umxuqWFVqCaBq72GxJJtNV8ODMqtFmGMnBtXSikaYLm6b',
    'Rosa', 'Garcia',
    'manager', TRUE, TRUE,
    '2023-11-05 11:00:00+00',
    '2024-05-28 09:10:00+00'
),
(
    'pwalker',                                           -- pw: Priya$W4lk!
    'priya.walker@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$Z4uXcqR46tOcgp11HuVKoQ$Z4uXcqR46tOcgp11HuVKoYuKkVkXDazKOkImxeLPPpZni5dypHjq05yCnXUe5Uqh',
    'Priya', 'Walker',
    'manager', TRUE, TRUE,
    '2023-11-25 08:00:00+00',
    '2024-06-13 09:55:01+00'
),
(
    'zpatel',                                            -- pw: Z0e$Pat3l!
    'zoe.patel@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$cNMbgQ3Ipmx2rZB/WgPG2w$cNMbgQ3Ipmx2rZB/WgPG23vEVqG3CcHANqbV7fY8141w0xuBDcimbHatkH9aA8bb',
    'Zoe', 'Patel',
    'manager', TRUE, TRUE,
    '2023-12-18 09:00:00+00',
    '2024-06-13 11:05:20+00'
),

-- ── Regular Users ─────────────────────────────────────────────
(
    'dwilliams',                                         -- pw: Derek$Pass1!
    'derek.williams@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$MUAAaOJznqiZx9ds/XuNiQ$MUAAaOJznqiZx9ds/XuNid5oz0zOQ0Uo6PrHiqqe3+UxQABo4nOeqJnH12z9e42J',
    'Derek', 'Williams',
    'user', TRUE, TRUE,
    '2023-11-08 08:45:00+00',
    '2024-06-01 07:55:22+00'
),
(
    'smartinez',                                         -- pw: Sara$secure9!  (email not verified)
    'sara.martinez@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$nNHpywwTtlkI5gpVTyX/xw$nNHpywwTtlkI5gpVTyX/xy0f5VLQ0UUxhZUGTPs6twKc0enLDBO2WQjmClVPJf/H',
    'Sara', 'Martinez',
    'user', TRUE, FALSE,
    '2023-11-10 13:30:00+00',
    NULL                                                 -- never logged in
),
(
    'bjohnson',                                          -- pw: BenJ$0hn!23
    'ben.johnson@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$UZs/YFC1Kje30mbNP7IRrQ$UZs/YFC1Kje30mbNP7IRrWOaXI/BMtjC1sI6XIiMgTVRmz9gULUqN7fSZs0/shGt',
    'Ben', 'Johnson',
    'user', TRUE, TRUE,
    '2023-11-12 09:00:00+00',
    '2024-06-09 11:20:33+00'
),
(
    'lwhite',                                            -- pw: L1saW$hite!
    'lisa.white@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$KLWDB/ErophZma88mJgiIQ$KLWDB/ErophZma88mJgiIeiqrMKPl1zbWT9FxkilaGEotYMH8SuimFmZrzyYmCIh',
    'Lisa', 'White',
    'user', TRUE, TRUE,
    '2023-11-15 14:00:00+00',
    '2024-06-11 16:44:50+00'
),
(
    'aharris',                                           -- pw: Amy$H4rris!
    'amy.harris@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$v1TWv0giB/R0XODHtkglDQ$v1TWv0giB/R0XODHtkglDdEZ7UpK/OrLR0LGXTeo78O/VNa/SCIH9HRc4Me2SCUN',
    'Amy', 'Harris',
    'user', TRUE, TRUE,
    '2023-11-20 09:30:00+00',
    '2024-06-08 08:02:19+00'
),
(
    'tlee',                                              -- pw: T0m$Lee2024!  (inactive — deactivated account)
    'tom.lee@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$x7/IufW8mKKda91Jz/H41g$x7/IufW8mKKda91Jz/H41vT52BzuGEVO+up6R7fy3knHv8i59byYop1r3UnP8fjW',
    'Tom', 'Lee',
    'user', FALSE, FALSE,
    '2023-11-22 11:00:00+00',
    NULL
),
(
    'nroberts',                                          -- pw: N1ck$R0b!
    'nick.roberts@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$ontQ0QmGLBa12IrgubUSOw$ontQ0QmGLBa12IrgubUSOwwGjIWkICbjXzHgLZ1Ec4iie1DRCYYsFrXYiuC5tRI7',
    'Nick', 'Roberts',
    'user', TRUE, TRUE,
    '2023-11-28 13:00:00+00',
    '2024-06-07 10:30:45+00'
),
(
    'jlewis',                                            -- pw: Jake$L3wis!
    'jake.lewis@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$rxCZYXrQ6R6VxUcov6CYVA$rxCZYXrQ6R6VxUcov6CYVOlGY4Tjn2Nusa44hCVV4FavEJlhetDpHpXFRyi/oJhU',
    'Jake', 'Lewis',
    'user', TRUE, TRUE,
    '2023-12-03 10:45:00+00',
    '2024-06-05 07:48:00+00'
),
(
    'krobinson',                                         -- pw: K4te$R0bin!  (inactive)
    'kate.robinson@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$+cdY3qtMIrzW/Xvoec3pQQ$+cdY3qtMIrzW/Xvoec3pQRIsJ4qeTZIxNv+EItAlZ0L5x1jeq0wivNb9e+h5zelB',
    'Kate', 'Robinson',
    'user', FALSE, TRUE,
    '2023-12-05 14:00:00+00',
    '2024-01-15 12:00:00+00'
),
(
    'dscott',                                            -- pw: D4vid$Sc0tt!  (email not verified)
    'david.scott@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$Tzf2V1LUBvIvJpsNAaiOqw$Tzf2V1LUBvIvJpsNAaiOq6GGj1BJmAz0rARJHi9wj+BPN/ZXUtQG8i8mmw0BqI6r',
    'David', 'Scott',
    'user', TRUE, FALSE,
    '2023-12-10 11:30:00+00',
    NULL
),
(
    'ykim',                                              -- pw: Yun4$K1m!
    'yuna.kim@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$J0QBg0VAfgjJtcAJlXFmMQ$J0QBg0VAfgjJtcAJlXFmMQ4wb5WVASvSDlmsiHT8N1MnRAGDRUB+CMm1wAmVcWYx',
    'Yuna', 'Kim',
    'user', TRUE, TRUE,
    '2023-12-12 09:15:00+00',
    '2024-06-06 15:22:30+00'
),

-- ── Viewers ───────────────────────────────────────────────────
(
    'cjackson',                                          -- pw: Chr1s$View!  (email not verified)
    'chris.jackson@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$ctUUB8smGlEUjSRPribgqw$ctUUB8smGlEUjSRPribgq6eFbnCdTxnI+PE0aMvupbhy1RQHyyYaURSNJE+uJuCr',
    'Chris', 'Jackson',
    'viewer', TRUE, FALSE,
    '2023-11-18 10:00:00+00',
    NULL
),
(
    'oclark',                                            -- pw: 0livia$C!ark  (email not verified)
    'olivia.clark@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$No7KZI/9IbvbUx953yd1Zw$No7KZI/9IbvbUx953yd1Zw48FP2VEAFEidquiMSR5Y42jspkj/0hu9tTH3nfJ3Vn',
    'Olivia', 'Clark',
    'viewer', TRUE, FALSE,
    '2023-12-01 09:00:00+00',
    NULL
),
(
    'fmartini',                                          -- pw: Fr4nco$M!  (email not verified)
    'franco.martini@onsitestudios.com',
    '$argon2id$v=19$m=65536,t=2,p=1$dgUwLTTd6jc30Ub3UoZfaQ$dgUwLTTd6jc30Ub3UoZfaT546+tp6fmze5qHFq2CR6p2BTAtNN3qNzfRRvdShl9p',
    'Franco', 'Martini',
    'viewer', TRUE, FALSE,
    '2023-12-15 10:00:00+00',
    NULL
);

-- ============================================================
--  Soft-delete one user to test the deleted_at workflow
-- ============================================================
UPDATE users
SET    deleted_at = '2024-03-01 12:00:00+00',
       is_active  = FALSE
WHERE  user_id = 'tlee';

-- ============================================================
--  Simulate a few failed login attempts on one account
-- ============================================================
UPDATE users
SET    failed_login_count = 3
WHERE  user_id = 'dscott';

-- ============================================================
--  Quick sanity check — run after inserting
-- ============================================================
-- SELECT id, user_id, display_name, role, is_active, is_email_verified,
--        last_login_at, deleted_at
-- FROM   users
-- ORDER  BY role, last_name;
