<?php
// db.php - Database helper for BuyCryptoCardBot

function getDB() {
    $db = new SQLite3('bot.db');
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        user_id INTEGER PRIMARY KEY,
        state TEXT DEFAULT 'IDLE',
        card_type TEXT,
        card_name TEXT,
        last_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    return $db;
}

function getUser($user_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE user_id = :id");
    $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    return $result->fetchArray(SQLITE3_ASSOC);
}

function updateUser($user_id, $data) {
    $db = getDB();
    $currentUser = getUser($user_id);

    if (!$currentUser) {
        $stmt = $db->prepare("INSERT INTO users (user_id) VALUES (:id)");
        $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
        $stmt->execute();
    }

    $sets = [];
    foreach ($data as $key => $value) {
        $sets[] = "$key = :$key";
    }

    if (empty($sets)) return;

    $sets_str = implode(", ", $sets);

    $stmt = $db->prepare("UPDATE users SET $sets_str, last_action = CURRENT_TIMESTAMP WHERE user_id = :id");
    foreach ($data as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
    $stmt->execute();
}
