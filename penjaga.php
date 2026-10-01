<?php
require 'config.php'; need('penjaga');
$p = $_GET['p'] ?? 'menu';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $a = $_POST['aksi'] ?? '';
  if ($a === 'stok') {
    $pdo->prepare("UPDATE pengaturan SET stok=? WHERE id=1")->execute([max(0, (int)$_POST['stok'])]);
    header('Location: penjaga.php'); exit;
  }
  if ($a === 'hapus') {
    $s = $pdo->prepare("DELETE FROM absensi WHERE id=?"); $s->execute([(int)$_POST['id']]);
    if ($s->rowCount()) $pdo->exec("UPDATE pengaturan SET stok=stok+1 WHERE id=1");
    header('Location: penjaga.php?p=hapus&tgl='.urlencode($_POST['tgl'] ?? '')); exit;
  }
  if ($a === 'edit') {
    $pdo->prepare("UPDATE absensi SET nisn=?,nama=?,kelas=?,tanggal=?,jam=? WHERE id=?")
        ->execute([trim($_POST['nisn']), trim($_POST['nama']), trim($_POST['kelas']), $_POST['tanggal'], $_POST['jam'], (int)$_POST['id']]);
    header('Location: penjaga.php?p=edit&ok=1'); exit;
  }
}

function filterTgl($pdo, &$tgl){
  $tgl = $_GET['tgl'] ?? date('Y-m-d');
  if ($tgl === 'semua' || $tgl === '') { $tgl = 'semua'; return $pdo->query("SELECT * FROM absensi ORDER BY tanggal DESC, jam DESC")->fetchAll(); }
  $s = $pdo->prepare("SELECT * FROM absensi WHERE tanggal=? ORDER BY jam DESC"); $s->execute([$tgl]); return $s->fetchAll();
}
function filterForm($p, $tgl){ ?>
  <form class="row g-2 mb-3 no-print" method="get"><input type="hidden" name="p" value="<?= e($p) ?>">
    <div class="col-auto"><input type="date" name="tgl" class="form-control" value="<?= $tgl==='semua' ? '' : e($tgl) ?>"></div>
    <div class="col-auto"><button class="btn btn-primary">Tampilkan</button>
    <a class="btn btn-outline-secondary" href="?p=<?= e($p) ?>&tgl=semua">Semua data</a></div></form>
<?php }
function kembali(){ echo '<a class="btn btn-secondary mt-3 no-print" href="penjaga.php">← Kembali</a>'; }

head('Penjaga BC');

if ($p === 'menu'): echo stokInfo($pdo); ?>
  <div class="row g-3 mb-4">
    <?php foreach ([['data','📋 Lihat Data','Tampilan semua data pengambilan'],['hapus','🗑️ Hapus Data','Hapus data yang tidak diperlukan'],['edit','✏️ Edit Data','Perbaiki data yang salah'],['rekap','📊 Rekap Data','Rekap harian atau bulanan']] as $m): ?>
      <div class="col-md-3 col-6"><a href="?p=<?= $m[0] ?>" class="card h-100 text-decoration-none shadow-sm"><div class="card-body text-center"><h5><?= $m[1] ?></h5><small class="text-muted"><?= $m[2] ?></small></div></a></div>
    <?php endforeach; ?>
  </div>
  <div class="card shadow-sm"><div class="card-body">
    <form method="post" class="row g-2 align-items-end"><input type="hidden" name="aksi" value="stok">
      <div class="col-auto"><label class="form-label">Update stok galon</label><input type="number" min="0" name="stok" class="form-control" value="<?= stok($pdo) ?>"></div>
      <div class="col-auto"><button class="btn btn-primary">Simpan stok</button></div>
    </form>
  </div></div>

<?php elseif ($p === 'data' || $p === 'hapus' || $p === 'edit' && !isset($_GET['id'])):
  $rows = filterTgl($pdo, $tgl);
  $judul = ['data'=>'Lihat Data','hapus'=>'Hapus Data','edit'=>'Edit Data'][$p]; ?>
  <h4><?= $judul ?> <small class="text-muted fs-6">(<?= count($rows) ?> data)</small></h4>
  <?php if (isset($_GET['ok'])): ?><div class="alert alert-success">Data berhasil disimpan.</div><?php endif; ?>
  <?php filterForm($p, $tgl); ?>
  <div class="table-responsive"><table class="table table-bordered table-striped bg-white">
    <thead class="table-dark"><tr><th>No</th><th>NISN</th><th>Nama</th><th>Kelas</th><th>Tanggal</th><th>Jam</th><?php if ($p!=='data'): ?><th>Aksi</th><?php endif; ?></tr></thead>
    <tbody>
    <?php foreach ($rows as $i => $r): ?>
      <tr><td><?= $i+1 ?></td><td><?= e($r['nisn']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['kelas']) ?></td><td><?= tgl($r['tanggal']) ?></td><td><?= e($r['jam']) ?></td>
      <?php if ($p === 'hapus'): ?><td><form method="post" onsubmit="return confirm('Hapus data ini?')"><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="tgl" value="<?= e($tgl) ?>"><button class="btn btn-sm btn-danger">Hapus</button></form></td>
      <?php elseif ($p === 'edit'): ?><td><a class="btn btn-sm btn-warning" href="?p=edit&id=<?= $r['id'] ?>">Edit</a></td><?php endif; ?></tr>
    <?php endforeach; if (!$rows) echo '<tr><td colspan="7" class="text-center text-muted">Belum ada data.</td></tr>'; ?>
    </tbody></table></div>
  <?php kembali(); ?>

<?php elseif ($p === 'edit'):
  $s = $pdo->prepare("SELECT * FROM absensi WHERE id=?"); $s->execute([(int)$_GET['id']]); $r = $s->fetch();
  if (!$r) { echo '<div class="alert alert-warning">Data tidak ditemukan.</div>'; } else { ?>
  <h4>Edit Data</h4>
  <form method="post" class="card card-body shadow-sm" style="max-width:500px">
    <input type="hidden" name="aksi" value="edit"><input type="hidden" name="id" value="<?= $r['id'] ?>">
    <label class="form-label">NISN</label><input name="nisn" class="form-control mb-2" value="<?= e($r['nisn']) ?>" required>
    <label class="form-label">Nama</label><input name="nama" class="form-control mb-2" value="<?= e($r['nama']) ?>" required>
    <label class="form-label">Kelas</label><input name="kelas" class="form-control mb-2" value="<?= e($r['kelas']) ?>" required>
    <label class="form-label">Tanggal</label><input type="date" name="tanggal" class="form-control mb-2" value="<?= e($r['tanggal']) ?>" required>
    <label class="form-label">Jam</label><input type="time" step="1" name="jam" class="form-control mb-3" value="<?= e($r['jam']) ?>" required>
    <button class="btn btn-success">Simpan perubahan</button>
  </form>
  <a class="btn btn-secondary mt-3" href="?p=edit">← Kembali</a>
  <?php } ?>

<?php elseif ($p === 'rekap'):
  $mode = $_GET['mode'] ?? 'hari'; $rows = null;
  $d1 = $_GET['dari'] ?? date('Y-m-d', strtotime('monday this week'));
  $d2 = $_GET['sampai'] ?? date('Y-m-d', strtotime('friday this week'));
  $b1 = (int)($_GET['b1'] ?? 1); $b2 = (int)($_GET['b2'] ?? 12); $th = (int)($_GET['th'] ?? date('Y'));
  if (isset($_GET['go'])) {
    if ($mode === 'bulan') { $d1 = sprintf('%04d-%02d-01', $th, $b1); $d2 = date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $th, $b2))); }
    $s = $pdo->prepare("SELECT * FROM absensi WHERE tanggal BETWEEN ? AND ? ORDER BY tanggal, jam"); $s->execute([$d1, $d2]); $rows = $s->fetchAll();
  }
  $nm = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; ?>
  <h4 class="no-print">Rekap Data</h4>
  <form method="get" class="card card-body mb-3 shadow-sm no-print"><input type="hidden" name="p" value="rekap"><input type="hidden" name="go" value="1">
    <div class="mb-2"><label class="me-3"><input type="radio" name="mode" value="hari" <?= $mode==='hari'?'checked':'' ?>> Hari ke hari</label>
    <label><input type="radio" name="mode" value="bulan" <?= $mode==='bulan'?'checked':'' ?>> Bulan ke bulan</label></div>
    <div class="row g-2">
      <div class="col-md-3"><label>Dari tanggal</label><input type="date" name="dari" class="form-control" value="<?= e($d1) ?>"></div>
      <div class="col-md-3"><label>Sampai tanggal</label><input type="date" name="sampai" class="form-control" value="<?= e($d2) ?>"></div>
      <div class="col-md-2"><label>Dari bulan</label><select name="b1" class="form-select"><?php for($i=1;$i<=12;$i++) echo '<option value="'.$i.'"'.($i==$b1?' selected':'').'>'.$nm[$i].'</option>'; ?></select></div>
      <div class="col-md-2"><label>Sampai bulan</label><select name="b2" class="form-select"><?php for($i=1;$i<=12;$i++) echo '<option value="'.$i.'"'.($i==$b2?' selected':'').'>'.$nm[$i].'</option>'; ?></select></div>
      <div class="col-md-2"><label>Tahun</label><input type="number" name="th" class="form-control" value="<?= $th ?>"></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Tampilkan rekap</button></div>
  </form>
  <?php if ($rows !== null): ?>
    <h5>Rekap pengambilan galon: <?= tgl($d1) ?> s/d <?= tgl($d2) ?></h5>
    <p><b>Total: <?= count($rows) ?> pengambilan</b></p>
    <?php $per = []; foreach ($rows as $r) $per[$r['tanggal']] = ($per[$r['tanggal']] ?? 0) + 1; ?>
    <table class="table table-sm table-bordered bg-white w-auto"><thead class="table-secondary"><tr><th>Tanggal</th><th>Jumlah</th></tr></thead>
      <?php foreach ($per as $t => $n) echo '<tr><td>'.tgl($t).'</td><td>'.$n.'</td></tr>'; ?></table>
    <div class="table-responsive"><table class="table table-bordered table-striped bg-white">
      <thead class="table-dark"><tr><th>No</th><th>Tanggal</th><th>NISN</th><th>Nama</th><th>Kelas</th><th>Jam</th></tr></thead>
      <?php foreach ($rows as $i => $r) echo '<tr><td>'.($i+1).'</td><td>'.tgl($r['tanggal']).'</td><td>'.e($r['nisn']).'</td><td>'.e($r['nama']).'</td><td>'.e($r['kelas']).'</td><td>'.e($r['jam']).'</td></tr>'; ?>
    </table></div>
    <button class="btn btn-outline-primary no-print" onclick="print()">🖨️ Cetak rekap</button>
  <?php endif; kembali(); endif;
foot();
