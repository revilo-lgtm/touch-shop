<?php require 'config.php';
$st = $pdo->prepare('SELECT * FROM orders WHERE id_order=?'); $st->execute([$_GET['id'] ?? '']);
$o = $st->fetch();
header_html('Kết quả thanh toán');
if (!$o) { echo '<p>Không tìm thấy đơn hàng.</p>'; footer_html(); exit; }
$labels = ['paid' => 'Thanh toán thành công', 'failed' => 'Thanh toán thất bại', 'pending' => 'Chờ thanh toán'];
$label = ($o['payment_method'] === 'COD' && $o['status'] === 'pending') ? 'Đặt hàng thành công (thanh toán khi nhận)' : $labels[$o['status']];
?>
<?php if (!empty($_SESSION['pay_error'])) { echo '<p class="failed">' . h($_SESSION['pay_error']) . '</p>'; unset($_SESSION['pay_error']); } ?>
<h1 class="<?= h($o['status']) ?>"><?= h($label) ?></h1>
<div class="box">
  <p>Mã đơn: <strong><?= h($o['id_order']) ?></strong></p>
  <p>Số tiền: <strong><?= money($o['amount']) ?></strong></p>
  <p>Hình thức: <?= h($o['payment_method']) ?></p>
  <p>Mã giao dịch: <?= h($o['transaction_id'] ?: '—') ?></p>
  <p>Người nhận: <?= h($o['customer_name']) ?> - <?= h($o['phone']) ?></p>
  <p>Địa chỉ: <?= h($o['address']) ?></p>
</div>
<p><a href="index.php">Về trang chủ</a></p>
<?php footer_html();
