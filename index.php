<?php
require_once 'config.php';

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
        sendMessage(
            $chat_id,
            "🧪 Card provisioning coming soon\n\n".
            "This is a placeholder.\n".
            "API integration will be added here."
        );
    }
    if ($callback === 'deposit') {
        sendMessage(
            $chat_id,
            "💰 Deposit USDT\n\n".
            "Send USDT to the address below:\n\n".
            "USDT_ADDRESS_PLACEHOLDER\n\n".
            "Network: TRC20\n".
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
        sendMessage($chat_id, "🏠 Main Menu", $menu);
    }
    tg('answerCallbackQuery', [
        'callback_query_id' => $update['callback_query']['id']
    ]);
}
