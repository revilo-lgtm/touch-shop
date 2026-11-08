<?php
// MODULE THANH TOÁN: ATM + MoMo (MoMo sandbox), VNPay sandbox, hoặc cổng giả lập.
require_once __DIR__ . '/payment_config.php';

function base_url(): string {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    return ($https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $dir;
}

function client_ip(): string {
    foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $k) {
        if (empty($_SERVER[$k])) continue;
        $ip = trim(explode(',', (string)$_SERVER[$k])[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
    }
    return '127.0.0.1';
}

// Ký HMAC-SHA256 theo đúng thứ tự khóa MoMo quy định (a-z).
function momo_sign(array $data, string $secret, array $keys): string {
    $parts = [];
    foreach ($keys as $k) { $parts[] = $k . '=' . ($data[$k] ?? ''); }
    return hash_hmac('sha256', implode('&', $parts), $secret);
}

// ===== VNPay: hash data (ksort + urlencode) theo tài liệu v2.1.0 =====
function vnpay_hash_data(array $data): string {
    ksort($data);
    $parts = [];
    foreach ($data as $k => $v) {
        if ($v === '' || $v === null) continue;
        $parts[] = urlencode((string)$k) . '=' . urlencode((string)$v);
    }
    return implode('&', $parts);
}

function vnpay_sign(array $data): string {
    $cfg = vnpay_config();
    return hash_hmac('sha512', vnpay_hash_data($data), $cfg['hashSecret']);
}

function vnpay_pay_url(array $order): string {
    $cfg = vnpay_config();
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $createDate = date('YmdHis');
    $expireDate = date('YmdHis', time() + 15 * 60);
    $params = [
        'vnp_Version'   => '2.1.0',
        'vnp_Command'   => 'pay',
        'vnp_TmnCode'   => $cfg['tmnCode'],
        'vnp_Amount'    => (int)$order['amount'] * 100,   // VNPay: VND x 100
        'vnp_CurrCode'  => 'VND',
        'vnp_TxnRef'    => $order['id_order'],
        'vnp_OrderInfo' => 'Thanh toan don hang ' . $order['id_order'],
        'vnp_OrderType' => 'other',
        'vnp_Locale'    => 'vn',
        'vnp_ReturnUrl' => base_url() . '/vnpay_return.php',
        'vnp_IpAddr'    => client_ip(),
        'vnp_CreateDate'=> $createDate,
        'vnp_ExpireDate'=> $expireDate,
    ];
    $params['vnp_SecureHash'] = vnpay_sign($params);
    return $cfg['url'] . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
}

// Chỉ lấy tham số vnp_* (trừ chữ ký) rồi kiểm HMAC-SHA512.
function vnpay_verify(array $input): bool {
    $secureHash = (string)($input['vnp_SecureHash'] ?? '');
    if ($secureHash === '') return false;
    $data = [];
    foreach ($input as $k => $v) {
        if (strpos((string)$k, 'vnp_') === 0 && $k !== 'vnp_SecureHash' && $k !== 'vnp_SecureHashType') {
            $data[$k] = $v;
        }
    }
    return hash_equals(vnpay_sign($data), $secureHash);
}

// Tạo yêu cầu thanh toán, trả về URL để chuyển khách sang trang thanh toán.
function createPayment(array $order): string {
    $method = $order['method'] ?? '';
    if ($method === 'VNPay') {
        return vnpay_pay_url($order);
    }
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
        'requestType' => ($method === 'ATM') ? 'payWithATM' : 'captureWallet',
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
