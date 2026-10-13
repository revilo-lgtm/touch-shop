<?php require 'config.php';
header_html('Câu chuyện', 'story');
?>
<div class="page-head">
  <h1>Câu chuyện của Toouch</h1>
  <p>Nước hoa Việt, kể bằng cái chạm.</p>
</div>

<!-- TỰ VIẾT LẠI: các đoạn dưới đây là bản nháp, hãy viết lại bằng lời của bạn -->
<section class="story">
  <div class="tile"><?= slot_image('story-1', bottle(1, 'Chai 1')) ?></div>
  <div>
    <h2>Niềm tự hào nước hoa Việt</h2>
    <p>Toouch là thương hiệu nước hoa được sáng tạo và chế tác tại Việt Nam. Mỗi mùi hương mang một câu chuyện riêng, đủ tinh tế để đặt cạnh các chuẩn mực quốc tế mà vẫn giữ được nét Việt.</p>
  </div>
</section>

<section class="story flip">
  <div class="tile"><?= slot_image('story-2', bottle(3, 'Chai 3')) ?></div>
  <div>
    <h2>Toouch nghĩa là gì</h2>
    <p>Tên gọi bắt nguồn từ chữ "touch", nghĩa là cái chạm. Đó là cái chạm của mùi hương lên làn da và lên những ký ức, cảm xúc mà mỗi người thường giấu kín.</p>
  </div>
</section>

<section class="story">
  <div class="tile"><?= slot_image('story-3', bottle(6, 'Chai 6')) ?></div>
  <div>
    <h2>Hành trình của mùi hương</h2>
    <p>Mùi hương nối hôm nay với hôm qua. Chỉ một thoáng hương quen cũng đủ gọi về một khoảnh khắc đã ngủ yên, và đồng hành cùng bạn trên con đường tìm hiểu chính mình.</p>
  </div>
</section>

<section class="story flip">
  <div class="tile"><?= slot_image('story-4', '<svg class="bottle" viewBox="0 0 160 120" role="img" aria-label="Hình vuông và hình tròn"> <rect x="14" y="24" width="72" height="72" fill="none" stroke="#14213d" stroke-width="3"/> <circle cx="102" cy="60" r="38" fill="none" stroke="#14213d" stroke-width="3"/> </svg>') ?></div>
  <div>
    <h2>Triết lý thiết kế</h2>
    <p>Dáng chai kết hợp hai hình khối đối lập là vuông và tròn, gợi ý quan niệm "trời tròn đất vuông" trong văn hóa Việt về sự trọn vẹn. Phần nắp vặn được làm thủ công bởi các nghệ nhân, để mỗi lần mở chai là một lần cảm nhận sự tỉ mỉ.</p>
  </div>
</section>

<section class="story">
  <div class="tile"><?= slot_image('story-5', bottle(5, 'Chai 5')) ?></div>
  <div>
    <h2>Bao bì tinh tế và tử tế</h2>
    <p>Bao bì thiết kế tối giản, làm từ giấy thân thiện với môi trường. Hộp không chỉ bảo vệ mùi hương mà còn như một lời cảm ơn, một món quà để tặng chính mình hoặc người thương.</p>
  </div>
</section>

<p class="small-note">Nội dung trang này mô phỏng theo phần giới thiệu trên website chính thức của Toouch, chỉ dùng cho bài tập môn học.</p>
<p style="margin-top:24px"><a class="btn" href="products.php">Khám phá sản phẩm</a></p>
<?php footer_html();
