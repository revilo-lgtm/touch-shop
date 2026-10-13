(function () {
  // ===== CHỖ BẠN CÓ THỂ TỰ CHỈNH =====
  var AUTO_SPEED = 0.006;          // tốc độ tự xoay (0 = đứng yên)
  var START_COLOR = '#24406b';     // màu mùi hương ban đầu
  var BRAND_TEXT = 'TOOUCH';       // chữ trên nhãn chai
  var SUB_TEXT = 'EAU DE PARFUM';  // dòng nhỏ dưới tên
  // ====================================

  var box = document.getElementById('hero3d');
  if (!box || !window.THREE) return;           // lỗi thì giữ chai SVG dự phòng
  var renderer;
  try { renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true }); } catch (e) { return; }
  renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
  box.appendChild(renderer.domElement);
  box.classList.add('ready');

  var scene = new THREE.Scene();
  var camera = new THREE.PerspectiveCamera(30, 1, 0.1, 100);
  camera.position.set(0, 0.5, 10);
  scene.add(new THREE.AmbientLight(0xffffff, 0.75));
  var key = new THREE.DirectionalLight(0xffffff, 0.9); key.position.set(4, 6, 5); scene.add(key);
  var rim = new THREE.PointLight(0x9bb4ff, 0.9); rim.position.set(-5, 2, -3); scene.add(rim);

  var bottle = new THREE.Group(); scene.add(bottle);
  function add(geo, mat, y) { var m = new THREE.Mesh(geo, mat); m.position.y = y; bottle.add(m); return m; }

  // Thân thủy tinh + dịch nước hoa
  add(new THREE.CylinderGeometry(1, 1, 2.4, 48),
      new THREE.MeshStandardMaterial({ color: 0xffffff, transparent: true, opacity: 0.28, roughness: 0.05, metalness: 0.2 }), 0);
  var liquidMat = new THREE.MeshStandardMaterial({ color: START_COLOR, roughness: 0.2, metalness: 0.1, transparent: true, opacity: 0.92 });
  add(new THREE.CylinderGeometry(0.92, 0.92, 1.8, 48), liquidMat, -0.2);
  // Đáy, cổ, nắp
  var dark = new THREE.MeshStandardMaterial({ color: 0x14213d, roughness: 0.35, metalness: 0.4 });
  add(new THREE.CylinderGeometry(1.03, 1.03, 0.12, 48), dark, -1.26);
  add(new THREE.CylinderGeometry(0.32, 0.32, 0.4, 32), new THREE.MeshStandardMaterial({ color: 0xcfd6df, metalness: 0.7, roughness: 0.3 }), 1.4);
  add(new THREE.CylinderGeometry(0.5, 0.5, 0.9, 32), dark, 2.05);

  // Nhãn: vẽ bằng canvas rồi dán lên mặt trước chai
  var c = document.createElement('canvas'); c.width = 600; c.height = 400;
  var g = c.getContext('2d');
  g.fillStyle = '#fbfbf9'; g.fillRect(0, 0, 600, 400);
  g.fillStyle = '#14213d'; g.textAlign = 'center';
  g.font = '500 78px Georgia, serif'; g.fillText(BRAND_TEXT, 300, 210);
  g.font = '500 28px Arial, sans-serif'; g.fillText(SUB_TEXT, 300, 270);
  var label = new THREE.Mesh(
    new THREE.CylinderGeometry(1.01, 1.01, 1.0, 32, 1, true, -0.75, 1.5),
    new THREE.MeshStandardMaterial({ map: new THREE.CanvasTexture(c), roughness: 0.6 }));
  label.position.y = -0.1; bottle.add(label);

  bottle.position.y = -0.6;

  // Kéo để xoay + nghiêng nhẹ theo chuột
  var dragging = false, lastX = 0, tiltTarget = 0;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  box.addEventListener('pointerdown', function (e) { dragging = true; lastX = e.clientX; box.setPointerCapture(e.pointerId); box.classList.add('grab'); });
  box.addEventListener('pointerup', function () { dragging = false; box.classList.remove('grab'); });
  box.addEventListener('pointermove', function (e) {
    if (dragging) { bottle.rotation.y += (e.clientX - lastX) * 0.012; lastX = e.clientX; }
    var r = box.getBoundingClientRect();
    tiltTarget = ((e.clientY - r.top) / r.height - 0.5) * 0.35;
  });

  // Chọn màu mùi hương
  document.querySelectorAll('.swatches .dot').forEach(function (b) {
    b.addEventListener('click', function () { liquidMat.color.set(b.dataset.c); });
  });

  function resize() {
    var w = box.clientWidth, h = box.clientHeight;
    if (!w || !h) return;
    renderer.setSize(w, h); camera.aspect = w / h; camera.updateProjectionMatrix();
  }
  new ResizeObserver(resize).observe(box); resize();

  var visible = true;
  new IntersectionObserver(function (en) { visible = en[0].isIntersecting; }).observe(box);

  (function loop() {
    requestAnimationFrame(loop);
    if (!visible) return;
    if (!dragging && !reduce) bottle.rotation.y += AUTO_SPEED;
    bottle.rotation.x += (tiltTarget - bottle.rotation.x) * 0.08;
    renderer.render(scene, camera);
  })();
})();
