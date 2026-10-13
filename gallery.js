(function () {
  // ===== CHỖ BẠN TỰ CHỈNH: không khí của từng mùi hương (theo id sản phẩm) =====
  // bg: màu nền/sương · light: màu ánh sáng · liq: màu nước hoa · pt: màu hạt
  // mode: drift (bay lơ lửng) | rise (bốc lên như hơi nước) | fall (rơi như giọt sương)
  var AMB = {
    7: { bg: '#cfe3ec', light: '#e2f3fb', liq: '#9ec9dc', pt: '#ffffff', mode: 'drift' },
    2: { bg: '#3b2a4d', light: '#ffb766', liq: '#6b3f8f', pt: '#ffe3b8', mode: 'rise' },
    4: { bg: '#b9cdbd', light: '#e9f5ea', liq: '#8fb79a', pt: '#e3f1ff', mode: 'fall' },
    5: { bg: '#c5cab6', light: '#f3f1e6', liq: '#9fbba5', pt: '#eaf5da', mode: 'fall' }
  };
  var DEF = { bg: '#e8dfd0', light: '#fff1da' };
  var PHOTO_HEIGHT = 3.0; // chiều cao ảnh chai trong cảnh 3D
  var SPACING = 5.2;   // khoảng cách giữa các bục
  // ===========================================================================

  var DATA = (window.GALLERY || []);
  var stage = document.getElementById('stage');
  if (!window.THREE || !DATA.length) { document.body.classList.add('nowebgl'); return; }
  var renderer;
  try { renderer = new THREE.WebGLRenderer({ antialias: true }); } catch (e) { document.body.classList.add('nowebgl'); return; }
  renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
  renderer.shadowMap.enabled = true; renderer.shadowMap.type = THREE.PCFSoftShadowMap;
  stage.appendChild(renderer.domElement);

  var C = function (h) { return new THREE.Color(h); };
  var bgCol = C(DEF.bg), lightTarget = C(DEF.light), bgTarget = C(DEF.bg);
  var scene = new THREE.Scene(); scene.background = bgCol; scene.fog = new THREE.Fog(DEF.bg, 14, 38);
  var camera = new THREE.PerspectiveCamera(35, 1, 0.1, 80);

  scene.add(new THREE.AmbientLight(0xffffff, 0.55));
  var sun = new THREE.DirectionalLight(0xfff1da, 0.95); sun.position.set(-7, 11, 6); sun.castShadow = true;
  sun.shadow.mapSize.set(1024, 1024);
  var sc = sun.shadow.camera; sc.left = -14; sc.right = 14; sc.top = 10; sc.bottom = -6; sc.near = 1; sc.far = 40;
  scene.add(sun);

  // Sàn cát + tia sáng xiên từ giếng trời (giả lập bằng hình nón mờ)
  var floor = new THREE.Mesh(new THREE.PlaneGeometry(80, 80), new THREE.MeshStandardMaterial({ color: 0xd9c9ad, roughness: 1 }));
  floor.rotation.x = -Math.PI / 2; floor.receiveShadow = true; scene.add(floor);
  var rays = new THREE.Mesh(new THREE.CylinderGeometry(0.3, 3.4, 16, 24, 1, true),
    new THREE.MeshBasicMaterial({ color: 0xfff3d6, transparent: true, opacity: 0.09, blending: THREE.AdditiveBlending, side: THREE.DoubleSide, depthWrite: false }));
  rays.position.set(-4, 8, -1); rays.rotation.z = -0.42; scene.add(rays);

  function rock(seed) {
    var g = new THREE.DodecahedronGeometry(1.15, 1), p = g.attributes.position;
    for (var i = 0; i < p.count; i++) {
      var x = p.getX(i), y = p.getY(i), z = p.getZ(i), f = 1 + 0.1 * Math.sin(x * 4.1 + y * 3.3 + z * 2.7 + seed);
      p.setXYZ(i, x * f, y * f, z * f);
    }
    g.computeVertexNormals();
    var m = new THREE.Mesh(g, new THREE.MeshStandardMaterial({ color: 0xb8aa94, roughness: 1, flatShading: true }));
    m.scale.set(1.15, 0.8, 1.15); m.position.y = 0.55; m.castShadow = m.receiveShadow = true; return m;
  }
  function bottle(name, liq) {
    var gr = new THREE.Group();
    function add(geo, mat, y) { var m = new THREE.Mesh(geo, mat); m.position.y = y; m.castShadow = true; gr.add(m); return m; }
    add(new THREE.CylinderGeometry(1, 1, 2.4, 48), new THREE.MeshStandardMaterial({ color: 0xffffff, transparent: true, opacity: 0.3, roughness: 0.05, metalness: 0.2 }), 0);
    add(new THREE.CylinderGeometry(0.92, 0.92, 1.8, 48), new THREE.MeshStandardMaterial({ color: C(liq), roughness: 0.2, transparent: true, opacity: 0.92 }), -0.2);
    var dark = new THREE.MeshStandardMaterial({ color: 0x3b2f25, roughness: 0.6 });   // nắp gỗ
    add(new THREE.CylinderGeometry(1.03, 1.03, 0.12, 48), dark, -1.26);
    add(new THREE.CylinderGeometry(0.32, 0.32, 0.4, 32), new THREE.MeshStandardMaterial({ color: 0xcfd6df, metalness: 0.7, roughness: 0.3 }), 1.4);
    add(new THREE.CylinderGeometry(0.5, 0.5, 0.9, 32), dark, 2.05);
    var cv = document.createElement('canvas'); cv.width = 600; cv.height = 400; var g = cv.getContext('2d');
    g.fillStyle = '#fbfbf9'; g.fillRect(0, 0, 600, 400); g.fillStyle = '#14213d'; g.textAlign = 'center';
    g.font = '500 76px Georgia, serif'; g.fillText('TOOUCH', 300, 190);
    g.font = '500 30px Arial, sans-serif'; g.fillText(name.toUpperCase(), 300, 260);
    var lb = new THREE.Mesh(new THREE.CylinderGeometry(1.01, 1.01, 1.0, 32, 1, true, -0.75, 1.5),
      new THREE.MeshStandardMaterial({ map: new THREE.CanvasTexture(cv), roughness: 0.6 }));
    lb.position.y = -0.1; gr.add(lb);
    gr.scale.setScalar(0.72); return gr;
  }

  var stops = [], hit = [];
  // Ảnh thật: đặt file images/3d-<id>.png (hoặc .jpg). Không có file thì dùng chai vẽ.
  //  - Ảnh nền trong suốt  -> chai đứng trên bục
  //  - Ảnh có phông nền    -> bức ảnh đóng khung dựa vào bục
  var loader = new THREE.TextureLoader(), photos = [];
  function hasAlpha(img) {
    var c = document.createElement('canvas'); c.width = c.height = 8;
    var g = c.getContext('2d'); g.drawImage(img, 0, 0, 8, 8);
    var d = g.getImageData(0, 0, 8, 8).data; return d[3] < 250 || d[8 * 8 * 4 - 1] < 250;
  }
  function usePhoto(id, x, fallback, idx, exts) {
    exts = exts || ['png', 'jpg'];
    if (!exts.length) return;
    loader.load('images/3d-' + id + '.' + exts[0], function (tex) {
      var ar = tex.image.width / tex.image.height, H = PHOTO_HEIGHT, W = H * ar, obj, k = 1;
      if (hasAlpha(tex.image)) {
        obj = new THREE.Mesh(new THREE.PlaneGeometry(W, H), new THREE.MeshBasicMaterial({ map: tex, transparent: true, alphaTest: 0.02 }));
        obj.position.set(x, 1.45 + H / 2, 0); obj.castShadow = true;
        obj.customDepthMaterial = new THREE.MeshDepthMaterial({ depthPacking: THREE.RGBADepthPacking, map: tex, alphaTest: 0.5 });
      } else {
        if (W > 2.6) { W = 2.6; H = W / ar; }
        obj = new THREE.Group(); k = 0.5;
        var fr = new THREE.Mesh(new THREE.BoxGeometry(W + 0.18, H + 0.18, 0.14), new THREE.MeshStandardMaterial({ color: 0x2b2118, roughness: 0.6 }));
        fr.castShadow = true; obj.add(fr);
        var pic = new THREE.Mesh(new THREE.PlaneGeometry(W, H), new THREE.MeshStandardMaterial({ map: tex, roughness: 0.9, emissive: 0xffffff, emissiveMap: tex, emissiveIntensity: 0.45 }));
        pic.position.z = 0.075; obj.add(pic);
        obj.position.set(x, 1.4 + (H + 0.18) / 2, -0.15); obj.rotation.x = -0.05;
      }
      obj.traverse(function (m) { if (m.isMesh) m.userData.idx = idx; });
      scene.add(obj); photos.push({ o: obj, k: k });
      scene.remove(fallback); var i = hit.indexOf(fallback); if (i >= 0) hit.splice(i, 1); hit.push(obj);
    }, undefined, function () { usePhoto(id, x, fallback, idx, exts.slice(1)); });
  }

  DATA.forEach(function (d, i) {
    var a = AMB[d.id] || { bg: DEF.bg, light: DEF.light, liq: '#24406b', pt: '#ffffff', mode: 'drift' };
    var x = (i - (DATA.length - 1) / 2) * SPACING;
    var r = rock(i * 1.7); r.position.x = x; scene.add(r);
    var b = bottle(d.name, a.liq); b.position.set(x, 2.33, 0); scene.add(b);
    usePhoto(d.id, x, b, i);
    [r, b].forEach(function (o) { o.traverse(function (m) { if (m.isMesh) m.userData.idx = i; }); });
    hit.push(r, b);
    var n = 160, arr = new Float32Array(n * 3);
    for (var k = 0; k < n; k++) { arr[k * 3] = x + (Math.random() - 0.5) * 5; arr[k * 3 + 1] = Math.random() * 5; arr[k * 3 + 2] = (Math.random() - 0.5) * 4; }
    var geo = new THREE.BufferGeometry(); geo.setAttribute('position', new THREE.BufferAttribute(arr, 3));
    var pm = new THREE.PointsMaterial({ color: C(a.pt), size: 0.07, transparent: true, opacity: 0, depthWrite: false });
    var pts = new THREE.Points(geo, pm); scene.add(pts);
    stops.push({ x: x, a: a, pts: pts, pm: pm, arr: arr, d: d });
  });

  // ===== Camera: toàn cảnh hoặc bay lại gần một bục =====
  var focus = -1, camPos = new THREE.Vector3(), camLook = new THREE.Vector3(), curLook = new THREE.Vector3(0, 2.2, 0);
  var mx = 0, my = 0;
  function aim() {
    var asp = camera.aspect;
    if (focus < 0) { camPos.set(0, 3.4, 13.5 * Math.max(1, 1.5 / asp)); camLook.set(0, 2.1, 0); }
    else { var s = stops[focus]; camPos.set(s.x + 0.4, 2.9, 6.6 * Math.max(1, 0.85 / asp)); camLook.set(s.x, 2.65, 0); }
  }
  camera.position.set(0, 4, 20);

  var $ = function (id) { return document.getElementById(id); };
  var nums = $('nums');
  stops.forEach(function (s, i) {
    if (i) { nums.appendChild(document.createElement('i')); }
    var b = document.createElement('button'); b.type = 'button'; b.textContent = ('0' + (i + 1)).slice(-2);
    b.onclick = function () { go(i); }; nums.appendChild(b);
  });
  function go(i) {
    focus = i; aim();
    [].forEach.call(nums.querySelectorAll('button'), function (b, k) { b.classList.toggle('on', k === i); });
    var info = $('info');
    if (i < 0) { info.hidden = true; $('hint').style.opacity = 1; }
    else { $('iname').textContent = stops[i].d.name; $('iprice').textContent = stops[i].d.price; $('iid').value = stops[i].d.id; info.hidden = false; var d = stops[i].d; $('istory').textContent = d.story || '';
      var ul = $('inotes'); ul.innerHTML = '';
      (d.notes || []).forEach(function (n) { var li = document.createElement('li'), b = document.createElement('b'); b.textContent = n[0] + ': '; li.appendChild(b); li.appendChild(document.createTextNode(n[1])); ul.appendChild(li); }); $('hint').style.opacity = 0; }
    var a = i < 0 ? DEF : stops[i].a;
    bgTarget.set(a.bg); lightTarget.set(a.light);
  }
  $('back').onclick = function () { go(-1); };
  window.addEventListener('keydown', function (e) { if (e.key === 'Escape') go(-1); });

  // ===== Con trỏ, hover, click =====
  var ring = $('ring'), ray = new THREE.Raycaster(), mv = new THREE.Vector2(), down = null;
  function pick(e) {
    mv.set(e.clientX / innerWidth * 2 - 1, -(e.clientY / innerHeight) * 2 + 1);
    ray.setFromCamera(mv, camera);
    var h = ray.intersectObjects(hit, true)[0]; return h ? h.object.userData.idx : -1;
  }
  stage.addEventListener('pointermove', function (e) {
    ring.style.opacity = 1; ring.style.transform = 'translate(' + e.clientX + 'px,' + e.clientY + 'px)';
    ring.classList.toggle('hot', pick(e) >= 0);
    mx = e.clientX / innerWidth - 0.5; my = e.clientY / innerHeight - 0.5;
  });
  stage.addEventListener('pointerdown', function (e) { down = [e.clientX, e.clientY]; });
  stage.addEventListener('pointerup', function (e) {
    if (down && Math.abs(e.clientX - down[0]) + Math.abs(e.clientY - down[1]) < 8) { var i = pick(e); if (i >= 0) go(i); }
    down = null;
  });

  function resize() { renderer.setSize(innerWidth, innerHeight); camera.aspect = innerWidth / innerHeight; camera.updateProjectionMatrix(); aim(); }
  window.addEventListener('resize', resize); resize();
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  (function loop(t) {
    requestAnimationFrame(loop);
    var k = reduce ? 0.2 : 0.045;
    camera.position.lerp(new THREE.Vector3(camPos.x + mx * 0.8, camPos.y - my * 0.4, camPos.z), k);
    curLook.lerp(camLook, k * 1.3); camera.lookAt(curLook);
    bgCol.lerp(bgTarget, 0.04); scene.fog.color.copy(bgCol); sun.color.lerp(lightTarget, 0.04);
    stops.forEach(function (s, i) {
      s.pm.opacity += ((focus === i ? 0.85 : (focus < 0 ? 0.3 : 0)) - s.pm.opacity) * 0.05;
      if (reduce) return;
      for (var j = 0; j < s.arr.length; j += 3) {
        if (s.a.mode === 'rise') { s.arr[j + 1] += 0.012; s.arr[j] += Math.sin(t / 700 + j) * 0.003; if (s.arr[j + 1] > 5) s.arr[j + 1] = 0; }
        else if (s.a.mode === 'fall') { s.arr[j + 1] -= 0.01; if (s.arr[j + 1] < 0) s.arr[j + 1] = 5; }
        else { s.arr[j] += Math.sin(t / 900 + j) * 0.004; s.arr[j + 1] += Math.cos(t / 1100 + j) * 0.003; }
      }
      s.pts.geometry.attributes.position.needsUpdate = true;
    });
    photos.forEach(function (p) { p.o.rotation.y = Math.atan2(camera.position.x - p.o.position.x, camera.position.z - p.o.position.z) * p.k; });
    renderer.render(scene, camera);
  })(0);
  go(-1);
})();
