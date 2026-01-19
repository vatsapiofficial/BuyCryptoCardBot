<?php
require_once 'config.php';
require_once 'db.php';

/* ---------- Functions ---------- */
function tg($method, $data) {
    global $token;
    $url = "https://api.telegram.org/bot$token/$method";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

function sendMessage($chat_id, $text, $keyboard = null) {
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    if ($keyboard) {
        $data['reply_markup'] = json_encode($keyboard);
    }
    return tg('sendMessage', $data);
}

/* ---------- Input ---------- */
$update = json_decode(file_get_contents('php://input'), true);
if (!$update) {
    // Basic landing page if no Telegram update is received
    echo "<h1>Buy Crypto Card Bot</h1>";
    echo "<p>Virtual Visa & Mastercard cards. No KYC, 0% transaction fees, high limits. Pay with USDT.</p>";
    echo "<p><a href='https://t.me/BuyCryptoCardBot'>Open in Telegram</a></p>";
    exit;
}

$chat_id = $update['message']['chat']['id'] ?? $update['callback_query']['message']['chat']['id'] ?? null;
$user_id = $update['message']['from']['id'] ?? $update['callback_query']['from']['id'] ?? null;
$text = $update['message']['text'] ?? null;
$callback = $update['callback_query']['data'] ?? null;

if (!$user_id) exit;

$user = getUser($user_id);
$state = $user['state'] ?? 'IDLE';

/* ---------- Main Menu ---------- */
$menu = [
    'inline_keyboard' => [
        [
            ['text' => '💳 Buy Virtual Card', 'callback_data' => 'buy_card'],
        ],
        [
            ['text' => '💰 Deposit USDT', 'callback_data' => 'deposit'],
            ['text' => '📊 Fees & Limits', 'callback_data' => 'fees'],
        ],
        [
            ['text' => '🆘 Support', 'url' => 'https://t.me/BuyCryptoCardBot'],
        ]
    ]
];

/* ---------- Commands ---------- */
if ($text === '/start') {
    updateUser($user_id, ['state' => 'IDLE']);
    sendMessage(
        $chat_id,
        "🚀 Buy Crypto Card\n\n".
        "Virtual Visa & Mastercard cards\n".
        "• No KYC\n".
        "• 0% transaction fees\n".
        "• High limits\n\n".
        "💳 Pay with USDT",
        $menu
    );
    exit;
}

/* ---------- Text Handling (States) ---------- */
if ($text && $state === 'AWAITING_NAME') {
    // Requirements:
    // • 3-30 characters
    // • Letters, numbers, spaces only
    // • No special characters
    if (preg_match('/^[a-zA-Z0-9\s]{3,30}$/', $text)) {
        updateUser($user_id, [
            'state' => 'AWAITING_PAYMENT',
            'card_name' => $text
        ]);

        sendMessage(
            $chat_id,
            "✅ <b>Card Name Set:</b> {$text}\n\n".
            "💳 <b>Card Type:</b> " . ucfirst($user['card_type'] ?? 'Card') . "\n\n".
            "📝 <b>Step 2: Payment</b>\n\n".
            "Please send <b>25 USDT</b> to the address below to complete your purchase:\n\n".
            "<code>USDT_ADDRESS_PLACEHOLDER</code>\n\n".
            "Network: <b>TRC20</b>\n".
            "⚠️ Send exactly 25 USDT. Your card will be issued automatically after confirmation.",
            [
                'inline_keyboard' => [
                    [['text' => '⬅ Back to Main Menu', 'callback_data' => 'back']]
                ]
            ]
        );
    } else {
        sendMessage(
            $chat_id,
            "⚠️ <b>Invalid Name</b>\n\n".
            "Please follow the requirements:\n".
            "• 3-30 characters\n".
            "• Letters, numbers, spaces only\n".
            "• No special characters\n\n".
            "Example: <i>John Doe Business Card</i>"
        );
    }
    exit;
}

/* ---------- Callbacks ---------- */
if ($callback) {
    if ($callback === 'buy_card') {
        sendMessage(
            $chat_id,
            "💳 Select Card Type",
            [
                'inline_keyboard' => [
                    [
                        ['text' => 'Visa', 'callback_data' => 'card_visa'],
                        ['text' => 'Mastercard', 'callback_data' => 'card_mc'],
                    ],
                    [
                        ['text' => '⬅ Back', 'callback_data' => 'back']
                    ]
                ]
            ]
        );
    }

    if (in_array($callback, ['card_visa', 'card_mc'])) {
        $type = ($callback === 'card_visa') ? 'visa' : 'mastercard';
        updateUser($user_id, [
            'state' => 'AWAITING_NAME',
            'card_type' => $type
        ]);

        sendMessage(
            $chat_id,
            "💳 <b>Card Purchase Process</b>\n\n".
            "📝 <b>Step 1: Card Information</b>\n\n".
            "Please enter the name you want on your virtual card:\n\n".
            "Example: <i>John Doe Business Card</i>\n\n".
            "<b>Requirements:</b>\n".
            "• 3-30 characters\n".
            "• Letters, numbers, spaces only\n".
            "• No special characters"
        );
    }

    if ($callback === 'deposit') {
        sendMessage(
            $chat_id,
            "💰 Deposit USDT\n\n".
            "Send USDT to the address below:\n\n".
            "<code>USDT_ADDRESS_PLACEHOLDER</code>\n\n".
            "Network: <b>TRC20</b>\n".
            "⚠️ Auto-detection not enabled yet."
        );
    }

    if ($callback === 'fees') {
        sendMessage(
            $chat_id,
            "📊 Fees & Limits\n\n".
            "• Card issuance: 0%\n".
            "• Transactions: 0%\n".
            "• Daily limits: High\n\n".
            "Limits depend on card provider."
        );
    }

    if ($callback === 'back') {
        updateUser($user_id, ['state' => 'IDLE']);
        sendMessage($chat_id, "🏠 Main Menu", $menu);
    }

    tg('answerCallbackQuery', [
        'callback_query_id' => $update['callback_query']['id']
    ]);
}
