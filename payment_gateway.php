<?php
// MODULE THANH TOÁN: ATM + MoMo qua MoMo sandbox, hoặc cổng giả lập (đổi PAYMENT_MODE trong payment_config.php).
require_once __DIR__ . '/payment_config.php';

function base_url(): string {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    return ($https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $dir;
}

// Ký HMAC-SHA256 theo đúng thứ tự khóa MoMo quy định (a-z).
function momo_sign(array $data, string $secret, array $keys): string {
    $parts = [];
    foreach ($keys as $k) { $parts[] = $k . '=' . ($data[$k] ?? ''); }
    return hash_hmac('sha256', implode('&', $parts), $secret);
}

// Tạo yêu cầu thanh toán, trả về URL để chuyển khách sang trang thanh toán.
function createPayment(array $order): string {
    if (PAYMENT_MODE !== 'momo') {
        return 'fake_gateway.php?id=' . urlencode($order['id_order']);
    }
    if (!function_exists('curl_init')) { throw new RuntimeException('PHP chưa bật extension cURL.'); }
    $cfg = momo_config();
    $req = [
        'partnerCode' => $cfg['partnerCode'],
        'partnerName' => 'Toouch Demo',
        'storeId'     => 'ToouchDemo',
        'requestId'   => $order['id_order'] . '-' . time(),          // mỗi lần gọi một mã khác nhau
        'amount'      => (int)$order['amount'],
        'orderId'     => $order['id_order'],
        'orderInfo'   => 'Thanh toan don hang ' . $order['id_order'],
        'redirectUrl' => base_url() . '/momo_return.php',
        'ipnUrl'      => base_url() . '/momo_ipn.php',
        'lang'        => 'vi',
        'extraData'   => '',
        // ATM = thẻ nội địa; MoMo = ví MoMo
        'requestType' => (($order['method'] ?? '') === 'ATM') ? 'payWithATM' : 'captureWallet',
    ];
    $req['signature'] = momo_sign($req + ['accessKey' => $cfg['accessKey']], $cfg['secretKey'],
        ['accessKey','amount','extraData','ipnUrl','orderId','orderInfo','partnerCode','redirectUrl','requestId','requestType']);

    $ch = curl_init($cfg['endpoint']);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($req, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json; charset=UTF-8'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT        => 30,                                 // MoMo yêu cầu tối thiểu 30 giây
        CURLOPT_SSL_VERIFYPEER => $cfg['verify_ssl'],
        CURLOPT_SSL_VERIFYHOST => $cfg['verify_ssl'] ? 2 : 0,
    ]);
    $res = curl_exec($ch); $err = curl_error($ch); curl_close($ch);
    if ($res === false) { throw new RuntimeException('Không kết nối được tới MoMo: ' . $err); }
    $j = json_decode($res, true);
    if (!is_array($j) || ($j['resultCode'] ?? -1) !== 0 || empty($j['payUrl'])) {
        throw new RuntimeException('MoMo từ chối yêu cầu: ' . ($j['message'] ?? $res) . ' (mã ' . ($j['resultCode'] ?? '?') . ')');
    }
    return $j['payUrl'];
}

// Kiểm tra chữ ký MoMo gửi về (redirect hoặc IPN) để chắc dữ liệu không bị giả mạo.
function momo_verify(array $p): bool {
    $cfg = momo_config();
    $p['accessKey'] = $cfg['accessKey'];
    $sig = momo_sign($p, $cfg['secretKey'],
        ['accessKey','amount','extraData','message','orderId','orderInfo','orderType','partnerCode','payType','requestId','responseTime','resultCode','transId']);
    return hash_equals($sig, (string)($p['signature'] ?? ''));
}

// Cập nhật trạng thái đơn trong database (chỉ đổi khi đang pending).
function handleCallback(PDO $pdo, string $idOrder, bool $success, string $txnId): void {
    $st = $pdo->prepare("UPDATE orders SET status=?, transaction_id=? WHERE id_order=? AND status='pending'");
    $st->execute([$success ? 'paid' : 'failed', $txnId, $idOrder]);
}
