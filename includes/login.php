<?php
declare(strict_types=1);

$siteName    = "Backstage 2.0";
$currentYear = date('Y');
$pageTitle   = "Log In — " . htmlspecialchars($siteName);

// Handle form submission
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId   = trim($_POST['user_id']   ?? '');
    $password = trim($_POST['password']  ?? '');

    if ($userId === '' || $password === '') {
        $error = 'Please enter both your User ID and password.';
    } else {
        // TODO: Replace with real authentication logic
        // e.g. query database, verify hashed password, start session, etc.
        // On success, redirect back to the main page:
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --purple:       #7A55C7;
            --purple-dark:  #6644AA;
            --purple-deeper:#5A3D96;
            --banner-height: 106px;
            --footer-height: 60px;
        }

        html, body {
            height: 100%;
            font-family: 'DM Sans', sans-serif;
        }

        body {
            background: #ffffff;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            color: #333;
        }

        /* ===== TOP BANNER ===== */
        header.top-banner {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--banner-height);
            background: linear-gradient(135deg, #6644AA 0%, #7A55C7 50%, #9060D4 100%);
            box-shadow: 0 4px 24px rgba(60, 30, 100, 0.35);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            border-bottom: 1px solid rgba(255,255,255,0.12);
        }

        .banner-top-row {
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 14px;
            flex: 1;
        }

        .banner-nav-row {
            display: flex;
            align-items: center;
            padding: 0 20px;
            height: 44px;
            border-top: 1px solid rgba(255,255,255,0.12);
            background: rgba(0,0,0,0.08);
        }

        /* Logo */
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            flex-shrink: 0;
        }

        .logo-icon {
            width: 42px; height: 42px;
            background: rgba(255,255,255,0.18);
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Playfair Display', serif;
            font-size: 21px; color: #fff;
            backdrop-filter: blur(4px);
            transition: background 0.2s, transform 0.2s;
        }

        .logo:hover .logo-icon {
            background: rgba(255,255,255,0.28);
            transform: scale(1.05);
        }

        .logo-text {
            font-family: 'Playfair Display', serif;
            font-size: 21px; color: #fff; letter-spacing: 0.5px;
        }

        /* Page label in banner */
        .banner-page-label {
            flex: 1;
            text-align: center;
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            color: rgba(255,255,255,0.85);
            letter-spacing: 0.5px;
        }

        /* Back link */
        .banner-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 14px;
            font-weight: 400;
            padding: 6px 14px;
            border-radius: 6px;
            transition: background 0.18s, color 0.18s;
            flex-shrink: 0;
        }

        .banner-back:hover {
            background: rgba(255,255,255,0.14);
            color: #fff;
        }

        .banner-back svg {
            width: 15px; height: 15px;
        }

        /* Nav row breadcrumb */
        .breadcrumb {
            font-size: 13px;
            color: rgba(255,255,255,0.65);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .breadcrumb a {
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            transition: color 0.15s;
        }

        .breadcrumb a:hover { color: #fff; }

        .breadcrumb span { color: rgba(255,255,255,0.45); }

        /* ===== MAIN ===== */
        main {
            flex: 1;
            margin-top: var(--banner-height);
            margin-bottom: var(--footer-height);
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        /* ===== LOGIN CARD ===== */
        .login-card {
            background: #faf8ff;
            border: 1px solid #e6d9ff;
            border-radius: 20px;
            padding: 44px 48px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 8px 36px rgba(147, 112, 219, 0.14);
            animation: cardIn 0.3s ease;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .login-card-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-icon {
            width: 62px; height: 62px;
            background: linear-gradient(135deg, var(--purple-dark), var(--purple));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px;
            box-shadow: 0 4px 18px rgba(147,112,219,0.35);
        }

        .login-icon svg {
            width: 28px; height: 28px;
            color: #fff;
        }

        .login-card-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            color: var(--purple-deeper);
            margin-bottom: 6px;
        }

        .login-card-header p {
            font-size: 14px;
            color: #8878a8;
            font-weight: 300;
        }

        /* ===== FORM ===== */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field-group label {
            font-size: 13px;
            font-weight: 500;
            color: var(--purple-deeper);
            letter-spacing: 0.3px;
        }

        .field-group label span.required {
            color: #c97de0;
            margin-left: 2px;
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrap svg.field-icon {
            position: absolute;
            left: 14px;
            width: 17px; height: 17px;
            color: #b09ed4;
            pointer-events: none;
            transition: color 0.2s;
        }

        .input-wrap input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid #ddd0f5;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14.5px;
            font-weight: 400;
            color: #3a2a5a;
            background: #fff;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-wrap input::placeholder {
            color: #c0b2d8;
            font-weight: 300;
        }

        .input-wrap input:focus {
            border-color: var(--purple);
            box-shadow: 0 0 0 3px rgba(147,112,219,0.15);
        }

        .input-wrap input:focus + svg.field-icon,
        .input-wrap:focus-within svg.field-icon {
            color: var(--purple);
        }

        /* Password toggle */
        .toggle-password {
            position: absolute;
            right: 13px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: #b09ed4;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }

        .toggle-password:hover { color: var(--purple); }
        .toggle-password svg { width: 18px; height: 18px; }

        /* Forgot password */
        .forgot-link {
            text-align: right;
            margin-top: -8px;
        }

        .forgot-link a {
            font-size: 12.5px;
            color: var(--purple);
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .forgot-link a:hover { opacity: 0.75; text-decoration: underline; }

        /* Error message */
        .error-msg {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fff0f5;
            border: 1px solid #f5c6d6;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 13.5px;
            color: #c0285a;
        }

        .error-msg svg { width: 16px; height: 16px; flex-shrink: 0; }

        /* Submit button */
        .btn-submit {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 13px 20px;
            background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple) 100%);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 500;
            letter-spacing: 0.3px;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 16px rgba(147,112,219,0.35);
            margin-top: 4px;
        }

        .btn-submit:hover {
            opacity: 0.92;
            transform: translateY(-1px);
            box-shadow: 0 6px 22px rgba(147,112,219,0.45);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit svg { width: 17px; height: 17px; }

        /* Divider */
        .card-divider {
            height: 1px;
            background: #ece4ff;
            margin: 4px 0;
        }

        /* Register link */
        .register-row {
            text-align: center;
            font-size: 13.5px;
            color: #8878a8;
        }

        .register-row a {
            color: var(--purple);
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.2s;
        }

        .register-row a:hover { opacity: 0.75; text-decoration: underline; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 700px) {
            :root { --banner-height: 70px; }
            .login-card { padding: 32px 24px 28px; }
        }

        @media (max-width: 440px) {
            .login-card { padding: 28px 18px 24px; }
        }

        /* ===== FOOTER ===== */
        footer.bottom-banner {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: var(--footer-height);
            background: linear-gradient(135deg, #6644AA 0%, #7A55C7 100%);
            border-top: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 -4px 20px rgba(60, 20, 100, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        footer.bottom-banner p {
            font-size: 13px;
            color: rgba(255,255,255,0.65);
            letter-spacing: 0.4px;
            text-align: center;
            font-weight: 300;
        }

        footer.bottom-banner p span {
            color: rgba(255,255,255,0.92);
            font-weight: 500;
        }
    </style>
</head>
<body>

    <!-- TOP BANNER -->
    <header class="top-banner" role="banner">
        <div class="banner-top-row">
            <a href="index.php" class="logo" aria-label="<?= htmlspecialchars($siteName) ?> Home">
                <img src="images/ONSITE-LOGO-New-Web-Small-White-300x139-1.png"
                     alt="<?= htmlspecialchars($siteName) ?> Logo"
                     class="logo-img" />
            </a>

            <div class="banner-page-label">Log In</div>

        </div>

    </header>

    <!-- MAIN -->
    <main>
        <div class="login-card" role="main">

            <div class="login-card-header">
                <div class="login-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h1>Welcome Back</h1>
                <p>Sign in to your <?= htmlspecialchars($siteName) ?> account</p>
            </div>

                <?php if ($error !== ''): ?>
                    <div class="error-msg" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form class="login-form" method="POST" action="login.php" novalidate>

                    <!-- User ID -->
                    <div class="field-group">
                        <label for="user_id">
                            User ID <span class="required" aria-hidden="true">*</span>
                        </label>
                        <div class="input-wrap">
                            <input
                                type="text"
                                id="user_id"
                                name="user_id"
                                placeholder="Enter your user ID"
                                value="<?= htmlspecialchars($_POST['user_id'] ?? '') ?>"
                                autocomplete="username"
                                required
                                autofocus
                            />
                            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="field-group">
                        <label for="password">
                            Password <span class="required" aria-hidden="true">*</span>
                        </label>
                        <div class="input-wrap">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                            />
                            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 11c0-1.1.9-2 2-2s2 .9 2 2v1h-4v-1zM5 11V9a7 7 0 0114 0v2M5 11h14a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2z"/>
                            </svg>
                            <button type="button" class="toggle-password" id="togglePwd" aria-label="Show password">
                                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Forgot password -->
                    <div class="forgot-link">
                        <a href="#forgot">Forgot your password?</a>
                    </div>

                    <div class="card-divider"></div>

                    <!-- Submit -->
                    <button type="submit" class="btn-submit">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h7a2 2 0 012 2v1"/>
                        </svg>
                        Log In
                    </button>

                </form>

                <div class="register-row" style="margin-top:20px;">
                    Don't have an account? <a href="#register">Sign up</a>
                </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="bottom-banner" role="contentinfo">
        <p>&copy; <?= $currentYear ?> <span><?= htmlspecialchars($siteName) ?></span>. All rights reserved.</p>
    </footer>

    <script>
        // Show/hide password toggle
        const toggleBtn = document.getElementById('togglePwd');
        const pwdInput  = document.getElementById('password');

        toggleBtn?.addEventListener('click', () => {
            const isPassword = pwdInput.type === 'password';
            pwdInput.type = isPassword ? 'text' : 'password';
            toggleBtn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            toggleBtn.querySelector('svg').style.opacity = isPassword ? '1' : '0.5';
        });
    </script>

</body>
</html>