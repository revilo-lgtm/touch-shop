<?php require 'config.php';
header_html('Sản phẩm', 'products');
$products = $pdo->query('SELECT * FROM products ORDER BY id')->fetchAll();
?>
<div class="page-head">
  <h1>Sản phẩm</h1>
  <p>Chọn mùi hương của bạn và thêm vào giỏ hàng.</p>
</div>
<div class="catalog">
  <?php foreach ($products as $p) echo product_card($p); ?>
</div>
<?php footer_html();
