<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Bendahara</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<style>
  :root{--blue:#0a57b3;--soft:#eaf4ff;}
  *{box-sizing:border-box}
  body{margin:0;font-family:Arial,Helvetica,sans-serif;background:var(--soft);color:#0b2540}
  .sidebar{width:240px;position:fixed;left:0;top:0;bottom:0;background:var(--blue);color:#fff;padding:18px}
  .sidebar h2{margin:0 0 12px;font-size:18px}
  .nav-link{display:block;color:#e8f4ff;padding:10px;border-radius:8px;text-decoration:none;margin-bottom:6px;cursor:pointer}
  .nav-link:hover{background:#0d4ea0}
  .topbar{margin-left:240px;height:58px;background:var(--blue);display:flex;align-items:center;justify-content:flex-end;padding:0 20px;color:#fff}
  .container{margin-left:250px;padding:20px}
  .cards{display:flex;gap:14px;flex-wrap:wrap}
  .card{flex:1;min-width:180px;background:#fff;padding:14px;border-radius:10px;box-shadow:0 6px 18px rgba(248, 249, 250, 1)}
  .card h4{margin:0;font-size:13px;color:#333}
  .card p{margin:8px 0 0;font-weight:700;color:var(--blue)}
  .box{background:#fff;padding:14px;border-radius:10px;margin-top:14px;box-shadow:0 6px 18px rgba(10,87,179,0.04)}
  .actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
  .btn{padding:8px 12px;border-radius:8px;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:8px}
  .btn-blue{background:var(--blue);color:#fff}
  .btn-green{background:#09a44a;color:#fff}
  .btn-red{background:#e04141;color:#fff}
  .btn-outline{background:transparent;border:1px solid #dbeefd;color:var(--blue)}
  .search{padding:8px;border-radius:8px;border:1px solid #dbeefd;min-width:200px}
  table{width:100%;border-collapse:collapse;margin-top:12px}
  th,td{padding:10px;border-bottom:1px solid #eef6ff;text-align:left}
  th{background:var(--blue);color:#fff;position:sticky;top:0}
  .modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);z-index:50}
  .modal.show{display:flex}
  .modal-card{width:520px;background:#fff;border-radius:10px;padding:18px;box-shadow:0 10px 30px rgba(0,0,0,0.2)}
  .form-row{display:flex;gap:8px}
  .form-row .col{flex:1}
  input,select,textarea{width:100%;padding:8px;border-radius:8px;border:1px solid #e6f2ff}
  label{font-size:13px;color:#374151}
  @media(max-width:900px){.sidebar{display:none}.topbar{margin-left:0}.container{margin-left:0;padding:12px}.modal-card{width:92%}}
</style>
</head>
<body>

<div class="sidebar">
  <h2>Bendahara</h2>
  <div class="nav-link" data-page="dashboard"><i class="fa fa-house"></i> Dashboard</div>
  <div class="nav-link" data-page="kasMasuk"><i class="fa fa-arrow-down"></i> Kas Masuk</div>
  <div class="nav-link" data-page="kasKeluar"><i class="fa fa-arrow-up"></i> Kas Keluar</div>
  <div class="nav-link" data-page="tabPribadi"><i class="fa fa-wallet"></i> Tabungan Pribadi</div>
  <div class="nav-link" data-page="tabKelas"><i class="fa fa-users"></i> Tabungan Kelas</div>
  <div class="nav-link" data-page="tabLainnya"><i class="fa fa-folder-open"></i> Tabungan Lainnya</div>
  <div class="nav-link" data-page="siswa"><i class="fa fa-user-graduate"></i> Data Siswa</div>
</div>

<div class="topbar">
  <div style="margin-right:12px"><i class="fa fa-user"></i> Bendahara</div>
</div>

<div class="container">
  <!-- Dashboard -->
  <div id="dashboard" class="page active">
    <div class="cards">
      <div class="card"><h4>Total Kas Masuk</h4><p id="sumMasuk">Rp 0</p></div>
      <div class="card"><h4>Total Kas Keluar</h4><p id="sumKeluar">Rp 0</p></div>
      <div class="card"><h4>Saldo</h4><p id="saldoAll">Rp 0</p></div>
      <div class="card"><h4>Total Tabungan Pribadi</h4><p id="sumPribadi">Rp 0</p></div>
    </div>
    <div class="box"><h4>Ringkasan</h4><p class="small">Semua data tersimpan.</p></div>
  </div>

  <!-- Kas Masuk -->
  <div id="kasMasuk" class="page box" style="display:none">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <div class="actions">
        <button class="btn btn-blue" onclick="openModal('kasMasuk')"><i class="fa fa-plus"></i> Tambah</button>
        <button class="btn btn-outline" onclick="exportCSV('kasMasuk')"><i class="fa fa-file-csv"></i> Export</button>
        <button class="btn btn-outline" onclick="importCSV('kasMasuk')"><i class="fa fa-file-import"></i> Import</button>
      </div>
      <div class="actions">
        <input id="searchKasMasuk" class="search" placeholder="Cari nama / keterangan..." oninput="renderTable('kasMasuk')">
        <button class="btn btn-outline" onclick="printCategory('kasMasuk')"><i class="fa fa-print"></i></button>
      </div>
    </div>
    <table id="tbl-kasMasuk"><thead><tr><th>No</th><th>Nama</th><th>Keterangan</th><th>Jumlah</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody></tbody></table>
  </div>

  <!-- Kas Keluar -->
  <div id="kasKeluar" class="page box" style="display:none">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <div class="actions">
        <button class="btn btn-blue" onclick="openModal('kasKeluar')"><i class="fa fa-plus"></i> Tambah</button>
        <button class="btn btn-outline" onclick="exportCSV('kasKeluar')"><i class="fa fa-file-csv"></i> Export</button>
      </div>
      <div class="actions">
        <input id="searchKasKeluar" class="search" placeholder="Cari keterangan..." oninput="renderTable('kasKeluar')">
        <button class="btn btn-outline" onclick="printCategory('kasKeluar')"><i class="fa fa-print"></i></button>
      </div>
    </div>
    <table id="tbl-kasKeluar"><thead><tr><th>No</th><th>Keterangan</th><th>Jumlah</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody></tbody></table>
  </div>

  <!-- Tabungan Pribadi -->
  <div id="tabPribadi" class="page box" style="display:none">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <div class="actions">
        <button class="btn btn-blue" onclick="openModal('tabPribadi')"><i class="fa fa-plus"></i> Tambah</button>
        <button class="btn btn-outline" onclick="exportCSV('tabPribadi')"><i class="fa fa-file-csv"></i> Export</button>
      </div>
      <div class="actions">
        <input id="searchTabPribadi" class="search" placeholder="Cari nama / catatan..." oninput="renderTable('tabPribadi')">
        <button class="btn btn-outline" onclick="printCategory('tabPribadi')"><i class="fa fa-print"></i></button>
      </div>
    </div>
    <table id="tbl-tabPribadi"><thead><tr><th>No</th><th>Nama</th><th>Jumlah</th><th>Catatan</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody></tbody></table>
  </div>

  <!-- Tabungan Kelas -->
  <div id="tabKelas" class="page box" style="display:none">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <div class="actions">
        <button class="btn btn-blue" onclick="openModal('tabKelas')"><i class="fa fa-plus"></i> Tambah</button>
        <button class="btn btn-outline" onclick="exportCSV('tabKelas')"><i class="fa fa-file-csv"></i> Export</button>
      </div>
      <div class="actions">
        <input id="searchTabKelas" class="search" placeholder="Cari..." oninput="renderTable('tabKelas')">
        <button class="btn btn-outline" onclick="printCategory('tabKelas')"><i class="fa fa-print"></i></button>
      </div>
    </div>
    <table id="tbl-tabKelas"><thead><tr><th>No</th><th>Keterangan</th><th>Jumlah</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody></tbody></table>
  </div>

  <!-- Tabungan Lainnya -->
  <div id="tabLainnya" class="page box" style="display:none">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <div class="actions">
        <button class="btn btn-blue" onclick="openModal('tabLainnya')"><i class="fa fa-plus"></i> Tambah</button>
        <button class="btn btn-outline" onclick="exportCSV('tabLainnya')"><i class="fa fa-file-csv"></i> Export</button>
      </div>
      <div class="actions">
        <input id="searchTabLainnya" class="search" placeholder="Cari..." oninput="renderTable('tabLainnya')">
        <button class="btn btn-outline" onclick="printCategory('tabLainnya')"><i class="fa fa-print"></i></button>
      </div>
    </div>
    <table id="tbl-tabLainnya"><thead><tr><th>No</th><th>Nama / Keterangan</th><th>Jumlah</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody></tbody></table>
  </div>

  <!-- Data Siswa -->
  <div id="siswa" class="page box" style="display:none">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <div class="actions">
        <button class="btn btn-blue" onclick="openModal('siswa')"><i class="fa fa-plus"></i> Tambah Siswa</button>
        <button class="btn btn-outline" onclick="exportCSV('siswa')"><i class="fa fa-file-csv"></i> Export</button>
      </div>
      <div class="actions">
        <input id="searchSiswa" class="search" placeholder="Cari nama / kelas..." oninput="renderTable('siswa')">
        <button class="btn btn-outline" onclick="printCategory('siswa')"><i class="fa fa-print"></i></button>
      </div>
    </div>
    <table id="tbl-siswa"><thead><tr><th>No</th><th>Nama</th><th>Kelas</th><th>Aksi</th></tr></thead><tbody></tbody></table>
  </div>
</div>

<!-- Modal -->
<div id="modal" class="modal" role="dialog" aria-hidden="true">
  <div class="modal-card">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <h3 id="modalTitle">Form</h3>
      <button onclick="closeModal()" class="btn btn-red"><i class="fa fa-times"></i></button>
    </div>
    <div id="modalBody" style="margin-top:12px"></div>
    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px">
      <button class="btn btn-outline" onclick="closeModal()">Batal</button>
      <button class="btn btn-blue" onclick="saveModal()">Simpan</button>
    </div>
  </div>
</div>

<input type="file" id="csvImportFile" accept=".csv" style="display:none" />

<script>
/* State & storage */
const STORAGE_KEY = 'tabungan_bendahara_v3';
let state = {
  kasMasuk: [], kasKeluar: [], tabPribadi: [], tabKelas: [], tabLainnya: [], siswa: []
};

function loadState(){ try{ const raw = localStorage.getItem(STORAGE_KEY); if(raw) state = JSON.parse(raw); }catch(e){console.error(e)} }
function saveState(){ localStorage.setItem(STORAGE_KEY, JSON.stringify(state)); }

/* Utils */
function q(s){ return document.querySelector(s) }
function qAll(s){ return Array.from(document.querySelectorAll(s)) }
function money(v){ v = Number(v)||0; return 'Rp ' + v.toLocaleString('id-ID') }
function num(v){ return Number(String(v).replace(/[^0-9.-]+/g,'')) || 0 }
function today(){ return new Date().toISOString().slice(0,10) }

/* Navigation */
const pages = ['dashboard','kasMasuk','kasKeluar','tabPribadi','tabKelas','tabLainnya','siswa'];
qAll('.nav-link').forEach(a => a.addEventListener('click', ()=> showPage(a.dataset.page)));

function showPage(page){
  pages.forEach(p => { const el = q('#'+p); if(el) el.style.display = (p===page ? 'block' : 'none'); });
  if(page==='dashboard') renderDashboard(); else renderTable(page);
}

/* Render */
function renderDashboard(){
  const sumIn = state.kasMasuk.reduce((s,r)=> s + num(r.jumlah),0);
  const sumOut = state.kasKeluar.reduce((s,r)=> s + num(r.jumlah),0);
  const sumPrib = state.tabPribadi.reduce((s,r)=> s + num(r.jumlah),0);
  q('#sumMasuk').innerText = money(sumIn);
  q('#sumKeluar').innerText = money(sumOut);
  q('#saldoAll').innerText = money(sumIn - sumOut);
  q('#sumPribadi').innerText = money(sumPrib);
}

/* Generic table render */
function renderTable(category){
  const tbody = q(`#tbl-${category} tbody`);
  if(!tbody) return;
  tbody.innerHTML = '';
  const qstrEl = q(`#search${category.charAt(0).toUpperCase()+category.slice(1)}`) || {value:''};
  const qstr = (qstrEl.value||'').trim().toLowerCase();
  const rows = state[category] || [];

  rows.forEach((r,i)=>{
    const combined = Object.values(r).join(' ').toLowerCase();
    if(qstr && !combined.includes(qstr)) return;

    let tr = document.createElement('tr');
    let actions = `<td>
      <button class="btn btn-outline" onclick="openModal('${category}', ${i})"><i class="fa fa-edit"></i></button>
      <button class="btn btn-red" onclick="deleteRow('${category}', ${i})"><i class="fa fa-trash"></i></button>
    </td>`;

    if(category==='kasMasuk'){
      tr.innerHTML = `<td>${i+1}</td><td>${escapeHtml(r.nama)}</td><td>${escapeHtml(r.keterangan)}</td><td>${money(r.jumlah)}</td><td>${r.tanggal||''}</td>${actions}`;
    } else if(category==='kasKeluar'){
      tr.innerHTML = `<td>${i+1}</td><td>${escapeHtml(r.keterangan)}</td><td>${money(r.jumlah)}</td><td>${r.tanggal||''}</td>${actions}`;
    } else if(category==='tabPribadi'){
      tr.innerHTML = `<td>${i+1}</td><td>${escapeHtml(r.nama)}</td><td>${money(r.jumlah)}</td><td>${escapeHtml(r.catatan||'')}</td><td>${r.tanggal||''}</td>${actions}`;
    } else if(category==='tabKelas'){
      tr.innerHTML = `<td>${i+1}</td><td>${escapeHtml(r.keterangan)}</td><td>${money(r.jumlah)}</td><td>${r.tanggal||''}</td>${actions}`;
    } else if(category==='tabLainnya'){
      tr.innerHTML = `<td>${i+1}</td><td>${escapeHtml(r.nama||r.keterangan)}</td><td>${money(r.jumlah)}</td><td>${r.tanggal||''}</td>${actions}`;
    } else if(category==='siswa'){
      tr.innerHTML = `<td>${i+1}</td><td>${escapeHtml(r.nama)}</td><td>${escapeHtml(r.kelas||'')}</td>${actions}`;
    }
    tbody.appendChild(tr);
  });

  renderDashboard();
}

/* Modal (add/edit) */
let modalState = { category:null, index:-1 };
function openModal(category, index = -1){
  modalState = { category, index };
  const titleMap = {
    kasMasuk: index===-1?'Tambah Kas Masuk':'Edit Kas Masuk',
    kasKeluar: index===-1?'Tambah Kas Keluar':'Edit Kas Keluar',
    tabPribadi: index===-1?'Tambah Tabungan Pribadi':'Edit Tabungan Pribadi',
    tabKelas: index===-1?'Tambah Tabungan Kelas':'Edit Tabungan Kelas',
    tabLainnya: index===-1?'Tambah Tabungan Lainnya':'Edit Tabungan Lainnya',
    siswa: index===-1?'Tambah Siswa':'Edit Siswa'
  };
  q('#modalTitle').innerText = titleMap[category] || 'Form';
  const body = q('#modalBody'); body.innerHTML = '';
  const existing = (state[category] && state[category][index]) || {};

  if(category==='kasMasuk'){
    body.innerHTML = `
      <label>Nama</label><input id="f_nama" value="${escapeAttr(existing.nama||'')}" />
      <label>Keterangan</label><input id="f_keterangan" value="${escapeAttr(existing.keterangan||'')}" />
      <div class="form-row"><div class="col"><label>Jumlah</label><input id="f_jumlah" type="number" value="${existing.jumlah||''}" /></div>
      <div class="col"><label>Tanggal</label><input id="f_tanggal" type="date" value="${existing.tanggal||today()}" /></div></div>
    `;
  } else if(category==='kasKeluar'){
    body.innerHTML = `
      <label>Keterangan</label><input id="f_keterangan" value="${escapeAttr(existing.keterangan||'')}" />
      <div class="form-row"><div class="col"><label>Jumlah</label><input id="f_jumlah" type="number" value="${existing.jumlah||''}" /></div>
      <div class="col"><label>Tanggal</label><input id="f_tanggal" type="date" value="${existing.tanggal||today()}" /></div></div>
    `;
  } else if(category==='tabPribadi'){
    body.innerHTML = `
      <label>Nama</label><input id="f_nama" value="${escapeAttr(existing.nama||'')}" />
      <label>Jumlah</label><input id="f_jumlah" type="number" value="${existing.jumlah||''}" />
      <label>Catatan</label><input id="f_catatan" value="${escapeAttr(existing.catatan||'')}" />
      <label>Tanggal</label><input id="f_tanggal" type="date" value="${existing.tanggal||today()}" />
    `;
  } else if(category==='tabKelas'){
    body.innerHTML = `
      <label>Keterangan</label><input id="f_keterangan" value="${escapeAttr(existing.keterangan||'')}" />
      <label>Jumlah</label><input id="f_jumlah" type="number" value="${existing.jumlah||''}" />
      <label>Tanggal</label><input id="f_tanggal" type="date" value="${existing.tanggal||today()}" />
    `;
  } else if(category==='tabLainnya'){
    body.innerHTML = `
      <label>Nama / Keterangan</label><input id="f_nama" value="${escapeAttr(existing.nama||existing.keterangan||'')}" />
      <label>Jumlah</label><input id="f_jumlah" type="number" value="${existing.jumlah||''}" />
      <label>Tanggal</label><input id="f_tanggal" type="date" value="${existing.tanggal||today()}" />
    `;
  } else if(category==='siswa'){
    body.innerHTML = `
      <label>Nama</label><input id="f_nama" value="${escapeAttr(existing.nama||'')}" />
      <label>Kelas</label><input id="f_kelas" value="${escapeAttr(existing.kelas||'')}" />
    `;
  } else {
    body.innerHTML = '<p>Form tidak tersedia</p>';
  }

  q('#modal').classList.add('show');
}

function closeModal(){ q('#modal').classList.remove('show'); modalState = {category:null,index:-1}; }

/* Save modal */
function saveModal(){
  const cat = modalState.category; const idx = modalState.index;
  if(!cat) return closeModal();

  if(cat==='kasMasuk'){
    const nama = q('#f_nama').value.trim(); const ket = q('#f_keterangan').value.trim();
    const jumlah = num(q('#f_jumlah').value); const tanggal = q('#f_tanggal').value || today();
    if(!nama){ alert('Nama wajib diisi'); return; }
    const obj = {nama, keterangan:ket, jumlah, tanggal};
    if(idx>=0) state.kasMasuk[idx]=obj; else state.kasMasuk.push(obj);
  } else if(cat==='kasKeluar'){
    const ket = q('#f_keterangan').value.trim(); const jumlah = num(q('#f_jumlah').value); const tanggal = q('#f_tanggal').value||today();
    if(!ket || !jumlah){ alert('Keterangan & jumlah wajib diisi'); return; }
    const obj = {keterangan:ket, jumlah, tanggal};
    if(idx>=0) state.kasKeluar[idx]=obj; else state.kasKeluar.push(obj);
  } else if(cat==='tabPribadi'){
    const nama = q('#f_nama').value.trim(); const jumlah = num(q('#f_jumlah').value); const catatan = q('#f_catatan').value.trim(); const tanggal = q('#f_tanggal').value||today();
    if(!nama){ alert('Nama wajib diisi'); return; }
    const obj = {nama, jumlah, catatan, tanggal};
    if(idx>=0) state.tabPribadi[idx]=obj; else state.tabPribadi.push(obj);
  } else if(cat==='tabKelas'){
    const ket = q('#f_keterangan').value.trim(); const jumlah = num(q('#f_jumlah').value); const tanggal = q('#f_tanggal').value||today();
    if(!ket || !jumlah){ alert('Keterangan & jumlah wajib diisi'); return; }
    const obj = {keterangan:ket, jumlah, tanggal};
    if(idx>=0) state.tabKelas[idx]=obj; else state.tabKelas.push(obj);
  } else if(cat==='tabLainnya'){
    const nama = q('#f_nama').value.trim(); const jumlah = num(q('#f_jumlah').value); const tanggal = q('#f_tanggal').value||today();
    if(!nama || !jumlah){ alert('Nama & jumlah wajib diisi'); return; }
    const obj = {nama, jumlah, tanggal};
    if(idx>=0) state.tabLainnya[idx]=obj; else state.tabLainnya.push(obj);
  } else if(cat==='siswa'){
    const nama = q('#f_nama').value.trim(); const kelas = q('#f_kelas').value.trim();
    if(!nama){ alert('Nama wajib diisi'); return; }
    const obj = {nama, kelas};
    if(idx>=0) state.siswa[idx]=obj; else state.siswa.push(obj);
  }

  saveState(); closeModal(); renderTable(cat); renderDashboard();
}

/* Delete */
function deleteRow(category, index){
  if(!confirm('Hapus data ini?')) return;
  state[category].splice(index,1); saveState(); renderTable(category); renderDashboard();
}

/* Print / CSV */
function printCategory(category){
  const rows = state[category]||[]; if(rows.length===0){ alert('Tidak ada data untuk dicetak'); return; }
  let title='', header='', rowsHtml='';
  if(category==='kasMasuk'){ title='Laporan Kas Masuk'; header=`<tr><th>No</th><th>Nama</th><th>Keterangan</th><th>Jumlah</th><th>Tanggal</th></tr>`; rowsHtml = rows.map((r,i)=>`<tr><td>${i+1}</td><td>${escapeHtml(r.nama)}</td><td>${escapeHtml(r.keterangan)}</td><td>${money(r.jumlah)}</td><td>${r.tanggal||''}</td></tr>`).join(''); }
  else if(category==='kasKeluar'){ title='Laporan Kas Keluar'; header=`<tr><th>No</th><th>Keterangan</th><th>Jumlah</th><th>Tanggal</th></tr>`; rowsHtml = rows.map((r,i)=>`<tr><td>${i+1}</td><td>${escapeHtml(r.keterangan)}</td><td>${money(r.jumlah)}</td><td>${r.tanggal||''}</td></tr>`).join(''); }
  else if(category==='tabPribadi'){ title='Laporan Tabungan Pribadi'; header=`<tr><th>No</th><th>Nama</th><th>Jumlah</th><th>Catatan</th><th>Tanggal</th></tr>`; rowsHtml = rows.map((r,i)=>`<tr><td>${i+1}</td><td>${escapeHtml(r.nama)}</td><td>${money(r.jumlah)}</td><td>${escapeHtml(r.catatan||'')}</td><td>${r.tanggal||''}</td></tr>`).join(''); }
  else if(category==='tabKelas'){ title='Laporan Tabungan Kelas'; header=`<tr><th>No</th><th>Keterangan</th><th>Jumlah</th><th>Tanggal</th></tr>`; rowsHtml = rows.map((r,i)=>`<tr><td>${i+1}</td><td>${escapeHtml(r.keterangan)}</td><td>${money(r.jumlah)}</td><td>${r.tanggal||''}</td></tr>`).join(''); }
  else if(category==='tabLainnya'){ title='Laporan Tabungan Lainnya'; header=`<tr><th>No</th><th>Nama/Keterangan</th><th>Jumlah</th><th>Tanggal</th></tr>`; rowsHtml = rows.map((r,i)=>`<tr><td>${i+1}</td><td>${escapeHtml(r.nama)}</td><td>${money(r.jumlah)}</td><td>${r.tanggal||''}</td></tr>`).join(''); }
  else if(category==='siswa'){ title='Daftar Siswa'; header=`<tr><th>No</th><th>Nama</th><th>Kelas</th></tr>`; rowsHtml = rows.map((r,i)=>`<tr><td>${i+1}</td><td>${escapeHtml(r.nama)}</td><td>${escapeHtml(r.kelas||'')}</td></tr>`).join(''); }
  const html = `<html><head><title>${title}</title><style>body{font-family:Arial;padding:16px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #333;padding:8px}th{background:#1e88e5;color:#fff}</style></head><body><h3>${title}</h3><p>Dicetak: ${new Date().toLocaleString()}</p><table><thead>${header}</thead><tbody>${rowsHtml}</tbody></table></body></html>`;
  const w = window.open('','_blank','width=900,height=700'); w.document.write(html); w.document.close(); w.print();
}

function exportCSV(category){
  const rows = state[category]||[]; if(rows.length===0){ alert('Tidak ada data'); return; }
  let csv='', fname = category + '.csv';
  if(category==='kasMasuk'){ csv += 'Nama,Keterangan,Jumlah,Tanggal\n'; rows.forEach(r=> csv += `${escapeCsv(r.nama)},${escapeCsv(r.keterangan)},${Number(r.jumlah||0)},${escapeCsv(r.tanggal)}\n`); }
  else if(category==='kasKeluar'){ csv += 'Keterangan,Jumlah,Tanggal\n'; rows.forEach(r=> csv += `${escapeCsv(r.keterangan)},${Number(r.jumlah||0)},${escapeCsv(r.tanggal)}\n`); }
  else if(category==='tabPribadi'){ csv += 'Nama,Jumlah,Catatan,Tanggal\n'; rows.forEach(r=> csv += `${escapeCsv(r.nama)},${Number(r.jumlah||0)},${escapeCsv(r.catatan)},${escapeCsv(r.tanggal)}\n`); }
  else if(category==='tabKelas' || category==='tabLainnya'){ csv += 'Keterangan,Jumlah,Tanggal\n'; rows.forEach(r=> csv += `${escapeCsv(r.keterangan||r.nama)},${Number(r.jumlah||0)},${escapeCsv(r.tanggal)}\n`); }
  else if(category==='siswa'){ csv += 'Nama,Kelas\n'; rows.forEach(r=> csv += `${escapeCsv(r.nama)},${escapeCsv(r.kelas)}\n`); }
  downloadFile(csv, fname, 'text/csv');
}
function escapeCsv(s){ if(s===undefined||s===null) return ''; return '"'+String(s).replace(/"/g,'""')+'"' }
function downloadFile(content, filename, mime){ const blob = new Blob([content], {type:mime}); const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = filename; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url); }

function importCSV(category){
  const input = q('#csvImportFile');
  input.onchange = e => {
    const file = e.target.files[0]; if(!file) return;
    const reader = new FileReader();
    reader.onload = evt => { parseImport(category, evt.target.result); input.value=''; saveState(); renderTable(category); renderDashboard(); alert('Import selesai'); }
    reader.readAsText(file);
  }
  input.click();
}
function parseImport(category, txt){
  const lines = txt.split(/\r?\n/).map(r=>r.trim()).filter(r=>r.length>0);
  if(lines.length<=1) return;
  const rows = lines.slice(1);
  rows.forEach(line=>{
    const cols = splitCsv(line);
    if(category==='kasMasuk') state.kasMasuk.push({nama:cols[0]||'', keterangan:cols[1]||'', jumlah:num(cols[2]||0), tanggal:cols[3]||today()});
    if(category==='kasKeluar') state.kasKeluar.push({keterangan:cols[0]||'', jumlah:num(cols[1]||0), tanggal:cols[2]||today()});
    if(category==='tabPribadi') state.tabPribadi.push({nama:cols[0]||'', jumlah:num(cols[1]||0), catatan:cols[2]||'', tanggal:cols[3]||today()});
    if(category==='tabKelas' || category==='tabLainnya') state[category].push({keterangan:cols[0]||cols[1]||'', jumlah:num(cols[1]||0), tanggal:cols[2]||today()});
    if(category==='siswa') state.siswa.push({nama:cols[0]||'', kelas:cols[1]||''});
  });
}
function splitCsv(line){ const out=[]; let cur='', inQ=false; for(let ch of line){ if(ch==='"'){ inQ=!inQ; continue; } if(ch===',' && !inQ){ out.push(cur); cur=''; continue; } cur+=ch;} out.push(cur); return out.map(s=>s.trim()); }

/* Helpers */
function escapeHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') }
function escapeAttr(s){ return String(s||'').replace(/"/g,'&quot;') }
function today(){ return new Date().toISOString().slice(0,10) }

/* Init */
loadState();
showPage('dashboard');
['kasMasuk','kasKeluar','tabPribadi','tabKelas','tabLainnya','siswa'].forEach(cat => { if(q(`#tbl-${cat}`)) renderTable(cat); });
renderDashboard();

/* Expose openModal globally for inline calls */
window.openModal = openModal;
window.deleteRow = deleteRow;
window.exportCSV = exportCSV;
window.importCSV = importCSV;
window.printCategory = printCategory;

/* Save state when window unloaded (optional) */
window.addEventListener('beforeunload', saveState);
</script>
</body>
</html>
