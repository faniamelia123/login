<?php
/* admin.php
   Single-file admin dengan:
   - Dashboard
   - Tabungan (all)
   - Tabungan Pribadi (terpisah)
   - Tabungan Kelas (terpisah)
   - Tabungan Lainnya (terpisah)
   - Laporan (tidak memasukkan data Pribadi/Kelas/Lainnya)
   - Export CSV, upload bukti, CRUD
*/

/* --------- Konfigurasi DB (ubah jika perlu) --------- */
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'tabunganku';

/* ---------- koneksi & buat database/tabel jika belum ada ---------- */
$mysqli = new mysqli($db_host, $db_user, $db_pass);
if ($mysqli->connect_errno) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}
if (!$mysqli->select_db($db_name)) {
    $mysqli->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $mysqli->select_db($db_name);
}
$create_table_sql = "
CREATE TABLE IF NOT EXISTS `tabungan` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `nama` VARCHAR(191) NOT NULL,
  `jenis` VARCHAR(100) NOT NULL,
  `jumlah` BIGINT NOT NULL DEFAULT 0,
  `tanggal` DATE NOT NULL,
  `bukti` VARCHAR(255) DEFAULT '',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$mysqli->query($create_table_sql);

/* folder upload */
$upload_dir = __DIR__ . '/uploads';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

/* helper */
function h($s){ return htmlspecialchars($s, ENT_QUOTES); }
function today(){ return date('Y-m-d'); }

/* ---------- handle actions ---------- */
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add' || $action === 'edit') {
        $nama = $mysqli->real_escape_string(trim($_POST['nama'] ?? ''));
        $jenis = $mysqli->real_escape_string(trim($_POST['jenis'] ?? 'Tabungan Pribadi'));
        $jumlah = intval(str_replace(',', '', $_POST['jumlah'] ?? 0));
        $tanggal = $_POST['tanggal'] ?? today();

        // file upload
        $bukti_name = '';
        if (!empty($_FILES['bukti']['name'])) {
            $tmp = $_FILES['bukti']['tmp_name'];
            $orig = basename($_FILES['bukti']['name']);
            $bukti_name = time() . '_' . preg_replace('/[^a-z0-9\._-]/i', '_', $orig);
            $target = $upload_dir . '/' . $bukti_name;
            if (!move_uploaded_file($tmp, $target)) $bukti_name = '';
        } elseif ($action === 'edit' && !empty($_POST['existing_bukti'])) {
            $bukti_name = $_POST['existing_bukti'];
        }

        if ($action === 'add') {
            $stmt = $mysqli->prepare("INSERT INTO tabungan (nama, jenis, jumlah, tanggal, bukti) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('ssiss', $nama, $jenis, $jumlah, $tanggal, $bukti_name);
            $stmt->execute(); $stmt->close();
            $alert = "Data berhasil ditambahkan.";
        } else {
            $id = intval($_POST['id'] ?? 0);
            $stmt = $mysqli->prepare("UPDATE tabungan SET nama=?, jenis=?, jumlah=?, tanggal=?, bukti=? WHERE id = ?");
            $stmt->bind_param('ssissi', $nama, $jenis, $jumlah, $tanggal, $bukti_name, $id);
            $stmt->execute(); $stmt->close();
            $alert = "Data berhasil diperbarui.";
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        $res = $mysqli->query("SELECT bukti FROM tabungan WHERE id = $id");
        if ($res && $row = $res->fetch_assoc()) {
            if (!empty($row['bukti'])) @unlink($upload_dir . '/' . $row['bukti']);
        }
        $mysqli->query("DELETE FROM tabungan WHERE id = $id");
        $alert = "Data berhasil dihapus.";
    } elseif ($action === 'export_csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=tabungan_export_' . date('Ymd_His') . '.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID','Nama','Jenis','Jumlah','Tanggal','Bukti','Created At']);
        $q = $mysqli->query("SELECT * FROM tabungan ORDER BY tanggal DESC, id DESC");
        while ($r = $q->fetch_assoc()) {
            fputcsv($out, [$r['id'],$r['nama'],$r['jenis'],$r['jumlah'],$r['tanggal'],$r['bukti'],$r['created_at']]);
        }
        fclose($out); exit;
    }
}

/* ---------- fetch helpers ---------- */
function fetch_all($mysqli, $where = ''){
    $arr = []; $sql = "SELECT * FROM tabungan ";
    if($where) $sql .= " WHERE $where ";
    $sql .= " ORDER BY tanggal DESC, id DESC";
    $q = $mysqli->query($sql);
    while ($r = $q->fetch_assoc()) $arr[] = $r;
    return $arr;
}

/* totals for dashboard (sum all entries except those types excluded by laporan) */
$all_rows = fetch_all($mysqli);
$sum_total = 0; foreach($all_rows as $r) $sum_total += intval($r['jumlah']);
/* totals masuk/keluar naive */
$qMasuk = $mysqli->query("SELECT SUM(jumlah) as s FROM tabungan WHERE jenis NOT IN ('Tabungan Pribadi','Tabungan Kelas','Tabungan Lainnya')"); $totalMasuk = ($qMasuk && $qMasuk->fetch_assoc())? intval($qMasuk->fetch_assoc()['s']) : 0;
/* but we will just compute display totals from all rows below */

/* ---------- page router ---------- */
$page = $_GET['p'] ?? 'dashboard';

/* ---------- helper to render table rows ---------- */
function render_table_rows($rows, $show_actions = true) {
    foreach($rows as $i => $r) {
        echo "<tr>";
        echo "<td>".($i+1)."</td>";
        echo "<td>".h($r['nama'])."</td>";
        echo "<td>".h($r['jenis'])."</td>";
        echo "<td>".number_format($r['jumlah'])."</td>";
        echo "<td>".h($r['tanggal'])."</td>";
        echo "<td>";
        if(!empty($r['bukti'])){
            $ext = strtolower(pathinfo($r['bukti'], PATHINFO_EXTENSION));
            if(in_array($ext, ['jpg','jpeg','png','gif'])) {
                echo "<a target='_blank' href='uploads/".h($r['bukti'])."'><img style='max-height:50px' src='uploads/".h($r['bukti'])."'></a>";
            } else {
                echo "<a target='_blank' href='uploads/".h($r['bukti'])."'>".h($r['bukti'])."</a>";
            }
        } else echo '-';
        echo "</td>";
        if($show_actions) {
            echo "<td>";
            echo "<a class='btn btn-yellow' href='?p={$GLOBALS['page']}&edit=".$r['id']."'><i class='fa fa-pen'></i> Edit</a> ";
            echo "<form method='post' style='display:inline' onsubmit=\"return confirm('Hapus data ini?')\">";
            echo "<input type='hidden' name='action' value='delete'><input type='hidden' name='id' value='".$r['id']."'>";
            echo "<button class='btn btn-red' type='submit'><i class='fa fa-trash'></i> Hapus</button></form>";
            echo "</td>";
        }
        echo "</tr>";
    }
}

/* ----------- HTML output starts ---------- */
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
<style>
:root{--sidebar:#0A3D91;--topbar:#D1E6FF;--accent:#0F52BA;--bg:#E6F2FF;}
*{box-sizing:border-box}
body{margin:0;font-family:Arial;background:var(--bg);color:#0b2540}
.sidebar{width:250px;height:100vh;position:fixed;background:var(--sidebar);color:#fff;padding:18px}
.sidebar h2{margin:0 0 12px;font-size:18px}
.sidebar a{display:block;color:#f1f6ff;padding:10px;margin-bottom:6px;text-decoration:none;border-radius:6px}
.sidebar a:hover{background:#0056CC}
.topbar{margin-left:250px;height:60px;background:var(--topbar);display:flex;align-items:center;justify-content:flex-end;padding:0 20px;color:#003F82;font-weight:700;border-bottom:2px solid #B7D4FF}
.container{margin-left:250px;padding:20px}
.box{background:#fff;padding:16px;border-radius:8px;border:1px solid #C9DEFF;margin-bottom:20px}
.table{width:100%;border-collapse:collapse;margin-top:12px}
.table th{background:#A5D0FF;color:#003F82;padding:10px;text-align:left}
.table td{padding:10px;border-bottom:1px solid #DDEEFF;vertical-align:middle}
.btn{padding:8px 12px;border-radius:6px;border:none;cursor:pointer}
.btn-blue{background:#007BFF;color:#fff}
.btn-yellow{background:#FFD500;color:#000}
.btn-red{background:#E53935;color:#fff}
.btn-outline{background:transparent;border:1px solid #CDE6FF;color:var(--accent);padding:6px 10px;border-radius:6px}
.input, select{padding:8px;border:1px solid #E6F2FF;border-radius:6px;width:100%}
.small{font-size:13px;color:#6b7280}
.thumbnail{max-height:60px}
.alert{padding:10px;border-radius:6px;background:#E6FFE9;color:#026400;margin-bottom:12px}
.flex{display:flex;gap:8px;align-items:center}
.actions{display:flex;gap:8px}
</style>
</head>
<body>

<div class="sidebar">
  <h2>Tabunganku</h2>
  <a href="?p=dashboard"><i class="fa fa-house"></i> Dashboard</a>
  <a href="?p=tabungan"><i class="fa fa-wallet"></i> Semua Tabungan</a>
  <a href="?p=pribadi"><i class="fa fa-user"></i> Tabungan Pribadi</a>
  <a href="?p=kelas"><i class="fa fa-users"></i> Tabungan Kelas</a>
  <a href="?p=lainnya"><i class="fa fa-folder-open"></i> Tabungan Lainnya</a>
  <a href="?p=laporan"><i class="fa fa-file-lines"></i> Laporan</a>
  <a href="?p=export"><i class="fa fa-file-csv"></i> Export CSV</a>
  <hr style="border:none;border-top:1px solid rgba(255,255,255,0.06);margin:12px 0">
  <div class="small">Admin</div>
</div>

<div class="topbar">
  <div style="margin-right:12px"><i class="fa fa-user"></i> Admin </div>
</div>

<div class="container">
<?php if($alert): ?><div class="alert"><?= h($alert) ?></div><?php endif; ?>

<?php
/* ---------- PAGES ---------- */

/* DASHBOARD */
if ($page === 'dashboard'):
    $totalAll = 0; $totalIn = 0; $totalOut = 0;
    $all = fetch_all($mysqli);
    foreach($all as $r){ $totalAll += intval($r['jumlah']); if (stripos($r['jenis'],'keluar')!==false) $totalOut += intval($r['jumlah']); else $totalIn += intval($r['jumlah']); }
?>
  <div class="box"><h3>Dashboard Tabunganku</h3><p class="small">Selamat datang! Kelola data di sidebar.</p></div>

  <div class="box" style="display:flex;gap:12px;align-items:center">
    <div style="flex:1"><h4>Total Semua</h4><p style="font-weight:700;font-size:20px"><?= number_format($totalAll) ?></p><small class="small">Jumlah keseluruhan</small></div>
    <div style="flex:1"><h4>Total Masuk</h4><p style="font-weight:700;font-size:20px"><?= number_format($totalIn) ?></p></div>
    <div style="flex:1"><h4>Total Keluar</h4><p style="font-weight:700;font-size:20px"><?= number_format($totalOut) ?></p></div>
  </div>

<?php
/* ALL TABUNGAN (semua jenis) */
elseif ($page === 'tabungan'):
    // add/edit handled via $_GET add/edit
    if (isset($_GET['add']) || isset($_GET['edit'])) {
        $edit_id = intval($_GET['edit'] ?? 0);
        $row = ['id'=>0,'nama'=>'','jenis'=>'Tabungan Pribadi','jumlah'=>0,'tanggal'=>date('Y-m-d'),'bukti'=>''];
        if ($edit_id) {
            $res = $mysqli->query("SELECT * FROM tabungan WHERE id = $edit_id");
            if ($res && $res->num_rows) $row = $res->fetch_assoc();
        }
        ?>
        <div class="box">
          <h4><?= $edit_id ? 'Edit' : 'Tambah' ?> Data (Semua Tabungan)</h4>
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $edit_id ? 'edit' : 'add' ?>">
            <?php if($edit_id): ?><input type="hidden" name="id" value="<?= $edit_id ?>"><?php endif; ?>
            <label>Nama</label><br><input class="input" type="text" name="nama" required value="<?= h($row['nama']) ?>"><br><br>
            <label>Jenis Tabungan</label><br>
            <select class="input" name="jenis">
              <?php $jenis_list = ['Tabungan Pribadi','Uang Kas','Tabungan Kelas','Tabungan Lainnya','Kas Keluar'];
              foreach($jenis_list as $j){ $sel = ($row['jenis']=== $j)?'selected':''; echo "<option value=\"".h($j)."\" $sel>".h($j)."</option>"; } ?>
            </select><br><br>
            <div style="display:flex;gap:10px">
              <div style="flex:1"><label>Jumlah</label><br><input class="input" type="number" name="jumlah" required value="<?= h($row['jumlah']) ?>"></div>
              <div style="width:220px"><label>Tanggal</label><br><input class="input" type="date" name="tanggal" value="<?= h($row['tanggal'] ?: date('Y-m-d')) ?>"></div>
            </div><br>
            <label>Bukti (opsional)</label><br>
            <?php if(!empty($row['bukti'])): ?><div style="margin-bottom:8px">File sekarang: <a target="_blank" href="uploads/<?= h($row['bukti']) ?>"><?= h($row['bukti']) ?></a></div>
            <input type="hidden" name="existing_bukti" value="<?= h($row['bukti']) ?>"><?php endif; ?>
            <input type="file" name="bukti"><br><br>
            <button class="btn btn-blue" type="submit"><?= $edit_id ? 'Perbarui' : 'Simpan' ?></button>
            <a class="btn btn-outline" href="?p=tabungan">Batal</a>
          </form>
        </div>
    <?php } ?>

    <div class="box" style="display:flex;justify-content:space-between;align-items:center">
      <h3>Semua Tabungan</h3>
      <div class="flex">
        <a class="btn btn-blue" href="?p=tabungan&add=1"><i class="fa fa-plus"></i> Tambah</a>
        <form method="post" style="display:inline;margin-left:8px">
          <input type="hidden" name="action" value="export_csv">
          <button class="btn btn-outline" type="submit"><i class="fa fa-file-csv"></i> Export CSV</button>
        </form>
      </div>
    </div>

    <div class="box">
      <table class="table">
        <thead><tr><th>No</th><th>Nama</th><th>Jenis</th><th>Jumlah</th><th>Tanggal</th><th>Bukti</th><th>aksi<th></th></tr></thead>
        <tbody>
          <?php $rows = fetch_all($mysqli); render_table_rows($rows, true); ?>
        </tbody>
      </table>
    </div>

<?php
/* TABUNGAN PRIBADI (terpisah) */
elseif ($page === 'pribadi'):
    // jenis fixed 'Tabungan Pribadi'
    $jenis_fixed = 'Tabungan Pribadi';
    if (isset($_GET['add']) || isset($_GET['edit'])) {
        $edit_id = intval($_GET['edit'] ?? 0);
        $row = ['id'=>0,'nama'=>'','jumlah'=>0,'tanggal'=>date('Y-m-d'),'bukti'=>''];
        if ($edit_id) {
            $res = $mysqli->query("SELECT * FROM tabungan WHERE id = $edit_id AND jenis = '".$mysqli->real_escape_string($jenis_fixed)."'");
            if ($res && $res->num_rows) $row = $res->fetch_assoc();
        }
        ?>
        <div class="box">
          <h4><?= $edit_id ? 'Edit' : 'Tambah' ?> Tabungan Pribadi</h4>
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $edit_id ? 'edit' : 'add' ?>">
            <?php if($edit_id): ?><input type="hidden" name="id" value="<?= $edit_id ?>"><?php endif; ?>
            <input type="hidden" name="jenis" value="<?= h($jenis_fixed) ?>">
            <label>Nama</label><br><input class="input" type="text" name="nama" required value="<?= h($row['nama']) ?>"><br><br>
            <div style="display:flex;gap:10px">
              <div style="flex:1"><label>Jumlah</label><br><input class="input" type="number" name="jumlah" required value="<?= h($row['jumlah']) ?>"></div>
              <div style="width:220px"><label>Tanggal</label><br><input class="input" type="date" name="tanggal" value="<?= h($row['tanggal'] ?: date('Y-m-d')) ?>"></div>
            </div><br>
            <label>Bukti (opsional)</label><br>
            <?php if(!empty($row['bukti'])): ?><div style="margin-bottom:8px">File sekarang: <a target="_blank" href="uploads/<?= h($row['bukti']) ?>"><?= h($row['bukti']) ?></a></div><input type="hidden" name="existing_bukti" value="<?= h($row['bukti']) ?>"><?php endif; ?>
            <input type="file" name="bukti"><br><br>
            <button class="btn btn-blue" type="submit"><?= $edit_id ? 'Perbarui' : 'Simpan' ?></button>
            <a class="btn btn-outline" href="?p=pribadi">Batal</a>
          </form>
        </div>
    <?php } ?>

    <div class="box" style="display:flex;justify-content:space-between;align-items:center">
      <h3>Tabungan Pribadi</h3>
      <div class="flex">
        <a class="btn btn-blue" href="?p=pribadi&add=1"><i class="fa fa-plus"></i> Tambah</a>
        <a class="btn btn-outline" href="?p=pribadi&print=1"><i class="fa fa-print"></i> Cetak</a>
      </div>
    </div>

    <div class="box">
      <table class="table">
        <thead><tr><th>No</th><th>Nama</th><th>Jenis<th>Jumlah</th><th>Tanggal</th><th>Bukti</th><th>Aksi</th><th></tr></thead>
        <tbody>
          <?php $rows = fetch_all($mysqli, "jenis = '".$mysqli->real_escape_string($jenis_fixed)."'"); render_table_rows($rows, true); ?>
        </tbody>
      </table>
    </div>

<?php
/* TABUNGAN KELAS */
elseif ($page === 'kelas'):
    $jenis_fixed = 'Tabungan Kelas';
    if (isset($_GET['add']) || isset($_GET['edit'])) {
        $edit_id = intval($_GET['edit'] ?? 0);
        $row = ['id'=>0,'nama'=>'','jumlah'=>0,'tanggal'=>date('Y-m-d'),'bukti'=>'','keterangan'=>''];
        if ($edit_id) {
            $res = $mysqli->query("SELECT * FROM tabungan WHERE id = $edit_id AND jenis = '".$mysqli->real_escape_string($jenis_fixed)."'");
            if ($res && $res->num_rows) $row = $res->fetch_assoc();
        }
        ?>
        <div class="box">
          <h4><?= $edit_id ? 'Edit' : 'Tambah' ?> Tabungan Kelas</h4>
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $edit_id ? 'edit' : 'add' ?>">
            <?php if($edit_id): ?><input type="hidden" name="id" value="<?= $edit_id ?>"><?php endif; ?>
            <input type="hidden" name="jenis" value="<?= h($jenis_fixed) ?>">
            <label>Keterangan / Nama Kelas</label><br><input class="input" type="text" name="nama" required value="<?= h($row['nama']) ?>"><br><br>
            <div style="display:flex;gap:10px">
              <div style="flex:1"><label>Jumlah</label><br><input class="input" type="number" name="jumlah" required value="<?= h($row['jumlah']) ?>"></div>
              <div style="width:220px"><label>Tanggal</label><br><input class="input" type="date" name="tanggal" value="<?= h($row['tanggal'] ?: date('Y-m-d')) ?>"></div>
            </div><br>
            <label>Bukti (opsional)</label><br>
            <?php if(!empty($row['bukti'])): ?><div style="margin-bottom:8px">File sekarang: <a target="_blank" href="uploads/<?= h($row['bukti']) ?>"><?= h($row['bukti']) ?></a></div><input type="hidden" name="existing_bukti" value="<?= h($row['bukti']) ?>"><?php endif; ?>
            <input type="file" name="bukti"><br><br>
            <button class="btn btn-blue" type="submit"><?= $edit_id ? 'Perbarui' : 'Simpan' ?></button>
            <a class="btn btn-outline" href="?p=kelas">Batal</a>
          </form>
        </div>
    <?php } ?>

    <div class="box" style="display:flex;justify-content:space-between;align-items:center">
      <h3>Tabungan Kelas</h3>
      <div class="flex">
        <a class="btn btn-blue" href="?p=kelas&add=1"><i class="fa fa-plus"></i> Tambah</a>
        <a class="btn btn-outline" href="?p=kelas&print=1"><i class="fa fa-print"></i> Cetak</a>
      </div>
    </div>

    <div class="box">
      <table class="table">
        <thead><tr><th>No</th><th>Nama</th><th>Jenis<th>Jumlah</th><th>Tanggal</th><th>Bukti</th><th>Aksi</th><th></tr></thead>
        <tbody>
          <?php $rows = fetch_all($mysqli, "jenis = '".$mysqli->real_escape_string($jenis_fixed)."'"); render_table_rows($rows, true); ?>
        </tbody>
      </table>
    </div>

<?php
/* TABUNGAN LAINNYA */
elseif ($page === 'lainnya'):
    $jenis_fixed = 'Tabungan Lainnya';
    if (isset($_GET['add']) || isset($_GET['edit'])) {
        $edit_id = intval($_GET['edit'] ?? 0);
        $row = ['id'=>0,'nama'=>'','jumlah'=>0,'tanggal'=>date('Y-m-d'),'bukti'=>''];
        if ($edit_id) {
            $res = $mysqli->query("SELECT * FROM tabungan WHERE id = $edit_id AND jenis = '".$mysqli->real_escape_string($jenis_fixed)."'");
            if ($res && $res->num_rows) $row = $res->fetch_assoc();
        }
        ?>
        <div class="box">
          <h4><?= $edit_id ? 'Edit' : 'Tambah' ?> Tabungan Lainnya</h4>
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $edit_id ? 'edit' : 'add' ?>">
            <?php if($edit_id): ?><input type="hidden" name="id" value="<?= $edit_id ?>"><?php endif; ?>
            <input type="hidden" name="jenis" value="<?= h($jenis_fixed) ?>">
            <label>Nama / Keterangan</label><br><input class="input" type="text" name="nama" required value="<?= h($row['nama']) ?>"><br><br>
            <div style="display:flex;gap:10px">
              <div style="flex:1"><label>Jumlah</label><br><input class="input" type="number" name="jumlah" required value="<?= h($row['jumlah']) ?>"></div>
              <div style="width:220px"><label>Tanggal</label><br><input class="input" type="date" name="tanggal" value="<?= h($row['tanggal'] ?: date('Y-m-d')) ?>"></div>
            </div><br>
            <label>Bukti (opsional)</label><br>
            <?php if(!empty($row['bukti'])): ?><div style="margin-bottom:8px">File sekarang: <a target="_blank" href="uploads/<?= h($row['bukti']) ?>"><?= h($row['bukti']) ?></a></div><input type="hidden" name="existing_bukti" value="<?= h($row['bukti']) ?>"><?php endif; ?>
            <input type="file" name="bukti"><br><br>
            <button class="btn btn-blue" type="submit"><?= $edit_id ? 'Perbarui' : 'Simpan' ?></button>
            <a class="btn btn-outline" href="?p=lainnya">Batal</a>
          </form>
        </div>
    <?php } ?>

    <div class="box" style="display:flex;justify-content:space-between;align-items:center">
      <h3>Tabungan Lainnya</h3>
      <div class="flex">
        <a class="btn btn-blue" href="?p=lainnya&add=1"><i class="fa fa-plus"></i> Tambah</a>
        <a class="btn btn-outline" href="?p=lainnya&print=1"><i class="fa fa-print"></i> Cetak</a>
      </div>
    </div>

    <div class="box">
      <table class="table">
        <thead><tr><th>No</th><th>Nama / Keterangan</th><th>Jenis<th>Jumlah</th><th>Tanggal</th><th>Bukti</th><th>Aksi</th><th></tr></thead>
        <tbody>
          <?php $rows = fetch_all($mysqli, "jenis = '".$mysqli->real_escape_string($jenis_fixed)."'"); render_table_rows($rows, true); ?>
        </tbody>
      </table>
    </div>

<?php
/* LAPORAN (exclude pribadi/kelas/lainnya) */
elseif ($page === 'laporan'):
    // exclude these three jenis from laporan
    $rows = fetch_all($mysqli, "jenis NOT IN ('Tabungan Pribadi','Tabungan Kelas','Tabungan Lainnya')");
    if (isset($_GET['print'])) {
        echo "<div style='background:#fff;padding:12px;border-radius:8px'>";
        echo "<h3>Laporan Tabungan (Exclude Pribadi/Kelas/Lainnya)</h3><p>Dicetak: " . date('Y-m-d H:i') . "</p>";
        echo "<table style='width:100%;border-collapse:collapse;border:1px solid #ccc'><thead><tr style='background:#ccc'><th>No</th><th>Nama</th><th>Jenis</th><th>Jumlah</th><th>Tanggal</th></tr></thead><tbody>";
        $no=1; foreach($rows as $r){ echo "<tr><td>".$no++."</td><td>".h($r['nama'])."</td><td>".h($r['jenis'])."</td><td>".number_format($r['jumlah'])."</td><td>".h($r['tanggal'])."</td></tr>"; }
        echo "</tbody></table></div>";
        echo "<script>window.print();</script>"; exit;
    } else {
        echo "<div class='box'><h3>Laporan (Exclude Pribadi/Kelas/Lainnya)</h3><p class='small'>Data di bawah tidak termasuk Tabungan Pribadi, Tabungan Kelas, dan Tabungan Lainnya.</p>";
        echo "<a class='btn btn-blue' href='?p=laporan&print=1'><i class='fa fa-print'></i> Cetak</a>";
        echo "<div style='margin-top:12px'><table class='table'><thead><tr><th>No</th><th>Nama</th><th>Jenis</th><th>Jumlah</th><th>Tanggal</th></tr></thead><tbody>";
        $no=1; foreach($rows as $r) echo "<tr><td>".$no++."</td><td>".h($r['nama'])."</td><td>".h($r['jenis'])."</td><td>".number_format($r['jumlah'])."</td><td>".h($r['tanggal'])."</td></tr>";
        echo "</tbody></table></div></div>";
    }
elseif ($page === 'export'):
    // fallback (export handled via POST)
    echo "<div class='box'><p>Gunakan tombol Export di halaman Tabungan untuk mengunduh CSV.</p></div>";
else:
    echo "<div class='box'><p>Halaman tidak ditemukan.</p></div>";
endif;
?>

</div>

<script>
/* kecil: pindahkan ke global jika perlu */
function confirmDelete(){ return confirm('Hapus data?'); }
</script>

</body>
</html>
