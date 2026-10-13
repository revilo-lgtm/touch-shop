<?php require 'config.php'; require 'payment_gateway.php';
$cart = $_SESSION['cart'] ?? [];
$method = $_POST['payment_method'] ?? '';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$cart || !in_array($method, ['ATM','MoMo','COD'], true)) {
    header('Location: cart.php'); exit;
}
// Tính tiền từ database, không tin dữ liệu từ trình duyệt.
$in = implode(',', array_fill(0, count($cart), '?'));
$st = $pdo->prepare("SELECT * FROM products WHERE id IN ($in)");
$st->execute(array_keys($cart));
$products = $st->fetchAll();
$amount = 0;
foreach ($products as $p) { $amount += $p['price'] * $cart[$p['id']]; }

$idOrder = 'DOC' . date('ymdHis') . random_int(100, 999);
$pdo->beginTransaction();
$pdo->prepare('INSERT INTO orders (id_order, customer_name, phone, address, amount, payment_method) VALUES (?,?,?,?,?,?)')
    ->execute([$idOrder, trim($_POST['customer_name']), trim($_POST['phone']), trim($_POST['address']), $amount, $method]);
$ins = $pdo->prepare('INSERT INTO order_items (id_order, product_id, quantity, price) VALUES (?,?,?,?)');
foreach ($products as $p) { $ins->execute([$idOrder, $p['id'], $cart[$p['id']], $p['price']]); }
$pdo->commit();
unset($_SESSION['cart']);

if ($method === 'COD') { header('Location: result.php?id=' . $idOrder); exit; }
try {
    $url = createPayment(['id_order' => $idOrder, 'amount' => $amount, 'method' => $method]);
    header('Location: ' . $url);
} catch (Throwable $e) {
    $_SESSION['pay_error'] = $e->getMessage();   // hiện lỗi ở trang kết quả, đơn vẫn ở trạng thái chờ thanh toán
    header('Location: result.php?id=' . urlencode($idOrder));
}
