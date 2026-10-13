<?php require 'config.php';
$cart = &$_SESSION['cart'];
$cart = $cart ?? [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'add') { $cart[$id] = ($cart[$id] ?? 0) + 1; }
    if (($_POST['action'] ?? '') === 'remove') { unset($cart[$id]); }
    header('Location: cart.php'); exit;
}
header_html('Giỏ hàng', 'cart');
$items = []; $total = 0;
if ($cart) {
    $in = implode(',', array_fill(0, count($cart), '?'));
    $st = $pdo->prepare("SELECT * FROM products WHERE id IN ($in)");
    $st->execute(array_keys($cart));
    foreach ($st->fetchAll() as $p) {
        $p['qty'] = $cart[$p['id']];
        $total += $p['price'] * $p['qty'];
        $items[] = $p;
    }
}
?>
<h1>Giỏ hàng của bạn</h1>
<?php if (!$items): ?>
  <p>Giỏ hàng trống. <a href="index.php">Tiếp tục mua sắm</a></p>
<?php else: ?>
<table>
  <tr><th>Sản phẩm</th><th>SL</th><th>Đơn giá</th><th></th></tr>
  <?php foreach ($items as $i): ?>
  <tr>
    <td><?= h($i['name']) ?></td><td><?= $i['qty'] ?></td><td><?= money($i['price']) ?></td>
    <td><form method="post"><input type="hidden" name="action" value="remove"><input type="hidden" name="id" value="<?= $i['id'] ?>"><button class="link">Xóa</button></form></td>
  </tr>
  <?php endforeach; ?>
  <tr><td colspan="2"><strong>Tổng cộng</strong></td><td colspan="2"><strong><?= money($total) ?></strong></td></tr>
</table>

<h2>Giao hàng và thanh toán</h2>
<form method="post" action="checkout.php" class="form">
  <input name="customer_name" placeholder="Họ tên" required>
  <input name="phone" placeholder="Số điện thoại" required pattern="[0-9]{9,11}">
  <input name="address" placeholder="Địa chỉ giao hàng" required>
  <label><input type="radio" name="payment_method" value="ATM" required> Thẻ ATM nội địa</label>
  <label><input type="radio" name="payment_method" value="MoMo"> Ví MoMo</label>
  <label><input type="radio" name="payment_method" value="COD"> Thanh toán khi nhận hàng</label>
  <button>Đặt hàng</button>
</form>
<?php endif; footer_html();
