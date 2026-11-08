<?php
require 'config.php';
require 'payment_gateway.php';
// VNPay gọi server-to-server. Trả JSON theo đúng tài liệu.
header('Content-Type: application/json; charset=UTF-8');

$input = $_GET;
$id = (string)($input['vnp_TxnRef'] ?? '');
$st = $pdo->prepare('SELECT * FROM orders WHERE id_order=?');
$st->execute([$id]);
$o = $st->fetch();

if (!$o) {
    echo json_encode(['RspCode' => '01', 'Message' => 'Order not found']);
    exit;
}
if (!vnpay_verify($input)) {
    echo json_encode(['RspCode' => '97', 'Message' => 'Invalid signature']);
    exit;
}
if ((int)($input['vnp_Amount'] ?? -1) !== ((int)$o['amount'] * 100)) {
    echo json_encode(['RspCode' => '04', 'Message' => 'Invalid amount']);
    exit;
}

// Đã xử lý rồi: SIT/VNPay yêu cầu RspCode 02 (không phải 00).
if ($o['status'] !== 'pending') {
    echo json_encode(['Message' => 'Order already confirmed', 'RspCode' => '02']);
    exit;
}

$ok = (string)($input['vnp_ResponseCode'] ?? '') === '00'
    && (string)($input['vnp_TransactionStatus'] ?? '00') === '00';
handleCallback($pdo, $o['id_order'], $ok, (string)($input['vnp_TransactionNo'] ?? ''));
echo json_encode(['RspCode' => '00', 'Message' => 'Confirm Success']);
