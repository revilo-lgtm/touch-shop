<?php require 'config.php';
// Nội dung câu chuyện + tầng hương của từng bục (key = id sản phẩm). TỰ SỬA Ở ĐÂY.
$stories = [
  7 => ['story' => 'Một ngày trôi chậm trên sông nước miền Tây, khi trời và nước lững lờ theo cùng một nhịp. Vị ngọt mềm của vải và đào mở đầu, rồi dịu xuống thành sữa gạo béo mịn, thoang thoảng như cơm mới nấu. Hương cuối êm như lớp chăn mỏng buổi sớm. Dành cho những lúc trái tim muốn chậm lại một nhịp.',
        'notes' => [['Hương đầu','Vải, Đào'], ['Hương giữa','Sữa gạo, Hoa táo'], ['Hương cuối','Diên vĩ, Nhựa bồ đề (Benzoin), Xạ hương trắng, Vani']]],
  2 => ['story' => 'Một làn hơi ấm bùi béo của xôi nếp và khoai môn, nhưng không nặng mùi ẩm thực: rễ diên vĩ và hoa violet phủ lên da một lớp phấn mịn thanh nhã. Gỗ, sữa dừa và vani nán lại ở cuối như tấm voan mỏng. Ranh giới giữa mộc mạc và sang trọng.',
        'notes' => [['Hương đầu','Khoai môn, Xôi nếp'], ['Hương giữa','Rễ diên vĩ (Orris), Hoa violet, Trà oolong'], ['Hương cuối','Vanilla, Sữa dừa']]],
  4 => ['story' => 'Hương vườn cây sau cơn dông: mùi đất ngai ngái, sương lạnh và lá non căng nhựa, xen hoa súng, ngọc lan, huệ trắng còn đẫm nước. Trong, tĩnh và xanh mát, đưa người ta về trạng thái nhẹ nhõm, vô lo.',
        'notes' => [['Hương đầu','Hoa súng, Huệ trắng'], ['Hương giữa','Ngọc lan tây, Vetiver, Mùi đất sau mưa'], ['Hương cuối','Lá sen']]],
];
// Thứ tự các bục trưng bày (id sản phẩm trong bảng products)
$order = [7, 2, 4];
$st = $pdo->query('SELECT * FROM products WHERE id IN (7,2,4)');
$byId = [];
foreach ($st->fetchAll() as $r) { $byId[$r['id']] = $r; }
$items = [];
foreach ($order as $id) {
    if (isset($byId[$id])) { $items[] = ['id'=>(int)$id, 'name'=>$byId[$id]['name'], 'price'=>money($byId[$id]['price']), 'story'=>$stories[$id]['story'] ?? '', 'notes'=>$stories[$id]['notes'] ?? []]; }
}
$cartN = array_sum($_SESSION['cart'] ?? []);
?><!DOCTYPE html>
<html lang="vi"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Toouch</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Work+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
html,body{margin:0;height:100%;overflow:hidden;background:#e8dfd0;font:15px "Work Sans",system-ui,sans-serif;color:#14213d}
#stage,#stage canvas{position:fixed;inset:0;width:100%;height:100%;display:block}
#stage canvas{cursor:none;touch-action:none}
.hud{position:fixed;left:0;right:0;z-index:3;display:flex;justify-content:space-between;align-items:center;padding:20px clamp(16px,4vw,48px);color:#fff;mix-blend-mode:difference;pointer-events:none}
.hud a,.hud button{pointer-events:auto;color:#fff;text-decoration:none;background:none;border:0;font:inherit;cursor:pointer}
.hud.top{top:0}.hud.bottom{bottom:0;justify-content:center}
.logo{font:700 26px "Playfair Display",serif}
.hud.top div{display:flex;gap:22px}
#nums{display:flex;gap:18px;align-items:center}
#nums button{font-size:14px;letter-spacing:.12em;opacity:.55;padding:6px 2px;border-bottom:1px solid transparent}
#nums button.on{opacity:1;border-color:#fff}
#nums i{width:28px;height:1px;background:#fff;opacity:.5}
#hint{position:fixed;left:50%;bottom:74px;transform:translateX(-50%);z-index:3;color:#fff;mix-blend-mode:difference;font-size:13px;letter-spacing:.06em;pointer-events:none;transition:opacity .4s}
#info{position:fixed;z-index:4;right:clamp(16px,4vw,48px);top:50%;transform:translateY(-50%);width:min(340px,calc(100% - 32px));max-height:76vh;overflow:auto;padding:22px;background:rgba(255,255,255,.82);backdrop-filter:blur(10px);border:1px solid rgba(20,33,61,.15)}
#info[hidden]{display:none}
#info h2{margin:0 0 4px;font:500 26px "Playfair Display",serif}
#info p{margin:0 0 16px;color:#3a4660}
#iprice{font-weight:500;color:#14213d}#istory{font-size:14.5px;line-height:1.65}#inotes{list-style:none;margin:0 0 18px;padding:12px 0 0;border-top:1px solid rgba(20,33,61,.18);font-size:14px;color:#3a4660}#inotes li{margin:0 0 6px}#inotes b{color:#14213d;font-weight:600}
#info .row{display:flex;gap:10px;flex-wrap:wrap}
#info button{background:#14213d;color:#fff;border:0;padding:11px 18px;font:500 14px "Work Sans",sans-serif;cursor:pointer}
#info button.ghost{background:none;color:#14213d;border:1px solid #14213d}
#disc{position:fixed;right:clamp(16px,4vw,48px);bottom:6px;margin:0;z-index:3;font-size:11px;color:#fff;mix-blend-mode:difference;opacity:.7;pointer-events:none}
#ring{position:fixed;left:0;top:0;width:26px;height:26px;margin:-13px 0 0 -13px;border:1.5px solid #fff;border-radius:50%;z-index:5;pointer-events:none;mix-blend-mode:difference;transition:width .2s,height .2s,margin .2s;opacity:0}
#ring.hot{width:56px;height:56px;margin:-28px 0 0 -28px}
@media(hover:none){#ring{display:none}}
.nowebgl #stage{display:none}.msg{display:none}.nowebgl .msg{display:block;padding:120px 24px;text-align:center}
@media(max-width:700px){.hud.top{flex-wrap:wrap;gap:6px}#disc{left:16px;right:16px;text-align:center;bottom:4px}#info{top:auto;bottom:70px;transform:none;max-height:42vh}.hud.top div{gap:12px;font-size:13px}.logo{font-size:22px}}
</style></head><body>
<div id="stage"></div><div id="ring"></div>
<p class="msg">Trình duyệt của bạn chưa hỗ trợ 3D. <a href="products.php">Xem danh sách sản phẩm</a></p>
<header class="hud top"><a class="logo" href="index.php">Toouch</a>
  <div><a href="products.php">Sản phẩm</a><a href="story.php">Câu chuyện</a><a href="contact.php">Liên hệ</a><a href="cart.php">Giỏ hàng (<?= (int)$cartN ?>)</a></div></header>
<p id="hint">Chạm - nghe câu chuyện của sản phẩm</p>
<aside id="info" hidden>
  <h2 id="iname"></h2><p id="iprice"></p><p id="istory"></p><ul id="inotes"></ul>
  <div class="row">
    <form method="post" action="cart.php"><input type="hidden" name="action" value="add"><input type="hidden" name="id" id="iid"><button>Thêm vào giỏ</button></form>
    <button type="button" class="ghost" id="back">Toàn cảnh</button>
  </div>
</aside>
<p id="disc">Website phục vụ môn học, không phải website chính thức của Toouch.</p>
<footer class="hud bottom"><div id="nums"></div></footer>
<script>window.GALLERY=<?= json_encode($items, JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="gallery.js"></script>
</body></html>
