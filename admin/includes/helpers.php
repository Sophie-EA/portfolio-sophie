<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function generateToken(): string {
    // Si token existant et valide (< 1h), on le garde
    if (!empty($_SESSION['csrf_token']) && !empty($_SESSION['csrf_time'])) {
        if ((time() - $_SESSION['csrf_time']) < 3600) {
            return $_SESSION['csrf_token'];
        }
    }
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_time'] = time();
    return $token;
}

function verifyToken(string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) return false;
    if ((time() - ($_SESSION['csrf_time'] ?? 0)) > 3600) return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}

function flashMessage(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
