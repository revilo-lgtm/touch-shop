<?php require 'config.php'; require 'payment_gateway.php';
// MoMo đưa khách quay lại đây sau khi thanh toán xong (hoặc huỷ).
$id = (string)($_GET['orderId'] ?? '');
$st = $pdo->prepare('SELECT * FROM orders WHERE id_order=?'); $st->execute([$id]);
$o = $st->fetch();
if ($o && momo_verify($_GET) && (int)($_GET['amount'] ?? -1) === (int)$o['amount']) {
    handleCallback($pdo, $id, (string)($_GET['resultCode'] ?? '') === '0', (string)($_GET['transId'] ?? ''));
} elseif ($o) {
    $_SESSION['pay_error'] = 'Dữ liệu MoMo trả về không hợp lệ (sai chữ ký hoặc số tiền), đơn chưa được xác nhận.';
}
header('Location: result.php?id=' . urlencode($id));
