<?php
declare(strict_types=1);

include_once 'config.php';

/* =====================================================================
   send_sms.php
   Sends a single SMS message to a phone number via the Twilio REST API.

   Requires these constants to be defined (put them in config.php,
   next to your DB_* constants):

       define('TWILIO_ACCOUNT_SID', 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
       define('TWILIO_AUTH_TOKEN',  'your_auth_token_here');
       define('TWILIO_FROM_NUMBER', '+15551234567'); // a number on your Twilio account

   Sign up / get these values at https://www.twilio.com/console
   No Composer / Twilio SDK is required — this uses cURL directly
   against Twilio's REST API.
   ===================================================================== */

$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json') && $_SERVER['REQUEST_METHOD'] === 'POST');

function jsonOut(array $payload, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

/* ---------------------------------------------------------------------
   Normalize a phone number to E.164-ish format (+1XXXXXXXXXX for US
   10-digit input). Adjust this if you need international support
   beyond the US/Canada.
--------------------------------------------------------------------- */
function normalizePhone(string $raw): ?string {
    $digits = preg_replace('/\D+/', '', $raw);
    if ($digits === null) return null;

    if (strlen($digits) === 10) {
        return '+1' . $digits;
    }
    if (strlen($digits) === 11 && $digits[0] === '1') {
        return '+' . $digits;
    }
    if (str_starts_with($raw, '+') && strlen($digits) >= 8) {
        return '+' . $digits;
    }
    return null;
}

/* ---------------------------------------------------------------------
   Send via Twilio's REST API using cURL (no SDK dependency).
   Returns ['success'=>bool,'message'=>string,'sid'=>?string]
--------------------------------------------------------------------- */
function sendSms(string $to, string $body): array {
    if (!defined('TWILIO_ACCOUNT_SID') || !defined('TWILIO_AUTH_TOKEN') || !defined('TWILIO_FROM_NUMBER')) {
        return ['success' => false, 'message' => 'SMS is not configured. Add TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN, and TWILIO_FROM_NUMBER to config.php.', 'sid' => null];
    }

    $sid   = TWILIO_ACCOUNT_SID;
    $token = TWILIO_AUTH_TOKEN;
    $url   = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_USERPWD        => "{$sid}:{$token}",
        CURLOPT_POSTFIELDS     => http_build_query([
            'From' => TWILIO_FROM_NUMBER,
            'To'   => $to,
            'Body' => $body,
        ]),
        CURLOPT_TIMEOUT        => 15,
    ]);

    $response  = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        return ['success' => false, 'message' => 'Request to Twilio failed: ' . $curlError, 'sid' => null];
    }

    $decoded = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300 && isset($decoded['sid'])) {
        return ['success' => true, 'message' => 'Message sent.', 'sid' => $decoded['sid']];
    }

    $errMsg = $decoded['message'] ?? ('Twilio returned HTTP ' . $httpCode);
    return ['success' => false, 'message' => $errMsg, 'sid' => null];
}

/* ---------------------------------------------------------------------
   Route: handle POST (send)
--------------------------------------------------------------------- */
$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawBody = file_get_contents('php://input');
    $posted  = $_POST;
    if (empty($posted) && $rawBody !== '') {
        $asJson = json_decode($rawBody, true);
        if (is_array($asJson)) $posted = $asJson;
    }

    $phoneRaw = trim((string) ($posted['phone'] ?? ''));
    $message  = trim((string) ($posted['message'] ?? ''));

    if ($phoneRaw === '' || $message === '') {
        $result = ['success' => false, 'message' => 'Phone number and message are both required.', 'sid' => null];
    } else {
        $to = normalizePhone($phoneRaw);
        if ($to === null) {
            $result = ['success' => false, 'message' => 'That phone number doesn\'t look valid.', 'sid' => null];
        } elseif (strlen($message) > 1600) {
            $result = ['success' => false, 'message' => 'Message is too long (max 1600 characters).', 'sid' => null];
        } else {
            $result = sendSms($to, $message);
        }
    }

    if ($isAjax) jsonOut($result);

    $qs = http_build_query(['sent' => $result['success'] ? 1 : 0, 'msg' => $result['message']]);
    header('Location: send_sms.php?' . $qs);
    exit;
}

$flashSent = isset($_GET['sent']) ? (bool) $_GET['sent'] : null;
$flashMsg  = $_GET['msg'] ?? null;
$siteName  = "Backstage 2.0";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Send SMS — <?= htmlspecialchars($siteName) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --purple: #7A55C7;
    --purple-dark: #6644AA;
    --canvas-bg: #eeeaf6;
  }
  html, body { height: 100%; font-family: 'DM Sans', sans-serif; }
  body { background: var(--canvas-bg); color: #333; min-height: 100vh; display: flex; align-items: flex-start; justify-content: center; padding: 40px 20px; }
  .sms-wrap { max-width: 480px; width: 100%; }
  .sms-card { background: #fff; border-radius: 14px; box-shadow: 0 4px 24px rgba(60,30,100,.12); padding: 28px 32px; }
  .sms-title { font-family: 'Playfair Display', serif; font-size: 24px; color: #4a2e7a; margin-bottom: 4px; }
  .sms-sub { color: #8a7aa8; font-size: 13px; margin-bottom: 22px; }
  .sms-flash { padding: 10px 14px; border-radius: 8px; margin-bottom: 18px; font-size: 14px; }
  .sms-flash.ok { background: #e8f8ee; color: #1e7a44; border: 1px solid #b9e8c9; }
  .sms-flash.err { background: #fdeaea; color: #a3282f; border: 1px solid #f2c2c2; }
  .sms-field { display: flex; flex-direction: column; gap: 5px; margin-bottom: 16px; }
  .sms-field label { font-size: 12.5px; font-weight: 500; color: #5a4a78; text-transform: uppercase; letter-spacing: .4px; }
  .sms-field input, .sms-field textarea {
    border: 1px solid #d9d0ec; border-radius: 8px; padding: 9px 11px; font-size: 14px; font-family: inherit; color: #333;
  }
  .sms-field input:focus, .sms-field textarea:focus { outline: none; border-color: var(--purple); box-shadow: 0 0 0 3px rgba(122,85,199,.15); }
  .sms-field textarea { min-height: 110px; resize: vertical; }
  .sms-count { font-size: 11.5px; color: #a89bc4; text-align: right; margin-top: 2px; }
  .sms-actions { margin-top: 10px; display: flex; justify-content: flex-end; }
  .sms-btn { border: none; border-radius: 8px; padding: 10px 22px; font-size: 14px; font-weight: 500; cursor: pointer; background: var(--purple); color: #fff; }
  .sms-btn:hover { background: var(--purple-dark); }
  .sms-btn:disabled { opacity: .6; cursor: not-allowed; }
</style>
</head>
<body>

<main class="sms-wrap">
  <div class="sms-card">
    <div class="sms-title">Send SMS</div>
    <div class="sms-sub">Send a text message to a phone number via Twilio</div>

    <?php if ($flashSent !== null): ?>
      <div class="sms-flash <?= $flashSent ? 'ok' : 'err' ?>"><?= htmlspecialchars((string) $flashMsg) ?></div>
    <?php endif; ?>

    <form method="post" action="send_sms.php" id="smsForm">
      <div class="sms-field">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" placeholder="(555) 123-4567" required>
      </div>

      <div class="sms-field">
        <label for="message">Message</label>
        <textarea id="message" name="message" maxlength="1600" required></textarea>
        <div class="sms-count"><span id="charCount">0</span> / 1600</div>
      </div>

      <div class="sms-actions">
        <button type="submit" class="sms-btn" id="sendBtn">Send Message</button>
      </div>
    </form>
  </div>
</main>

<script>
  const messageEl = document.getElementById('message');
  const charCount  = document.getElementById('charCount');
  messageEl.addEventListener('input', () => {
    charCount.textContent = messageEl.value.length;
  });

  // Optional: submit via fetch so the page doesn't have to reload.
  const form    = document.getElementById('smsForm');
  const sendBtn = document.getElementById('sendBtn');
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    sendBtn.disabled = true;
    sendBtn.textContent = 'Sending…';

    const formData = new FormData(form);
    try {
      const res = await fetch('send_sms.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData,
      });
      const data = await res.json();

      const existingFlash = document.querySelector('.sms-flash');
      if (existingFlash) existingFlash.remove();

      const flash = document.createElement('div');
      flash.className = 'sms-flash ' + (data.success ? 'ok' : 'err');
      flash.textContent = data.message;
      form.parentElement.insertBefore(flash, form);

      if (data.success) {
        form.reset();
        charCount.textContent = '0';
      }
    } catch (err) {
      alert('Something went wrong sending the message.');
    } finally {
      sendBtn.disabled = false;
      sendBtn.textContent = 'Send Message';
    }
  });
</script>

</body>
</html>
