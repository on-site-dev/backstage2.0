<?php
declare(strict_types=1);

session_start();

if(isset($_SESSION["siteName"])) {
    $siteName    = $_SESSION["siteName"];
}
else {
    $siteName    = "Backstage 2.0";
}
if(isset($_SESSION["studioName"])) {
    $studioName    = $_SESSION["studioName"];
}
else {
    $studioName  = "On-Site Studios";
}
$loggedInStatus = "In";
$currentYear = date('Y');
$pageTitle   = "Log In — " . htmlspecialchars($siteName);

// Handle form submission
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId   = trim($_POST['user_id']   ?? '');
    $password = trim($_POST['password']  ?? '');

    if ($userId === '' || $password === '') {
        $error = 'Please enter both your User ID and password.';
    } 
    else 
        {
        // TODO: Replace with real authentication logic
        // e.g. query database, verify hashed password, start session, etc.
        // On success, redirect back to the main page:
        $_SESSION["loggedInStatus"] = $loggedInStatus;
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
    <link rel="stylesheet" href="css/bs20loginstyles.css">

</head>
<body>

    <?php include_once 'includes/loginheader.php'; ?>

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
                        <a href="#forgot" onclick="event.preventDefault(); forgotPassword()">Forgot your password?</a>
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

                <div class="register-row" id="errMsg" style="margin-top:20px;background-color:transparent;border:2px solid transparent;border-radius:5px;">
                    
                </div>

        </div>
    </main>

    <?php include_once 'includes/footer.php'; ?>

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

        function forgotPassword()
        {
            const errMsg = document.getElementById('errMsg');

            try {
                errMsg.style.backgroundColor = 'pink';
                errMsg.textContent = 'Forgot Password';
            }
            catch (error) {
                alert(error.message);
            }

            return;

        }
        
    </script>

</body>
</html>