<?php require 'config.php';
// Ảnh chi tiết của sản phẩm: images/3d-<id>.png|jpg (ảnh chính) và images/detail-<id>-2.jpg, detail-<id>-3.jpg... (ảnh thêm)
function detail_photos($id) {
    $dir = __DIR__ . '/images'; $out = [];
    if (is_dir($dir)) {
        foreach (scandir($dir) as $f) {
            if (preg_match('/^(?:3d-' . (int)$id . '|detail-' . (int)$id . '-\d+)\.(?:png|jpe?g|webp)$/i', $f)) {
                $out[] = 'images/' . $f . '?v=' . filemtime($dir . '/' . $f);
            }
        }
    }
    natsort($out); return array_values($out);
}
// Nội dung câu chuyện + tầng hương của từng bục (key = id sản phẩm). TỰ SỬA Ở ĐÂY.
$stories = [
  7 => ['caption' => 'Bông lúa, lá dừa, gáo dừa và những viên đá, xếp thành một thế cân bằng mong manh như một buổi chiều miền Tây.',
        'story' => 'Một ngày trôi chậm trên sông nước miền Tây, khi trời và nước lững lờ theo cùng một nhịp. Vị ngọt mềm của vải và đào mở đầu, rồi dịu xuống thành sữa gạo béo mịn, thoang thoảng như cơm mới nấu. Hương cuối êm như lớp chăn mỏng buổi sớm. Dành cho những lúc trái tim muốn chậm lại một nhịp.',
        'notes' => [['Hương đầu','Vải, Đào'], ['Hương giữa','Sữa gạo, Hoa táo'], ['Hương cuối','Diên vĩ, Nhựa bồ đề (Benzoin), Xạ hương trắng, Vani']]],
  2 => ['caption' => 'Sắc tím của nếp cẩm và vị bùi béo của dừa, đặt cạnh nhau như một món ăn của ký ức.',
        'story' => 'Một làn hơi ấm bùi béo của xôi nếp và khoai môn, nhưng không nặng mùi ẩm thực: rễ diên vĩ và hoa violet phủ lên da một lớp phấn mịn thanh nhã. Gỗ, sữa dừa và vani nán lại ở cuối như tấm voan mỏng. Ranh giới giữa mộc mạc và sang trọng.',
        'notes' => [['Hương đầu','Khoai môn, Xôi nếp'], ['Hương giữa','Rễ diên vĩ (Orris), Hoa violet, Trà oolong'], ['Hương cuối','Vanilla, Sữa dừa']]],
  4 => ['caption' => 'Nụ hoa, cánh bướm và sắc xanh sau cơn mưa trong một góc vườn tĩnh lặng.',
        'story' => 'Hương vườn cây sau cơn dông: mùi đất ngai ngái, sương lạnh và lá non căng nhựa, xen hoa súng, ngọc lan, huệ trắng còn đẫm nước. Trong, tĩnh và xanh mát, đưa người ta về trạng thái nhẹ nhõm, vô lo.',
        'notes' => [['Hương đầu','Hoa súng, Huệ trắng'], ['Hương giữa','Ngọc lan tây, Vetiver, Mùi đất sau mưa'], ['Hương cuối','Lá sen']]],
];
$order = [7, 2, 4];
$st = $pdo->query('SELECT * FROM products WHERE id IN (7,2,4)');
$byId = [];
foreach ($st->fetchAll() as $r) { $byId[$r['id']] = $r; }
$items = []; $names = [];
foreach ($order as $id) {
    if (isset($byId[$id])) {
        $names[$id] = $byId[$id]['name'];
        $items[] = ['id'=>(int)$id, 'name'=>$byId[$id]['name'], 'price'=>money($byId[$id]['price']),
                    'story'=>$stories[$id]['story'] ?? '', 'notes'=>$stories[$id]['notes'] ?? [],
                    'caption'=>$stories[$id]['caption'] ?? '', 'photos'=>detail_photos($id)];
    }
}
$cartN = array_sum($_SESSION['cart'] ?? []);
$more = $pdo->query('SELECT * FROM products ORDER BY FIELD(id,7,2,4,1,3,5,6) LIMIT 6')->fetchAll();
?><!DOCTYPE html>
<html lang="vi"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Toouch</title>

<link rel="stylesheet" href="fonts/fonts.css">
<style>
:root{--ink:#14213d;--ink2:#3a4660;--bg:#e5d8c4}
*{box-sizing:border-box}
html{scroll-behavior:smooth}
html,body{margin:0;background:var(--bg);font:15px/1.5 "Work Sans",system-ui,sans-serif;color:var(--ink)}
body.sel{overflow:hidden}
#hero{position:relative;height:100vh;height:100svh;min-height:600px;display:flex;flex-direction:column;overflow:hidden}
.top{position:absolute;top:0;left:0;right:0;z-index:6;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;padding:16px clamp(16px,4vw,48px)}
.top a{color:var(--ink);text-decoration:none}
.logo{font:700 26px "Playfair Display",serif}
.top div{display:flex;gap:22px}
.top div a:hover{text-decoration:underline}
#home{flex:1;min-height:0;display:flex}
#photo{flex:1;min-width:0;position:relative;overflow:hidden;container-type:size;display:grid;place-items:center}

/* Máy quay: parallax theo chuột + đẩy gần vào sản phẩm đang chọn */
.cam{position:relative;width:min(100cqw,calc(100cqh * 1.7876));aspect-ratio:1153 / 645;
  transform:translate(var(--px,0px),var(--py,0px)) scale(var(--s,1));transform-origin:var(--o,50% 50%);
  transition:transform 1.1s cubic-bezier(.2,.7,.2,1),transform-origin 1.1s cubic-bezier(.2,.7,.2,1)}
/* Hơi thở: phóng to thu nhỏ rất chậm */
.breathe{position:absolute;inset:0;animation:breathe 26s ease-in-out infinite alternate;transform-origin:50% 60%}
@keyframes breathe{from{transform:scale(1)}to{transform:scale(1.045)}}
.ext{position:absolute;display:block;width:273.461%;left:-86.730%;top:-54.264%;max-width:none;user-select:none;-webkit-user-drag:none}
.spot-light{position:absolute;inset:-300%;pointer-events:none;opacity:0;transition:opacity .8s}
#home.sel .spot-light{opacity:1}
.breathe svg{position:absolute;inset:0;width:100%;height:100%}

.spot{cursor:pointer;outline:none}
.spot rect{fill:transparent;pointer-events:all}
.spot .dot{fill:#fff;stroke:rgba(20,33,61,.45);stroke-width:1.5;pointer-events:none;transition:opacity .4s}
.spot .ring{fill:none;stroke:#fff;stroke-width:2;pointer-events:none;transform-box:fill-box;transform-origin:center;animation:pulse 2.2s ease-out infinite;transition:opacity .4s}
@keyframes pulse{0%{transform:scale(1);opacity:.9}100%{transform:scale(3.2);opacity:0}}
#home.sel .dot,#home.sel .ring{opacity:0;animation:none}
.spot .tag{font:500 22px "Playfair Display",serif;fill:var(--ink);stroke:rgba(255,250,240,.9);stroke-width:5px;paint-order:stroke;opacity:0;transition:opacity .3s;pointer-events:none}
.spot:hover .tag,.spot:focus-visible .tag{opacity:1}
#home.sel .spot .tag{opacity:0}

#dust{position:absolute;inset:0;width:100%;height:100%;pointer-events:none;z-index:2}
#hint{position:absolute;z-index:3;top:84px;left:0;right:0;text-align:center;margin:0;font-size:14px;letter-spacing:.06em;color:var(--ink);pointer-events:none;transition:opacity .3s}
#home.sel #hint{opacity:0}
#nums{position:absolute;z-index:3;bottom:34px;left:0;right:0;display:flex;gap:18px;justify-content:center;align-items:center}
#nums button{background:none;border:0;border-bottom:1px solid transparent;font:500 14px "Work Sans",sans-serif;letter-spacing:.12em;color:var(--ink);opacity:.6;padding:6px 2px;cursor:pointer}
#nums button.on{opacity:1;border-color:var(--ink)}
#nums i{width:28px;height:1px;background:var(--ink);opacity:.4}
#disc{position:absolute;z-index:3;left:0;right:0;bottom:8px;text-align:center;margin:0;font-size:11px;color:var(--ink2);opacity:.85;pointer-events:none}

#info{flex:0 0 auto;width:0;overflow:hidden;background:#fbf8f3;box-shadow:-1px 0 0 rgba(20,33,61,.12);position:relative;z-index:5;transition:width .5s cubic-bezier(.2,.7,.2,1)}
#home.sel #info{width:min(400px,38vw);overflow-y:auto;overflow-x:hidden}
#info .in{width:min(400px,38vw);padding:64px 30px 36px}
#info h2{margin:0 0 4px;font:500 30px "Playfair Display",serif;line-height:1.2}
#iprice{margin:0 0 16px;font-weight:500}
#istory{font-size:15px;line-height:1.7;color:var(--ink2);margin:0 0 18px}
#inotes{list-style:none;margin:0 0 22px;padding:14px 0 0;border-top:1px solid rgba(20,33,61,.18);font-size:14.5px;color:var(--ink2)}
#inotes li{margin:0 0 8px}#inotes b{color:var(--ink);font-weight:600}
#info .row{display:flex;gap:10px;flex-wrap:wrap}
#info button{background:var(--ink);color:#fff;border:0;padding:12px 20px;font:500 14px "Work Sans",sans-serif;cursor:pointer;border-radius:2px}
#info button.ghost{background:none;color:var(--ink);border:1px solid var(--ink)}
:focus-visible{outline:3px solid #4c7be0;outline-offset:2px}
@media(max-width:800px){
  #home{flex-direction:column;justify-content:center;overflow:auto;padding-top:64px}
  #home.sel{justify-content:flex-start}
  #photo{flex:0 0 auto;container-type:inline-size;display:block}
  .cam{width:100%}
  #info{width:100%!important;max-height:0;transition:max-height .5s}
  #home.sel #info{max-height:none;flex:1}
  #info .in{width:100%;padding:22px 20px 30px}
  #nums{position:static;margin:12px 0 4px}
  #hint{position:static;margin:10px 0 0}
  #disc{position:static;padding:6px 0}
}
@media(prefers-reduced-motion:reduce){.breathe,.ring{animation:none}.cam,#info{transition:none}#dust{display:none}}

/* ===== Chế độ xem chi tiết: ẩn menu, hiện ảnh riêng của sản phẩm ===== */
.top{transition:transform .6s cubic-bezier(.2,.7,.2,1),opacity .5s,visibility 0s}
body.sel .top{transform:translateY(-110%);opacity:0;visibility:hidden;pointer-events:none;transition:transform .6s cubic-bezier(.2,.7,.2,1),opacity .5s,visibility 0s .6s}
.detail{position:absolute;inset:0;z-index:1;opacity:0;pointer-events:none;background:#d9cbb3;transition:opacity .5s}
#home.sel .detail.has{opacity:1;pointer-events:auto;transition:opacity 1s ease .5s}
.dbg{position:absolute;inset:-8%;background-size:cover;background-position:center;filter:blur(36px) saturate(1.1) brightness(.92);transform:scale(1.1)}
.dimg{position:absolute;inset:0;width:100%;height:100%;object-fit:contain;transform:scale(1.07);transition:transform 1.6s cubic-bezier(.2,.7,.2,1),opacity .35s;
  -webkit-mask-image:linear-gradient(to right,transparent,#000 7%,#000 93%,transparent),linear-gradient(to bottom,transparent,#000 5%,#000 95%,transparent);-webkit-mask-composite:source-in;
  mask-image:linear-gradient(to right,transparent,#000 7%,#000 93%,transparent),linear-gradient(to bottom,transparent,#000 5%,#000 95%,transparent);mask-composite:intersect}
#home.sel .detail.has .dimg{transform:scale(1);transition-delay:.5s,0s}
.dimg.swap{opacity:0}
.detail::after{content:"";position:absolute;left:0;right:0;bottom:0;height:36%;background:linear-gradient(to top,rgba(20,14,8,.62),transparent);pointer-events:none}
.cap{position:absolute;z-index:2;left:0;right:0;bottom:92px;margin:0;padding:0 clamp(24px,6vw,80px);text-align:center;color:#fff;font:italic 500 clamp(16px,1.5vw,20px)/1.55 "Playfair Display",serif;text-wrap:balance;text-shadow:0 1px 8px rgba(0,0,0,.35);opacity:0;transform:translateY(10px);transition:opacity .8s,transform .8s}
#home.sel .detail.has .cap{opacity:1;transform:none;transition-delay:1.1s}
.dots{position:absolute;z-index:2;right:22px;top:50%;transform:translateY(-50%);display:flex;flex-direction:column;gap:10px}
.dots button{width:9px;height:9px;padding:0;border-radius:50%;border:1.5px solid #fff;background:transparent;cursor:pointer}
.dots button.on{background:#fff}
.backlink{position:absolute;z-index:4;top:22px;left:clamp(16px,3vw,36px);background:rgba(20,14,8,.38);color:#fff;border:0;padding:9px 16px;border-radius:20px;font:500 14px "Work Sans",sans-serif;cursor:pointer;backdrop-filter:blur(6px);opacity:0;pointer-events:none;transition:opacity .4s}
#home.sel .backlink{opacity:1;pointer-events:auto;transition-delay:.7s}
#home.sel #nums button{color:#fff}#home.sel #nums i{background:#fff}#home.sel #disc{color:#fff}
@media(max-width:800px){#home.sel #photo{height:56vh;transition:height .5s}}

/* ===== Phần nội dung bên dưới trang chủ ===== */
.scrollcue{position:absolute;z-index:3;right:clamp(16px,4vw,48px);bottom:32px;color:var(--ink);text-decoration:none;font-size:13px;letter-spacing:.08em;transition:opacity .3s}
#home.sel .scrollcue{opacity:0;pointer-events:none}
.below{background:#f4efe7;color:var(--ink)}
.below a{color:inherit}
.wrap{max-width:1120px;margin:0 auto;padding:64px clamp(16px,4vw,32px)}
.below h2{font:500 clamp(26px,3vw,36px)/1.2 "Playfair Display",serif;margin:0 0 8px}
.below h3{font:500 21px/1.2 "Playfair Display",serif;margin:0}
.lead{color:var(--ink2);margin:0 0 24px;max-width:56ch}
.sec-head{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;margin-bottom:8px}
.sec-head a{font-weight:500;margin-bottom:24px}
.btn{display:inline-block;background:var(--ink);color:#fff!important;border:0;padding:12px 24px;font:500 15px "Work Sans",sans-serif;cursor:pointer;text-decoration:none;border-radius:2px}
.btn:hover{background:#243659}
.small-btn{padding:9px 16px}
.catalog{display:grid;grid-template-columns:repeat(3,1fr);border-top:1px solid #d8d1c4;border-left:1px solid #d8d1c4;background:#fff}
.item{display:flex;flex-direction:column;padding:20px;border-right:1px solid #d8d1c4;border-bottom:1px solid #d8d1c4}
.tile{background:#e9e4da;padding:20px;display:flex;justify-content:center;margin-bottom:18px}
.tile:has(.photo){padding:0}
.photo{display:block;width:100%;aspect-ratio:4/5;object-fit:cover}
.bottle{width:110px;height:auto;display:block}
.desc{color:var(--ink2);font-size:15px;margin:8px 0 18px;flex:1}
.buy{display:flex;justify-content:space-between;align-items:center;gap:12px}
.buy form{margin:0}
.price{font-weight:600;font-size:18px}
.split{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:48px;align-items:center}
.split>*{min-width:0}
.tile.big{margin:0;padding:48px}.tile.big .bottle{width:150px}
.notes{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:8px}
.notes>div{background:#fff;border:1px solid #d8d1c4;padding:24px}
.notes span{font:500 28px "Playfair Display",serif;color:var(--ink2)}
.notes h3{margin:8px 0}.notes p{margin:0;color:var(--ink2);font-size:15px}
.foot{background:var(--ink);color:#dfe5ee;padding:40px clamp(16px,4vw,48px)}
.foot strong{font:700 22px "Playfair Display",serif;color:#fff}
.foot p{margin:6px 0;max-width:60ch}.foot .small{font-size:13px;opacity:.7}
.fnav{display:flex;gap:20px;flex-wrap:wrap;margin:14px 0}.fnav a{color:#dfe5ee;text-decoration:none}.fnav a:hover{text-decoration:underline}
.tile:has(.slot),.tile.big:has(.slot){padding:0;overflow:hidden}
.slot{display:block;width:100%;max-width:100%;aspect-ratio:4/3;object-fit:cover}
@media(max-width:860px){.catalog{grid-template-columns:repeat(2,1fr)}.split{grid-template-columns:1fr}.notes{grid-template-columns:1fr}}
@media(max-width:800px){.scrollcue{display:none}}
@media(max-width:520px){.catalog{grid-template-columns:1fr}}
</style></head><body>
<section id="hero">
<header class="top"><a class="logo" href="index.php">Toouch</a>
  <div><a href="products.php">Sản phẩm</a><a href="story.php">Câu chuyện</a><a href="contact.php">Liên hệ</a><a href="cart.php">Giỏ hàng (<?= (int)$cartN ?>)</a></div></header>
<main id="home">
  <section id="photo">
    <p id="hint">Chạm - nghe câu chuyện của sản phẩm</p>
    <div class="cam" id="cam"><div class="breathe">
      <img class="ext" src="images/overview-wide.jpg" alt="Ba chai nước hoa Toouch trên bục đá">
      <div class="spot-light" id="light"></div>
      <svg viewBox="0 0 1153 645" aria-label="Chọn một sản phẩm">
<g class="spot" data-id="7" tabindex="0" role="button"><title><?= h($names[7] ?? '') ?></title><rect x="218" y="188" width="184" height="317" rx="14"/><rect x="134" y="503" width="324" height="89" rx="14"/><circle class="dot" cx="318" cy="396" r="7"/><circle class="ring" cx="318" cy="396" r="7"/><text class="tag" x="310" y="168" text-anchor="middle"><?= h($names[7] ?? '') ?></text></g>
<g class="spot" data-id="2" tabindex="0" role="button"><title><?= h($names[2] ?? '') ?></title><rect x="497" y="106" width="162" height="280" rx="14"/><rect x="424" y="384" width="312" height="119" rx="14"/><rect x="458" y="500" width="210" height="82" rx="14"/><circle class="dot" cx="577" cy="288" r="7"/><circle class="ring" cx="577" cy="288" r="7"/><text class="tag" x="578" y="88" text-anchor="middle"><?= h($names[2] ?? '') ?></text></g>
<g class="spot" data-id="4" tabindex="0" role="button"><title><?= h($names[4] ?? '') ?></title><rect x="758" y="226" width="162" height="281" rx="14"/><rect x="668" y="503" width="348" height="90" rx="14"/><circle class="dot" cx="838" cy="406" r="7"/><circle class="ring" cx="838" cy="406" r="7"/><text class="tag" x="839" y="206" text-anchor="middle"><?= h($names[4] ?? '') ?></text></g>
      </svg>
    </div></div>
    <div class="detail" id="detail"><div class="dbg"></div><img class="dimg" alt="Ảnh sản phẩm"><p class="cap" id="cap"></p><div class="dots" id="dots"></div></div>
    <button type="button" class="backlink" id="back2">← Toàn cảnh</button>
    <canvas id="dust"></canvas>
    <a class="scrollcue" href="#below">Cuộn xuống ↓</a>
    <div id="nums"></div>
    <p id="disc">Website phục vụ môn học, không phải website chính thức của Toouch.</p>
  </section>
  <aside id="info"><div class="in">
    <h2 id="iname"></h2><p id="iprice"></p><p id="istory"></p><ul id="inotes"></ul>
    <div class="row">
      <form method="post" action="cart.php"><input type="hidden" name="action" value="add"><input type="hidden" name="id" id="iid"><button>Thêm vào giỏ</button></form>
      <button type="button" class="ghost" id="back">Toàn cảnh</button>
    </div>
  </div></aside>
</main>
</section>

<div id="below" class="below">
  <section class="wrap">
    <div class="sec-head"><div><h2>Sản phẩm</h2><p class="lead">Chọn mùi hương hợp với bạn nhất.</p></div><a href="products.php">Xem tất cả →</a></div>
    <div class="catalog"><?php foreach ($more as $p) echo product_card($p); ?></div>
  </section>

  <section class="wrap split">
    <div>
      <h2>Câu chuyện</h2>
      <!-- TỰ VIẾT LẠI: đoạn giới thiệu ngắn về câu chuyện thương hiệu -->
      <p class="lead">Mùi hương có thể đưa ta trở về một buổi chiều cũ, một căn phòng quen, một người đã lâu không gặp. Mỗi chai nước hoa là một lần chạm vào ký ức như thế.</p>
      <a class="btn" href="story.php">Đọc câu chuyện</a>
    </div>
    <div class="tile big"><?= slot_image('story-home', bottle(3, 'Chai nước hoa')) ?></div>
  </section>

  <section class="wrap">
    <h2>Hành trình của một mùi hương</h2>
    <p class="lead">Mỗi chai nước hoa mở ra theo ba tầng hương, từ ấn tượng đầu tiên đến dư vị đọng lại trên da.</p>
    <div class="notes">
      <div><span>01</span><h3>Hương đầu</h3><p>Ấn tượng đầu tiên, thường tươi và nhẹ, xuất hiện ngay khi xịt và tan nhanh sau vài phút.</p></div>
      <div><span>02</span><h3>Hương giữa</h3><p>Phần cốt lõi của mùi hương, hiện ra khi hương đầu tan dần và là thứ người ngửi nhớ nhất.</p></div>
      <div><span>03</span><h3>Hương cuối</h3><p>Lớp nền ấm và lâu phai, còn đọng trên da và quần áo hàng giờ sau đó.</p></div>
    </div>
  </section>

  <section class="wrap">
    <h2>Đặt hàng dễ dàng</h2>
    <div class="notes steps">
      <div><span>01</span><h3>Chọn mùi hương</h3><p>Xem câu chuyện và tầng hương của từng sản phẩm.</p></div>
      <div><span>02</span><h3>Thêm vào giỏ</h3><p>Kiểm tra giỏ hàng và nhập thông tin giao hàng.</p></div>
      <div><span>03</span><h3>Thanh toán</h3><p>Chọn thẻ ATM, ví MoMo hoặc thanh toán khi nhận hàng.</p></div>
    </div>
    <p style="margin:32px 0 0"><a class="btn" href="products.php">Khám phá sản phẩm</a></p>
  </section>

  <footer class="foot">
    <strong>Toouch</strong>
    <p>Nước hoa thủ công Việt Nam.</p>
    <nav class="fnav"><?= nav_links('') ?></nav>
    <p class="small">Website phục vụ môn học, không phải website chính thức của Toouch.</p>
  </footer>
</div>
<script>
var D = <?= json_encode($items, JSON_UNESCAPED_UNICODE) ?>;
(function () {
  var $ = function (id) { return document.getElementById(id); };
  var home = $('home'), nums = $('nums'), cam = $('cam'), light = $('light'), photo = $('photo');
  var spots = [].slice.call(document.querySelectorAll('.spot'));
  var ORI = { 7: [0.268, 0.54], 2: [0.5, 0.38], 4: [0.726, 0.57] };   // tâm từng sản phẩm trong ảnh (tỉ lệ 0-1)
  var ZOOM = 1.08;                                                      // mức đẩy gần khi chọn sản phẩm
  var byId = {}; D.forEach(function (d) { byId[d.id] = d; });
  spots.forEach(function (s) { if (!byId[s.dataset.id]) s.style.display = 'none'; });

  function go(id) {
    var d = id && byId[id];
    home.classList.toggle('sel', !!d); document.body.classList.toggle('sel', !!d);
    [].forEach.call(nums.querySelectorAll('button'), function (b) { b.classList.toggle('on', !!d && +b.dataset.id === +id); });
    if (!d) { cam.style.setProperty('--s', 1); return; }
    var o = ORI[id] || [0.5, 0.5];
    cam.style.setProperty('--o', (o[0] * 100) + '% ' + (o[1] * 100) + '%');
    cam.style.setProperty('--s', (d.photos && d.photos.length) ? 2.4 : ZOOM);
    // Đèn sân khấu: làm tối xung quanh sản phẩm đang chọn
    light.style.backgroundImage = 'radial-gradient(ellipse 4.6% 6.8% at ' + ((3 + o[0]) / 7 * 100).toFixed(2) + '% ' + ((3 + o[1]) / 7 * 100).toFixed(2) +
      '%, rgba(35,25,15,0) 0%, rgba(35,25,15,0) 45%, rgba(35,25,15,.42) 100%)';
    $('iname').textContent = d.name; $('iprice').textContent = d.price; $('iid').value = d.id; $('istory').textContent = d.story || '';
    var ul = $('inotes'); ul.innerHTML = '';
    (d.notes || []).forEach(function (n) {
      var li = document.createElement('li'), b = document.createElement('b');
      b.textContent = n[0] + ': '; li.appendChild(b); li.appendChild(document.createTextNode(n[1])); ul.appendChild(li);
    });
    $('info').scrollTop = 0;
    showDetail(d);
  }
  // Ảnh chi tiết của sản phẩm (đổi ảnh bằng các chấm bên phải nếu có nhiều ảnh)
  var detail = $('detail'), dimg = detail.querySelector('.dimg'), dbg = detail.querySelector('.dbg'), dots = $('dots'), cur = null;
  function paint(i) {
    var src = cur.photos[i], im = new Image();
    dimg.classList.add('swap');
    im.onload = function () { dimg.src = src; dbg.style.backgroundImage = 'url("' + src + '")'; dimg.classList.remove('swap'); };
    im.src = src;
    [].forEach.call(dots.children, function (b, k) { b.classList.toggle('on', k === i); });
  }
  function showDetail(d) {
    cur = d; var has = d.photos && d.photos.length;
    detail.classList.toggle('has', !!has); $('cap').textContent = d.caption || ''; dots.innerHTML = '';
    if (!has) return;
    if (d.photos.length > 1) d.photos.forEach(function (_, k) {
      var b = document.createElement('button'); b.type = 'button'; b.setAttribute('aria-label', 'Ảnh ' + (k + 1)); b.onclick = function () { paint(k); }; dots.appendChild(b);
    });
    paint(0);
  }
  D.forEach(function (d, i) {
    if (i) nums.appendChild(document.createElement('i'));
    var b = document.createElement('button'); b.type = 'button'; b.dataset.id = d.id; b.textContent = ('0' + (i + 1)).slice(-2);
    b.onclick = function () { go(d.id); }; nums.appendChild(b);
  });
  spots.forEach(function (s) {
    s.addEventListener('click', function (e) { e.stopPropagation(); go(+s.dataset.id); });
    s.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); go(+s.dataset.id); } });
  });
  photo.addEventListener('click', function (e) { if (!e.target.closest('.spot') && !e.target.closest('#nums') && !e.target.closest('#detail') && !e.target.closest('.backlink')) go(null); });
  $('back').onclick = function () { go(null); };
  $('back2').onclick = function () { go(null); };
  window.addEventListener('keydown', function (e) { if (e.key === 'Escape') go(null); });

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce) return;

  // Parallax nhẹ theo chuột
  photo.addEventListener('pointermove', function (e) {
    if (e.pointerType === 'touch') return;
    var nx = e.clientX / innerWidth - 0.5, ny = e.clientY / innerHeight - 0.5;
    cam.style.setProperty('--px', (-nx * 16).toFixed(1) + 'px'); cam.style.setProperty('--py', (-ny * 9).toFixed(1) + 'px');
  });
  photo.addEventListener('pointerleave', function () { cam.style.setProperty('--px', '0px'); cam.style.setProperty('--py', '0px'); });

  // Bụi sáng lơ lửng trong không khí (chấm mờ nhỏ, xoá sạch mỗi khung hình)
  var cv = $('dust'), g = cv.getContext('2d'), P = [], N = 34;
  function size() { cv.width = cv.clientWidth; cv.height = cv.clientHeight; }
  function mk(init) {
    return { x: Math.random() * cv.width, y: init ? Math.random() * cv.height : cv.height + 10, r: Math.random() * 3 + 2,
             vy: -(Math.random() * 0.25 + 0.08), vx: (Math.random() - 0.5) * 0.15, a: Math.random() * 0.45 + 0.25, p: Math.random() * 6.28 };
  }
  new ResizeObserver(size).observe(photo); size();
  for (var i = 0; i < N; i++) P.push(mk(true));
  (function loop(t) {
    requestAnimationFrame(loop);
    if (!cv.width || !cv.height) return;
    g.clearRect(0, 0, cv.width, cv.height);
    P.forEach(function (p, i) {
      p.y += p.vy; p.x += p.vx + Math.sin(t / 1800 + p.p) * 0.12;
      if (p.y < -10) { P[i] = mk(false); return; }
      var al = p.a * (0.55 + 0.45 * Math.sin(t / 900 + p.p)), R = p.r * 2.4;
      var gr = g.createRadialGradient(p.x, p.y, 0, p.x, p.y, R);
      gr.addColorStop(0, 'rgba(255,248,230,' + al.toFixed(3) + ')'); gr.addColorStop(1, 'rgba(255,248,230,0)');
      g.fillStyle = gr; g.fillRect(p.x - R, p.y - R, R * 2, R * 2);
    });
  })(0);
})();
</script>
</body></html>
