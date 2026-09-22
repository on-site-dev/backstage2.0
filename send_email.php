<?php
declare(strict_types=1);

// ============================================================
//  SendGrid Email Page — Backstage 2.0 / On-Site Studios
//  Requires: sendgrid/sendgrid composer package  OR  cURL only
//  Config:   set SENDGRID_API_KEY in your environment / .env
// ============================================================

$siteName    = "Backstage 2.0";
$studioName  = "On-Site Studios";
$currentYear = date('Y');
$pageTitle   = "Send Email — " . myhtmlspecialchars($siteName);

// ── SendGrid configuration ───────────────────────────────────
// Load from environment or define here (never hard-code in production)
$sendgridApiKey  = $_ENV['SENDGRID_API_KEY'] ? $_ENV['SENDGRID_API_KEY'] : (getenv('SENDGRID_API_KEY') ? getenv('SENDGRID_API_KEY') : '');
$defaultFromEmail = "software@plexsoftapps.com";  // $_ENV['MAIL_FROM_EMAIL'] ? $_ENV['MAIL_FROM_EMAIL'] : (getenv('MAIL_FROM_EMAIL') ? getenv('MAIL_FROM_EMAIL') : 'noreply@onsitestudios.com');
$defaultFromName  = "Software"; //$_ENV['MAIL_FROM_NAME']  ? $_ENV['MAIL_FROM_NAME'] : (getenv('MAIL_FROM_NAME') ? getenv('MAIL_FROM_NAME')  : 'On-Site Studios');
$defaultToEmail = "steve.smith@plexsoftapps.com";
$defaultSubject = "Test of Sendgrid";
$defaultBody = "<h2>This is a test</h2>";

// ── Nav items ────────────────────────────────────────────────
require_once('includes/navitems.php');
// $navItems = [
//     ['label' => 'Home',       'href' => 'index.php', 'children' => []],
//     ['label' => 'Projects',   'href' => '#projects',  'children' => []],
//     ['label' => 'Scheduling', 'href' => '#scheduling','children' => []],
//     ['label' => 'Reports',    'href' => '#reports',   'children' => []],
//     ['label' => 'Admin',      'href' => '#admin',     'children' => []],
//     ['label' => 'Ziflow',     'href' => '#ziflow',    'children' => []],
//     [
//         'label'    => 'Contact',
//         'href'     => '#contact',
//         'children' => [['label' => 'Support', 'href' => '#support']],
//     ],
//     [
//         'label'    => 'About',
//         'href'     => '#about',
//         'children' => [
//             ['label' => 'Mission',  'href' => '#mission'],
//             ['label' => 'Vision',   'href' => '#vision'],
//             ['label' => 'Founders', 'href' => '#founders'],
//         ],
//     ],
// ];

// ============================================================
//  SENDGRID MAILER — pure cURL, no SDK dependency
// ============================================================
class SendGridMailer
{
    const API_URL = 'https://api.sendgrid.com/v3/mail/send';
    const API_KEY = $this->sendgridApiKey;

    private $apiKey = API_KEY;
    private $defaultFromEmail = "";
    private $defaultFromName = "";

    public function __construct(
        $apiKey,
        $defaultFromEmail,
        $defaultFromName
    ) {
        $this->apiKey = $apiKey;
        $this->defaultFromEmail = $defaultFromEmail;
        $this->defaultFromName = $defaultFromName;
    }

    /**
     * Send an email via the SendGrid v3 Mail Send API.
     *
     * @param string      $toEmail      Recipient email address
     * @param string      $toName       Recipient display name
     * @param string      $subject      Email subject line
     * @param string      $htmlBody     HTML message body
     * @param string      $textBody     Plain-text fallback body
     * @param string|null $fromEmail    Override the default From address
     * @param string|null $fromName     Override the default From name
     * @param string|null $replyTo      Reply-To address (optional)
     * @param array       $attachments  [['filename'=>'x.pdf','content'=>base64,'type'=>'application/pdf'], …]
     * @param array       $cc           [['email'=>'…','name'=>'…'], …]
     * @param array       $bcc          [['email'=>'…','name'=>'…'], …]
     *
     * @return array{success: bool, statusCode: int, message: string, messageId: string}
     */
    public function send(
        string  $toEmail,
        string  $toName,
        string  $subject,
        string  $htmlBody,
        string  $textBody     = null,
        string  $fromEmail    = null,
        string  $fromName     = null,
        string  $replyTo      = null,
        array   $attachments  = [],
        array   $cc           = [],
        array   $bcc          = []
        ) {

        error_log('SEND - API Key: ' . $this->apiKey);

        if (empty($this->apiKey)) {
            return [
                'success'    => false,
                'statusCode' => 0,
                'message'    => 'SendGrid API key is not configured.',
                'messageId'  => '',
            ];
        }

        // Build the JSON payload
        $payload = [
            'personalizations' => [
                [
                    'to'      => [['email' => $toEmail, 'name' => $toName]],
                    'subject' => $subject,
                ],
            ],
            'from' => [
                'email' => $fromEmail ? $this->defaultFromEmail : '',
                'name'  => $fromName  ? $this->defaultFromName : ''
            ],
            'content' => [],
        ];

        // Plain-text content first (recommended order)
        if ($textBody !== '') {
            $payload['content'][] = ['type' => 'text/plain', 'value' => $textBody];
        }
        $payload['content'][] = ['type' => 'text/html', 'value' => $htmlBody];

        // Optional: Reply-To
        if ($replyTo !== null) {
            $payload['reply_to'] = ['email' => $replyTo];
        }

        // Optional: CC / BCC
        if (!empty($cc)) {
            $payload['personalizations'][0]['cc'] = $cc;
        }
        if (!empty($bcc)) {
            $payload['personalizations'][0]['bcc'] = $bcc;
        }

        // Optional: Attachments (base64-encoded)
        if (!empty($attachments)) {
            $payload['attachments'] = $attachments;
        }

        // Execute via cURL
        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 15,
        ]);

        $responseBody = curl_exec($ch);
        $statusCode   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        $messageId    = curl_getinfo($ch, CURLINFO_REDIRECT_URL); // SG returns X-Message-Id header
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'statusCode' => 0, 'message' => 'cURL error: ' . $curlError, 'messageId' => ''];
        }

        // 2xx = success
        if ($statusCode >= 200 && $statusCode < 300) {
            return ['success' => true, 'statusCode' => $statusCode, 'message' => 'Email sent successfully.', 'messageId' => $messageId ?: ''];
        }

        // Parse SendGrid error body
        $decoded = json_decode($responseBody ?: '{}', true);
        $errors  = $decoded['errors'] ? [] : '';
        $errMsg  = implode('; ', array_column($errors, 'message'));

        return [
            'success'    => false,
            'statusCode' => $statusCode,
            'message'    => $errMsg ?: ('SendGrid error ' . $statusCode),
            'messageId'  => '',
        ];
    }
}

function getPost($name, $default='') {
    $value = '';

    try {
        if (isset($_POST[$name])) {
            $value = trim($_POST[$name]);
        }
        else {
            $value = $default;
        }
            
    }
    catch (Exception $e)
    {
        error_log($e);
    }

    return $value;
}


function parseCsvEmails($raw) {
    $parsed = [];
    foreach (explode(',', $raw) as $addr) {
        $addr = trim($addr);
        if ($addr !== '' && filter_var($addr, FILTER_VALIDATE_EMAIL)) {
            $parsed[] = ['email' => $addr];
        }
    }
    return $parsed;
};

// ============================================================
//  HANDLE POST SUBMISSION
// ============================================================
$result      = null;
$formValues  = [];
$fieldErrors = [];
$formValues = [
    'to_email'   => $defaultToEmail,
    'to_name'    => '',
    'reply_to'   => $defaultFromEmail,
    'cc'         => '',
    'bcc'        => '',
    'subject'    => $defaultSubject,
    'body_html'  => $defaultBody,
    'body_text'  => $defaultBody,
    'from_email' => $defaultFromEmail,
    'from_name'  => $defaultFromName
];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect & sanitise
    $formValues = [
        'to_email'   => getPost('to_email', $defaultToEmail),
        'to_name'    => getPost('to_name'),
        'reply_to'   => getPost('reply_to', $defaultFromEmail),
        'cc'         => getPost('cc'),
        'bcc'        => getPost('bcc'),
        'subject'    => getPost('subject', $defaultSubject),
        'body_html'  => getPost('body_html', $defaultBody),
        'body_text'  => getPost('body_text', $defaultBody),
        'from_email' => getPost('from_email', $defaultFromEmail),
        'from_name'  => getPost('from_name', $defaultFromName)
    ];

    // ── Validation ───────────────────────────────────────────
    if (empty($formValues['to_email'])) {
        $fieldErrors['to_email'] = 'Recipient email is required.';
    } 
    elseif (!filter_var($formValues['to_email'], FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['to_email'] = 'Please enter a valid email address.';
    }

    if (empty($formValues['subject'])) {
        $fieldErrors['subject'] = 'Subject is required.';
    }

    if (empty($formValues['body_html']) && empty($formValues['body_text'])) {
        $fieldErrors['body_html'] = 'At least one of HTML body or plain-text body is required.';
    }

    if (!empty($formValues['reply_to']) && !filter_var($formValues['reply_to'], FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['reply_to'] = 'Reply-To must be a valid email address.';
    }

    if (!empty($formValues['from_email']) && !filter_var($formValues['from_email'], FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['from_email'] = 'From email must be a valid address.';
    }

    // Parse optional CC / BCC lists (comma-separated emails)
    $ccList  = [];
    $bccList = [];

    if (!empty($formValues['cc'])) {
        $ccList = parseCsvEmails($formValues['cc']);
        if (empty($ccList)) {
            $fieldErrors['cc'] = 'CC field contains invalid email addresses.';
        }
    }
    if (!empty($formValues['bcc'])) {
        $bccList = parseCsvEmails($formValues['bcc']);
        if (empty($bccList)) {
            $fieldErrors['bcc'] = 'BCC field contains invalid email addresses.';
        }
    }

    // ── Handle file attachment ────────────────────────────────
    $attachments = [];
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['attachment'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $fieldErrors['attachment'] = 'File upload failed (code ' . $file['error'] . ').';
        } elseif ($file['size'] > 10 * 1024 * 1024) {
            $fieldErrors['attachment'] = 'Attachment must be under 10 MB.';
        } else {
            $fileContent  = file_get_contents($file['tmp_name']);
            $attachments[] = [
                'content'     => base64_encode($fileContent),
                'filename'    => basename($file['name']),
                'type'        => $file['type'] ?: 'application/octet-stream',
                'disposition' => 'attachment',
            ];
        }
    }

    // ── Send if no errors ────────────────────────────────────
    if (empty($fieldErrors)) {
        error_log('API Key: ' . $sendgridApiKey);
        $mailer = new SendGridMailer($sendgridApiKey, $defaultFromEmail, $defaultFromName);

        // Auto-generate plain-text fallback from HTML if not provided
        $textBody = $formValues['body_text'] !== ''
            ? $formValues['body_text']
            : strip_tags($formValues['body_html']);

        error_log('Sending body: ' . $textBody);

        $result = $mailer->send(
            $formValues['to_email'],
            $formValues['to_name'] ?: $formValues['to_email'],
            $formValues['subject'],
            $formValues['body_html'] !== '' ? $formValues['body_html'] : nl2br(myhtmlspecialchars($formValues['body_text'])),
            $textBody,
            $formValues['from_email'] ?: null,
            $formValues['from_name']  ?: null,
            $formValues['reply_to']   ?: null,
            $attachments,
            $ccList,
            $bccList
        );

        error_log(print_r($result,true));

        // Clear form on success
        if ($result['success']) {
            $formValues = [];
        }
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
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --purple:        #9370DB;
            --purple-dark:   #7B5EBF;
            --purple-deeper: #6B4FA8;
            --text-light:    #f0eaff;
            --text-muted:    #d8cef5;
            --banner-height: 150px;
            --footer-height: 60px;
        }

        html, body { height: 100%; font-family: 'DM Sans', sans-serif; }

        body {
            background-color: #f7f4fd;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            color: #333;
        }

        /* ===== TOP BANNER — identical to index.php ===== */
        header.top-banner {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--banner-height);
            background: linear-gradient(135deg, #7B5EBF 0%, #9370DB 50%, #A880E8 100%);
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
            align-items: stretch;
            padding: 0 20px;
            height: 44px;
            border-top: 1px solid rgba(255,255,255,0.12);
            background: rgba(0,0,0,0.08);
            overflow: visible;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            flex-shrink: 0;
        }
        .logo-img { height: 52px; width: auto; display: block; transition: opacity 0.2s, transform 0.2s; }
        .logo:hover .logo-img { opacity: 0.88; transform: scale(1.03); }
        .logo-site-label {
            font-family: 'Playfair Display', serif;
            font-size: 24px; font-weight: 600; color: #ffffff;
            letter-spacing: 0.4px; white-space: nowrap;
            text-shadow: 0 1px 4px rgba(0,0,0,0.15);
        }

        .search-wrapper { flex: 1; display: flex; justify-content: center; }
        .search-bar {
            display: flex; align-items: center;
            background: #ffffff;
            border: 1px solid rgba(255,255,255,0.6);
            border-radius: 50px;
            padding: 0 18px; width: 100%; max-width: 420px; height: 44px;
            transition: box-shadow 0.2s;
        }
        .search-bar:focus-within { box-shadow: 0 0 0 3px rgba(255,255,255,0.3); }
        .search-bar svg { width: 17px; height: 17px; color: #9370DB; flex-shrink: 0; margin-right: 10px; }
        .search-bar input {
            flex: 1; background: transparent; border: none; outline: none;
            color: #3a2a5a; font-family: 'DM Sans', sans-serif; font-size: 14px;
        }
        .search-bar input::placeholder { color: #b09ed4; }

        .banner-right { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
        .btn-login {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(255,255,255,0.15); border: 1.5px solid rgba(255,255,255,0.4);
            border-radius: 50px; padding: 0 18px; height: 38px;
            color: #fff; font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 500;
            letter-spacing: 0.3px; text-decoration: none; cursor: pointer;
            transition: background 0.2s, transform 0.15s; white-space: nowrap; backdrop-filter: blur(6px);
        }
        .btn-login:hover { background: rgba(255,255,255,0.28); transform: translateY(-1px); }
        .btn-login svg { width: 15px; height: 15px; }

        .avatar-wrap { flex-shrink: 0; cursor: pointer; position: relative; }
        .avatar {
            width: 42px; height: 42px; border-radius: 50%;
            background: linear-gradient(135deg, #e0c8ff, #c09aff);
            border: 2px solid rgba(255,255,255,0.4);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Playfair Display', serif; font-size: 17px; color: #5a3fa0; font-weight: 700;
            transition: transform 0.2s; box-shadow: 0 2px 12px rgba(0,0,0,0.2);
        }
        .avatar:hover { transform: scale(1.08); }
        .avatar-status {
            position: absolute; bottom: 1px; right: 1px;
            width: 10px; height: 10px; background: #4ade80;
            border-radius: 50%; border: 2px solid var(--purple);
        }

        nav.banner-nav { display: flex; align-items: stretch; gap: 2px; height: 100%; }
        .nav-item { position: relative; display: flex; align-items: stretch; }
        .nav-item > a {
            display: inline-flex; align-items: center; gap: 5px; padding: 0 18px;
            color: rgba(255,255,255,0.85); text-decoration: none;
            font-size: 14px; font-weight: 400; letter-spacing: 0.3px;
            border-radius: 6px 6px 0 0;
            transition: background 0.18s, color 0.18s; white-space: nowrap;
        }
        .nav-item > a:hover, .nav-item:hover > a { background: rgba(255,255,255,0.14); color: #fff; }
        .nav-item > a.active {
            background: rgba(255,255,255,0.18); color: #fff; font-weight: 500;
            border-bottom: 2px solid rgba(255,255,255,0.75); border-radius: 6px 6px 0 0;
        }
        .chevron { width: 12px; height: 12px; transition: transform 0.2s; opacity: 0.75; }
        .nav-item:hover > a .chevron { transform: rotate(180deg); opacity: 1; }
        .dropdown {
            display: none; position: absolute; top: 100%; left: 0;
            min-width: 160px; background: #5a3fa0;
            border-radius: 0 8px 8px 8px;
            box-shadow: 0 8px 28px rgba(40,15,80,0.45);
            overflow: hidden; z-index: 2000;
            animation: dropIn 0.16s ease;
        }
        @keyframes dropIn {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .nav-item:hover .dropdown { display: block; }
        .dropdown a {
            display: flex; align-items: center; padding: 11px 18px;
            color: rgba(255,255,255,0.88); text-decoration: none;
            font-size: 13.5px; font-weight: 400;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            transition: background 0.15s, color 0.15s; white-space: nowrap;
        }
        .dropdown a:last-child { border-bottom: none; }
        .dropdown a:hover { background: rgba(255,255,255,0.15); color: #fff; padding-left: 24px; }

        .hamburger {
            display: none; background: none; border: 1.5px solid rgba(255,255,255,0.35);
            border-radius: 8px; cursor: pointer; padding: 6px 8px; color: #fff;
            flex-shrink: 0; transition: background 0.2s;
        }
        .hamburger:hover { background: rgba(255,255,255,0.15); }
        .hamburger svg { width: 20px; height: 20px; display: block; }

        .mobile-nav {
            display: none; position: fixed; top: var(--banner-height); left: 0; right: 0;
            background: #6B4FA8; z-index: 999; flex-direction: column;
            border-bottom: 2px solid rgba(255,255,255,0.15);
            box-shadow: 0 8px 20px rgba(60,20,100,0.3);
            max-height: calc(100vh - var(--banner-height)); overflow-y: auto;
        }
        .mobile-nav.open { display: flex; }
        .mobile-nav-item > a.mobile-parent {
            display: flex; align-items: center; justify-content: space-between;
            padding: 15px 28px; color: rgba(255,255,255,0.95); text-decoration: none;
            font-size: 15px; font-weight: 500; border-bottom: 1px solid rgba(255,255,255,0.1);
            transition: background 0.15s;
        }
        .mobile-nav-item > a.mobile-parent:hover { background: rgba(255,255,255,0.1); }
        .m-chevron { width: 14px; height: 14px; transition: transform 0.2s; opacity: 0.7; }
        .mobile-nav-item.open > a.mobile-parent .m-chevron { transform: rotate(180deg); }
        .mobile-nav-item > a.mobile-simple {
            display: block; padding: 15px 28px; color: rgba(255,255,255,0.95);
            text-decoration: none; font-size: 15px; font-weight: 500;
            border-bottom: 1px solid rgba(255,255,255,0.1); transition: background 0.15s;
        }
        .mobile-nav-item > a.mobile-simple:hover { background: rgba(255,255,255,0.1); }
        .mobile-submenu { display: none; flex-direction: column; background: rgba(0,0,0,0.12); }
        .mobile-nav-item.open .mobile-submenu { display: flex; }
        .mobile-submenu a {
            display: block; padding: 12px 28px 12px 44px; color: rgba(255,255,255,0.8);
            text-decoration: none; font-size: 14px; font-weight: 300;
            border-bottom: 1px solid rgba(255,255,255,0.06); transition: background 0.15s;
        }
        .mobile-submenu a:hover { background: rgba(255,255,255,0.08); color: #fff; }

        /* ===== MAIN CONTENT ===== */
        main {
            flex: 1;
            margin-top: var(--banner-height);
            margin-bottom: var(--footer-height);
            padding: 40px 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .page-header {
            width: 100%;
            max-width: 780px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .page-header-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, var(--purple-dark), var(--purple));
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 16px rgba(147,112,219,0.35);
            flex-shrink: 0;
        }
        .page-header-icon svg { width: 24px; height: 24px; color: #fff; }
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: var(--purple-deeper);
        }
        .page-header p { font-size: 13.5px; color: #8878a8; font-weight: 300; margin-top: 3px; }

        /* ===== ALERT BANNERS ===== */
        .alert {
            width: 100%; max-width: 780px;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 14px;
            line-height: 1.5;
            animation: slideIn 0.25s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .alert svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
        .alert-success { background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46; }
        .alert-error   { background: #fff1f2; border: 1px solid #fca5a5; color: #9b1c1c; }
        .alert-warning { background: #fffbeb; border: 1px solid #fcd34d; color: #78350f; }

        /* ===== EMAIL FORM CARD ===== */
        .email-card {
            background: #fff;
            border: 1px solid #e6d9ff;
            border-radius: 20px;
            padding: 36px 40px;
            width: 100%;
            max-width: 780px;
            box-shadow: 0 6px 28px rgba(147,112,219,0.10);
        }

        /* Section dividers inside the form */
        .form-section {
            margin-bottom: 28px;
        }
        .form-section:last-child { margin-bottom: 0; }

        .section-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: rgba(107,79,168,0.5);
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f0e8ff;
        }

        /* Row / column layout */
        .form-row { display: flex; gap: 16px; }
        .form-row .form-group { flex: 1; }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 16px;
        }
        .form-group:last-child { margin-bottom: 0; }

        label {
            font-size: 13px;
            font-weight: 500;
            color: var(--purple-deeper);
            letter-spacing: 0.2px;
        }
        label .req { color: #c97de0; margin-left: 2px; }
        label .hint {
            font-weight: 300;
            color: #b09ed4;
            font-size: 11.5px;
            margin-left: 6px;
        }

        .input-wrap { position: relative; }
        .input-wrap svg.field-icon {
            position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
            width: 16px; height: 16px; color: #b09ed4; pointer-events: none;
            transition: color 0.2s;
        }

        input[type="text"],
        input[type="email"],
        input[type="file"],
        textarea,
        select {
            width: 100%;
            padding: 11px 13px 11px 38px;
            border: 1.5px solid #ddd0f5;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: #3a2a5a;
            background: #fff;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        input.no-icon, textarea.no-icon { padding-left: 13px; }
        input[type="file"] { padding: 9px 13px; cursor: pointer; }

        input:focus, textarea:focus, select:focus {
            border-color: var(--purple);
            box-shadow: 0 0 0 3px rgba(147,112,219,0.15);
        }
        input::placeholder, textarea::placeholder { color: #c0b2d8; font-weight: 300; }

        textarea { resize: vertical; min-height: 120px; line-height: 1.6; }
        #body_html { min-height: 200px; font-family: monospace; font-size: 13px; }

        .input-wrap:focus-within svg.field-icon { color: var(--purple); }

        /* Field error state */
        .field-error input,
        .field-error textarea { border-color: #fca5a5; }
        .field-error input:focus,
        .field-error textarea:focus { box-shadow: 0 0 0 3px rgba(239,68,68,0.12); }
        .error-text {
            font-size: 12px;
            color: #dc2626;
            margin-top: 3px;
        }

        /* Tabs for HTML / Plain-text body */
        .tab-bar {
            display: flex;
            gap: 2px;
            margin-bottom: 10px;
        }
        .tab-btn {
            padding: 7px 16px;
            border: 1.5px solid #ddd0f5;
            border-radius: 8px 8px 0 0;
            border-bottom: none;
            background: #f7f4fd;
            font-family: 'DM Sans', sans-serif;
            font-size: 12.5px;
            font-weight: 500;
            color: #9b8abf;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }
        .tab-btn.active {
            background: #fff;
            color: var(--purple-deeper);
            border-color: #ddd0f5;
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* Preview pane */
        #html-preview {
            width: 100%;
            min-height: 200px;
            border: 1.5px solid #ddd0f5;
            border-radius: 0 10px 10px 10px;
            padding: 16px;
            font-family: sans-serif;
            font-size: 14px;
            background: #fdfbff;
            color: #333;
            line-height: 1.6;
            overflow-y: auto;
        }

        /* Character counter */
        .char-count {
            font-size: 11px;
            color: #b09ed4;
            text-align: right;
            margin-top: 4px;
        }
        .char-count.warn { color: #f59e0b; }
        .char-count.over { color: #dc2626; }

        /* Submit button */
        .btn-send {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple) 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 500;
            letter-spacing: 0.3px;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 18px rgba(147,112,219,0.38);
            margin-top: 8px;
        }
        .btn-send:hover { opacity: 0.93; transform: translateY(-1px); box-shadow: 0 6px 24px rgba(147,112,219,0.48); }
        .btn-send:active { transform: translateY(0); }
        .btn-send svg { width: 18px; height: 18px; }
        .btn-send:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        /* API key warning */
        .api-warning {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            color: #78350f;
            margin-bottom: 24px;
        }
        .api-warning svg { width: 18px; height: 18px; flex-shrink: 0; color: #d97706; }
        .api-warning code {
            background: rgba(0,0,0,0.06);
            border-radius: 4px;
            padding: 1px 5px;
            font-size: 12px;
        }

        /* ===== FOOTER ===== */
        footer.bottom-banner {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: var(--footer-height);
            background: linear-gradient(135deg, #7B5EBF 0%, #9370DB 100%);
            border-top: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 -4px 20px rgba(60,20,100,0.3);
            display: flex; align-items: center; justify-content: center;
            z-index: 1000;
        }
        footer.bottom-banner p { font-size: 13px; color: rgba(255,255,255,0.65); font-weight: 300; }
        footer.bottom-banner p span { color: rgba(255,255,255,0.92); font-weight: 500; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 700px) {
            :root { --banner-height: 80px; }
            .banner-nav-row { display: none; }
            .hamburger { display: flex; }
            .email-card { padding: 24px 18px; }
            .form-row { flex-direction: column; gap: 0; }
        }
        @media (max-width: 440px) {
            .btn-login span { display: none; }
            .btn-login { padding: 0 12px; }
        }
    </style>
</head>
<body>

<!-- ===== TOP BANNER ===== -->
<header class="top-banner" role="banner">
    <div class="banner-top-row">
        <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
            <a href="index.php" class="logo" aria-label="<?= myhtmlspecialchars($siteName) ?> Home">
                <img src="images/ONSITE-LOGO-New-Web-Small-White-300x139-1.png"
                     alt="<?= myhtmlspecialchars($siteName) ?> Logo"
                     class="logo-img" />
            </a>
            <span class="logo-site-label"><?= myhtmlspecialchars($siteName) ?></span>
        </div>

        <div class="search-wrapper">
            <form class="search-bar" role="search" action="#" method="get">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
                <input type="search" name="q" placeholder="Search anything…" aria-label="Search" autocomplete="off"/>
            </form>
        </div>

        <div class="banner-right">
            <a href="login.php" class="btn-login" aria-label="Log in">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h7a2 2 0 012 2v1"/>
                </svg>
                <span>Log In</span>
            </a>
            <div class="avatar-wrap" role="button" tabindex="0" aria-label="User profile">
                <div class="avatar">A</div>
                <span class="avatar-status" aria-label="Online"></span>
            </div>
            <button class="hamburger" id="hamburgerBtn" aria-label="Toggle navigation" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="banner-nav-row">
        <nav class="banner-nav" aria-label="Main navigation">
            <?php foreach ($navItems as $index => $item): ?>
                <?php $hasChildren = !empty($item['children']); ?>
                <div class="nav-item">
                    <a href="<?= myhtmlspecialchars($item['href']) ?>"
                       <?= $index === 0 ? 'class="active"' : '' ?>
                       <?= $hasChildren ? 'aria-haspopup="true"' : '' ?>>
                        <?= myhtmlspecialchars($item['label']) ?>
                        <?php if ($hasChildren): ?>
                            <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        <?php endif; ?>
                    </a>
                    <?php if ($hasChildren): ?>
                        <div class="dropdown" role="menu">
                            <?php foreach ($item['children'] as $child): ?>
                                <a href="<?= myhtmlspecialchars($child['href']) ?>" role="menuitem">
                                    <?= myhtmlspecialchars($child['label']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </nav>
    </div>
</header>

<!-- Mobile Nav -->
<nav class="mobile-nav" id="mobileNav" aria-label="Mobile navigation">
    <?php foreach ($navItems as $item): ?>
        <?php $hasChildren = !empty($item['children']); ?>
        <div class="mobile-nav-item">
            <?php if ($hasChildren): ?>
                <a href="<?= myhtmlspecialchars($item['href']) ?>" class="mobile-parent">
                    <?= myhtmlspecialchars($item['label']) ?>
                    <svg class="m-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </a>
                <div class="mobile-submenu">
                    <?php foreach ($item['children'] as $child): ?>
                        <a href="<?= myhtmlspecialchars($child['href']) ?>"><?= myhtmlspecialchars($child['label']) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <a href="<?= myhtmlspecialchars($item['href']) ?>" class="mobile-simple">
                    <?= myhtmlspecialchars($item['label']) ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</nav>

<!-- ===== MAIN CONTENT ===== -->
<main>

    <!-- Page heading -->
    <div class="page-header">
        <div class="page-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <h1>Send Email</h1>
            <p>Compose and dispatch via the SendGrid v3 API</p>
        </div>
    </div>

    <?php if (empty($sendgridApiKey)): ?>
        <div class="api-warning" style="width:100%;max-width:780px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <span>
                <strong>API key not configured.</strong>
                Set the <code>SENDGRID_API_KEY</code> environment variable (or <code>$_ENV</code>) before sending.
                Emails submitted now will fail.
            </span>
        </div>
    <?php endif; ?>

    <?php if ($result !== null): ?>
        <?php if ($result['success']): ?>
            <div class="alert alert-success" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span><strong>Email sent successfully</strong> via SendGrid (HTTP <?= $result['statusCode'] ?>).</span>
            </div>
        <?php else: ?>
            <div class="alert alert-error" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>
                    <strong>Send failed</strong>
                    <?php if ($result['statusCode']): ?>
                        (HTTP <?= $result['statusCode'] ?>):
                    <?php else: ?>:<?php endif; ?>
                    <?= myhtmlspecialchars($result['message']) ?>
                </span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Email Form -->
    <div class="email-card">
        <form method="POST" action="" enctype="multipart/form-data" novalidate id="emailForm">

            <?php  
                error_log('Formvalues');
                error_log(print_r($formValues,true));
            ?>
            <!-- ── FROM ───────────────────────────────────────────── -->
            <div class="form-section">
                <div class="section-label">From</div>
                <div class="form-row">
                    <div class="form-group <?= isset($fieldErrors['from_email']) ? 'field-error' : '' ?>">
                        <label for="from_email">
                            From Email
                            <span class="hint">(overrides default)</span>
                        </label>
                        <?php error_log('setting from email to ' . $defaultFromEmail); ?>
                        <div class="input-wrap">
                            <input type="email" id="from_email" name="from_email"
                                   placeholder="<?= myhtmlspecialchars($defaultFromEmail) ?>"
                                   value="<?= myhtmlspecialchars($defaultFromEmail) ?>"
                                   autocomplete="off"/>
                            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <?php if (isset($fieldErrors['from_email'])): ?>
                            <span class="error-text"><?= myhtmlspecialchars($fieldErrors['from_email']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="from_name">From Name</label>
                        <?php error_log('setting from name to ' . $defaultFromName); ?>
                        <div class="input-wrap">
                            <input type="text" id="from_name" name="from_name"
                                   placeholder="<?= myhtmlspecialchars($defaultFromName) ?>"
                                   value="<?= myhtmlspecialchars($defaultFromName) ?>"/>
                            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── TO / CC / BCC ──────────────────────────────────── -->
            <div class="form-section">
                <div class="section-label">Recipients</div>

                <div class="form-row">
                    <div class="form-group <?= isset($fieldErrors['to_email']) ? 'field-error' : '' ?>">
                        <label for="to_email">
                            To Email <span class="req">*</span>
                        </label>
                        <?php error_log('setting to email to ' . $defaultToEmail); ?>
                        <div class="input-wrap">
                            <input type="email" id="to_email" name="to_email"
                                   placeholder="recipient@example.com"
                                   value="<?= myhtmlspecialchars($defaultToEmail) ?>"
                                   required autocomplete="off"/>
                            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <?php if (isset($fieldErrors['to_email'])): ?>
                            <span class="error-text"><?= myhtmlspecialchars($fieldErrors['to_email']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="to_name">To Name</label>
                        <?php error_log('setting to email to name ' . $defaultToName); ?>
                        <div class="input-wrap">
                            <input type="text" id="to_name" name="to_name"
                                   placeholder="Recipient Name"
                                   value="<?= myhtmlspecialchars($defaultToName) ?>"/>
                            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group <?= isset($fieldErrors['cc']) ? 'field-error' : '' ?>">
                        <label for="cc">
                            CC
                            <span class="hint">comma-separated</span>
                        </label>
                        <div class="input-wrap">
                            <input type="text" id="cc" name="cc"
                                   placeholder="cc1@example.com, cc2@example.com"
                                   value="<?= myhtmlspecialchars($formValues['cc']) ?>" class="no-icon" style="padding-left:13px"/>
                        </div>
                        <?php if (isset($fieldErrors['cc'])): ?>
                            <span class="error-text"><?= myhtmlspecialchars($fieldErrors['cc']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group <?= isset($fieldErrors['bcc']) ? 'field-error' : '' ?>">
                        <label for="bcc">
                            BCC
                            <span class="hint">comma-separated</span>
                        </label>
                        <div class="input-wrap">
                            <input type="text" id="bcc" name="bcc"
                                   placeholder="bcc@example.com"
                                   value="<?= myhtmlspecialchars($formValues['bcc']) ?>" class="no-icon" style="padding-left:13px"/>
                        </div>
                        <?php if (isset($fieldErrors['bcc'])): ?>
                            <span class="error-text"><?= myhtmlspecialchars($fieldErrors['bcc']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group <?= isset($fieldErrors['reply_to']) ? 'field-error' : '' ?>">
                    <label for="reply_to">
                        Reply-To
                        <span class="hint">optional</span>
                    </label>
                    <div class="input-wrap">
                        <input type="email" id="reply_to" name="reply_to"
                               placeholder="replies@example.com"
                               value="<?= myhtmlspecialchars($formValues['reply_to']) ?>"/>
                        <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                    </div>
                    <?php if (isset($fieldErrors['reply_to'])): ?>
                        <span class="error-text"><?= myhtmlspecialchars($fieldErrors['reply_to']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ── SUBJECT ────────────────────────────────────────── -->
            <div class="form-section">
                <div class="section-label">Message</div>

                <div class="form-group <?= isset($fieldErrors['subject']) ? 'field-error' : '' ?>">
                    <label for="subject">Subject <span class="req">*</span></label>
                    <div class="input-wrap">
                        <input type="text" id="subject" name="subject"
                               placeholder="Your email subject line"
                               value="<?= myhtmlspecialchars($formValues['subject']) ?>"
                               maxlength="998" required/>
                        <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <?php if (isset($fieldErrors['subject'])): ?>
                        <span class="error-text"><?= myhtmlspecialchars($fieldErrors['subject']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Body tabs -->
                <div class="form-group <?= isset($fieldErrors['body_html']) ? 'field-error' : '' ?>">
                    <label>
                        Body <span class="req">*</span>
                        <span class="hint">at least one of HTML or plain text</span>
                    </label>

                    <div class="tab-bar" role="tablist">
                        <button type="button" class="tab-btn active" data-tab="html" role="tab" aria-selected="true">
                            HTML
                        </button>
                        <button type="button" class="tab-btn" data-tab="text" role="tab" aria-selected="false">
                            Plain Text
                        </button>
                        <button type="button" class="tab-btn" data-tab="preview" role="tab" aria-selected="false">
                            Preview
                        </button>
                    </div>

                    <div class="tab-content active" id="tab-html">
                        <textarea id="body_html" name="body_html"
                                  placeholder="<p>Hello <strong>World</strong>,</p>&#10;<p>Your message here…</p>"
                                  maxlength="1048576"><?= myhtmlspecialchars($formValues['body_html']) ?></textarea>
                        <div class="char-count" id="html-char-count">0 / 1,048,576</div>
                    </div>

                    <div class="tab-content" id="tab-text">
                        <textarea id="body_text" name="body_text" class="no-icon"
                                  placeholder="Plain text fallback (auto-generated from HTML if left blank)"
                                  maxlength="1048576"><?= myhtmlspecialchars($formValues['body_text']) ?></textarea>
                        <div class="char-count" id="text-char-count">0 / 1,048,576</div>
                    </div>

                    <div class="tab-content" id="tab-preview">
                        <div id="html-preview" aria-label="HTML email preview">
                            <em style="color:#b09ed4">Switch to HTML tab and write your content — the preview will appear here.</em>
                        </div>
                    </div>

                    <?php if (isset($fieldErrors['body_html'])): ?>
                        <span class="error-text"><?= myhtmlspecialchars($fieldErrors['body_html']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ── ATTACHMENT ────────────────────────────────────── -->
            <div class="form-section">
                <div class="section-label">Attachment <span style="font-weight:300;text-transform:none;letter-spacing:0;font-size:10px;">(optional · max 10 MB)</span></div>
                <div class="form-group <?= isset($fieldErrors['attachment']) ? 'field-error' : '' ?>">
                    <div class="input-wrap">
                        <input type="file" id="attachment" name="attachment"
                               accept="*/*"
                               style="padding-left:13px"/>
                    </div>
                    <?php if (isset($fieldErrors['attachment'])): ?>
                        <span class="error-text"><?= myhtmlspecialchars($fieldErrors['attachment']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ── SEND ───────────────────────────────────────────── -->
            <div class="form-section">
                <button type="submit" class="btn-send" id="sendBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Send via SendGrid
                </button>
            </div>

        </form>
    </div>
</main>

<!-- ===== FOOTER ===== -->
<footer class="bottom-banner" role="contentinfo">
    <p>&copy; <?= $currentYear ?> <span><?= myhtmlspecialchars($studioName) ?></span>. All rights reserved.</p>
</footer>

<script>
    // ============================================================
    //  TAB SWITCHING
    // ============================================================
    const tabBtns     = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    const bodyHtml    = document.getElementById('body_html');
    const preview     = document.getElementById('html-preview');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.tab;

            tabBtns.forEach(b => { b.classList.remove('active'); b.setAttribute('aria-selected','false'); });
            tabContents.forEach(c => c.classList.remove('active'));

            btn.classList.add('active');
            btn.setAttribute('aria-selected', 'true');
            document.getElementById('tab-' + target).classList.add('active');

            if (target === 'preview') {
                preview.innerHTML = bodyHtml.value.trim()
                    ? bodyHtml.value
                    : '<em style="color:#b09ed4">Nothing to preview yet.</em>';
            }
        });
    });

    // ============================================================
    //  CHARACTER COUNTERS
    // ============================================================
    function updateCount(textarea, display) {
        const len   = textarea.value.length;
        const max   = parseInt(textarea.getAttribute('maxlength') || '1048576', 10);
        const pct   = len / max;
        display.textContent = len.toLocaleString() + ' / ' + max.toLocaleString();
        display.className   = 'char-count' + (pct > 0.95 ? ' over' : pct > 0.80 ? ' warn' : '');
    }

    const htmlCount = document.getElementById('html-char-count');
    const textCount = document.getElementById('text-char-count');
    const bodyText  = document.getElementById('body_text');

    bodyHtml.addEventListener('input', () => updateCount(bodyHtml, htmlCount));
    bodyText.addEventListener('input', () => updateCount(bodyText, textCount));
    updateCount(bodyHtml, htmlCount);
    updateCount(bodyText, textCount);

    // ============================================================
    //  SEND BUTTON — loading state
    // ============================================================
    document.getElementById('emailForm').addEventListener('submit', (e) => {
        const btn = document.getElementById('sendBtn');
        btn.disabled = true;
        btn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                style="animation:spin 1s linear infinite;width:18px;height:18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Sending…`;
    });

    // ============================================================
    //  MOBILE NAV — mirrors index.php
    // ============================================================
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const mobileNav    = document.getElementById('mobileNav');

    hamburgerBtn?.addEventListener('click', () => {
        const open = mobileNav.classList.toggle('open');
        hamburgerBtn.setAttribute('aria-expanded', String(open));
    });

    document.querySelectorAll('.mobile-parent').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const item = link.closest('.mobile-nav-item');
            item.classList.toggle('open');
            item.parentElement.querySelectorAll('.mobile-nav-item').forEach(s => {
                if (s !== item) s.classList.remove('open');
            });
        });
    });

    document.querySelectorAll('.mobile-submenu a, .mobile-simple').forEach(a => {
        a.addEventListener('click', () => {
            mobileNav.classList.remove('open');
            hamburgerBtn?.setAttribute('aria-expanded', 'false');
        });
    });
</script>

<?php 
    function myhtmlspecialchars($value) {
        return $value;
    }
?>
<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
</body>
</html>
