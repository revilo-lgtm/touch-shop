<?php require 'config.php'; require 'payment_gateway.php';
// VNPay đưa khách quay lại đây sau khi thanh toán xong (hoặc huỷ).
$id = (string)($_GET['vnp_TxnRef'] ?? '');
$st = $pdo->prepare('SELECT * FROM orders WHERE id_order=?'); $st->execute([$id]);
$o = $st->fetch();
$amountOk = $o && (int)($_GET['vnp_Amount'] ?? -1) === ((int)$o['amount'] * 100);
if ($o && vnpay_verify($_GET) && $amountOk) {
    handleCallback($pdo, $id, (string)($_GET['vnp_ResponseCode'] ?? '') === '00', (string)($_GET['vnp_TransactionNo'] ?? ''));
} elseif ($o) {
    $_SESSION['pay_error'] = 'Dữ liệu VNPay trả về không hợp lệ (sai chữ ký hoặc số tiền), đơn chưa được xác nhận.';
}
header('Location: result.php?id=' . urlencode($id));
