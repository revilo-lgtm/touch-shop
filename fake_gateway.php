<?php require 'config.php'; require 'payment_gateway.php';
$id = $_GET['id'] ?? '';
$st = $pdo->prepare('SELECT * FROM orders WHERE id_order=?'); $st->execute([$id]);
$order = $st->fetch();
if (!$order) { die('Không tìm thấy đơn hàng'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleCallback($pdo, $id, ($_POST['result'] ?? '') === 'success', 'FAKE' . time());
    header('Location: result.php?id=' . urlencode($id)); exit;
}
header_html('Cổng thanh toán (giả lập)');
?>
<h1>Cổng thanh toán giả lập</h1>
<div class="box">
  <p>Mã đơn: <strong><?= h($order['id_order']) ?></strong></p>
  <p>Số tiền: <strong><?= money($order['amount']) ?></strong></p>
  <p>Hình thức: <strong><?= h($order['payment_method']) ?></strong></p>
  <form method="post">
    <button name="result" value="success">Thanh toán thành công</button>
    <button name="result" value="fail" class="danger">Thanh toán thất bại</button>
  </form>
</div>
<?php footer_html();
