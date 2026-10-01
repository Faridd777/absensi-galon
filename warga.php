<?php
require 'config.php'; need('warga');
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $s = $pdo->prepare("SELECT * FROM siswa WHERE nisn=?"); $s->execute([$_POST['nisn'] ?? '']);
  $sw = $s->fetch();
  if (!$sw) $err = 'Data siswa tidak ditemukan.';
  elseif (stok($pdo) < 1) $err = 'Maaf, stok galon habis.';
  else {
    $c = $pdo->prepare("SELECT id FROM absensi WHERE nisn=? AND tanggal=CURDATE()"); $c->execute([$sw['nisn']]);
    if ($x = $c->fetch()) { header('Location: warga.php?struk='.$x['id'].'&dobel=1'); exit; }
    $pdo->beginTransaction();
    $pdo->prepare("INSERT INTO absensi (nisn,nama,kelas,tanggal,jam) VALUES (?,?,?,CURDATE(),CURTIME())")->execute([$sw['nisn'],$sw['nama'],$sw['kelas']]);
    $id = $pdo->lastInsertId();
    $pdo->exec("UPDATE pengaturan SET stok=stok-1 WHERE id=1 AND stok>0");
    $pdo->commit();
    header('Location: warga.php?struk='.$id); exit;
  }
}
head('Absensi Galon');
echo stokInfo($pdo);
if ($err) echo '<div class="alert alert-warning">'.e($err).'</div>';

if (isset($_GET['struk'])):
  $s = $pdo->prepare("SELECT * FROM absensi WHERE id=?"); $s->execute([(int)$_GET['struk']]); $r = $s->fetch();
  if ($r): ?>
  <div class="row justify-content-center"><div class="col-md-5">
    <?php if (isset($_GET['dobel'])): ?><div class="alert alert-info no-print">Kamu sudah absen hari ini. Ini struk absenmu.</div><?php endif; ?>
    <div class="card shadow-sm border-dark"><div class="card-body text-center">
      <h5>STRUK ABSENSI PENGAMBILAN GALON</h5><div class="text-muted mb-3">SMKN 1 Probolinggo</div>
      <table class="table table-sm text-start">
        <tr><th>No. Struk</th><td>#<?= str_pad($r['id'],5,'0',STR_PAD_LEFT) ?></td></tr>
        <tr><th>NISN</th><td><?= e($r['nisn']) ?></td></tr>
        <tr><th>Nama</th><td><?= e($r['nama']) ?></td></tr>
        <tr><th>Kelas</th><td><?= e($r['kelas']) ?></td></tr>
        <tr><th>Tanggal</th><td><?= tgl($r['tanggal']) ?></td></tr>
        <tr><th>Jam</th><td><?= e($r['jam']) ?></td></tr>
      </table>
      <p class="small">Tunjukkan struk ini ke penjaga BC.</p>
      <button class="btn btn-outline-primary no-print" onclick="print()">Cetak / Simpan</button>
      <a class="btn btn-primary no-print" href="warga.php">Selesai</a>
    </div></div>
  </div></div>
<?php endif; foot(); exit; endif;
$siswa = $pdo->query("SELECT nisn,nama,kelas FROM siswa ORDER BY nama")->fetchAll(); ?>
<div class="row justify-content-center"><div class="col-md-6">
  <div class="card shadow-sm"><div class="card-body">
    <h5>Absensi Pengambilan Galon</h5>
    <label class="form-label">Ketik nama siswa</label>
    <input id="cari" class="form-control" placeholder="Contoh: Ahmad" autocomplete="off">
    <div id="hasil" class="list-group mt-1"></div>
    <form method="post" id="detail" class="d-none mt-3">
      <input type="hidden" name="nisn" id="f_nisn">
      <table class="table table-bordered mb-3">
        <tr><th width="30%">NISN</th><td id="d_nisn"></td></tr>
        <tr><th>Nama</th><td id="d_nama"></td></tr>
        <tr><th>Kelas</th><td id="d_kelas"></td></tr>
        <tr><th>Tanggal</th><td><?= date('d-m-Y') ?></td></tr>
      </table>
      <button class="btn btn-success w-100" <?= stok($pdo) < 1 ? 'disabled' : '' ?>>Absen &amp; Buat Struk</button>
    </form>
  </div></div>
</div></div>
<script>
const S = <?= json_encode($siswa) ?>;
const cari = document.getElementById('cari'), hasil = document.getElementById('hasil'), detail = document.getElementById('detail');
cari.addEventListener('input', () => {
  const q = cari.value.trim().toLowerCase(); hasil.innerHTML = ''; detail.classList.add('d-none');
  if (!q) return;
  S.filter(s => s.nama.toLowerCase().includes(q)).slice(0, 8).forEach(s => {
    const b = document.createElement('button'); b.type = 'button'; b.className = 'list-group-item list-group-item-action';
    b.textContent = s.nama + ' — ' + s.kelas;
    b.onclick = () => {
      f_nisn.value = s.nisn; d_nisn.textContent = s.nisn; d_nama.textContent = s.nama; d_kelas.textContent = s.kelas;
      detail.classList.remove('d-none'); hasil.innerHTML = ''; cari.value = s.nama;
    };
    hasil.appendChild(b);
  });
});
</script>
<?php foot();
