<?php
session_start();
// ===== THÔNG TIN DATABASE: khi đưa lên hosting, chỉ cần sửa 4 dòng này =====
$DB_HOST = 'localhost';
$DB_NAME = 'touch_shop';
$DB_USER = 'root';
$DB_PASS = '';
$DEBUG   = true;   // đặt false khi chạy thật để không lộ thông tin lỗi
// ===========================================================================
if (!$DEBUG) { ini_set('display_errors', '0'); }
$pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n) { return number_format($n, 0, ',', '.') . 'đ'; }

// Màu nước hoa theo từng sản phẩm (id => màu)
function bottle($id, $name = '') {
    $c = [1=>'#24406b', 2=>'#c4d0dc', 3=>'#7a5240', 4=>'#9fbba5', 5=>'#54486b', 6=>'#e6b9a6'];
    $col = $c[$id] ?? '#24406b';
    return '<svg class="bottle" viewBox="0 0 120 160" role="img" aria-label="Chai ' . h($name) . '">'
        . '<rect x="46" y="8" width="28" height="22" rx="2" fill="#14213d"/>'
        . '<rect x="52" y="30" width="16" height="12" fill="#cfd6df"/>'
        . '<rect x="22" y="42" width="76" height="106" rx="6" fill="#eef1f4" stroke="#14213d" stroke-width="2"/>'
        . '<rect x="26" y="70" width="68" height="74" rx="3" fill="' . $col . '" opacity=".85"/>'
        . '<rect x="36" y="88" width="48" height="34" fill="#fbfbf9"/>'
        . '<text x="60" y="110" text-anchor="middle" font-family="Georgia,serif" font-size="11" fill="#14213d">No.' . (int)$id . '</text>'
        . '</svg>';
}

// Ảnh sản phẩm: đặt file vào thư mục images/ với tên product-<id>.jpg (hoặc .png, .webp, .jpeg)
function product_visual($p) {
    foreach (['jpg','jpeg','png','webp'] as $ext) {
        $file = 'images/product-' . (int)$p['id'] . '.' . $ext;
        if (file_exists(__DIR__ . '/' . $file)) {
            return '<img class="photo" src="' . $file . '?v=' . filemtime(__DIR__ . '/' . $file) . '" alt="' . h($p['name']) . '" loading="lazy">';
        }
    }
    return bottle($p['id'], $p['name']);
}

// Ô ảnh thay thế được: đặt file images/<tên>.jpg|png|webp thì dùng ảnh, không có thì dùng hình dự phòng
function slot_image($name, $fallback = '') {
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $file = 'images/' . $name . '.' . $ext;
        if (file_exists(__DIR__ . '/' . $file)) {
            return '<img class="slot" src="' . $file . '?v=' . filemtime(__DIR__ . '/' . $file) . '" alt="" loading="lazy">';
        }
    }
    return $fallback;
}

function nav_links($active = '') {
    $items = ['home'=>['index.php','Trang chủ'], 'products'=>['products.php','Sản phẩm'], 'story'=>['story.php','Câu chuyện'], 'contact'=>['contact.php','Liên hệ']];
    $out = '';
    foreach ($items as $k => $v) {
        $out .= '<a href="' . $v[0] . '"' . ($k === $active ? ' class="on" aria-current="page"' : '') . '>' . $v[1] . '</a>';
    }
    return $out;
}

function product_card($p) {
    return '<article class="item"><div class="tile">' . product_visual($p) . '</div>'
        . '<h3>' . h($p['name']) . '</h3><p class="desc">' . h($p['description']) . '</p>'
        . '<div class="buy"><span class="price">' . money($p['price']) . '</span>'
        . '<form method="post" action="cart.php"><input type="hidden" name="action" value="add">'
        . '<input type="hidden" name="id" value="' . (int)$p['id'] . '"><button class="btn small-btn">Thêm vào giỏ</button></form></div></article>';
}

function header_html($title, $active = '') {
    $n = array_sum($_SESSION['cart'] ?? []);
    echo '<!DOCTYPE html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
       . '<title>' . h($title) . ' | Documents</title>'
       . ''
       . '<link rel="stylesheet" href="fonts/fonts.css">'
       . '<link rel="stylesheet" href="style.css?v=' . filemtime(__DIR__ . '/style.css') . '"></head><body>'
       . '<header class="top"><a href="index.php" class="logo">Toouch</a>'
       . '<nav>' . nav_links($active) . '<a href="cart.php" class="cart' . ($active === 'cart' ? ' on' : '') . '">Giỏ hàng<span>' . $n . '</span></a></nav></header><main>';
}
function footer_html() {
    echo '</main><footer><strong>Toouch</strong><p>Nước hoa thủ công Việt Nam.</p><nav class="fnav">' . nav_links('') . '</nav>'
       . '<p class="small">Website phục vụ môn học, không phải website chính thức của Toouch.</p></footer></body></html>';
}
