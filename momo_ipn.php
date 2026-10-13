<?php require 'config.php'; require 'payment_gateway.php';
// MoMo gọi vào đây (server-to-server). Chỉ hoạt động khi web có địa chỉ công khai (hosting hoặc ngrok).
$d = json_decode(file_get_contents('php://input'), true) ?: [];
$st = $pdo->prepare('SELECT * FROM orders WHERE id_order=?'); $st->execute([(string)($d['orderId'] ?? '')]);
$o = $st->fetch();
if ($o && momo_verify($d) && (int)($d['amount'] ?? -1) === (int)$o['amount']) {
    handleCallback($pdo, $o['id_order'], (int)($d['resultCode'] ?? -1) === 0, (string)($d['transId'] ?? ''));
}
http_response_code(204);
