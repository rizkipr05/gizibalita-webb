<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_nakes();

// TODO: nanti ganti dummy ini dengan SELECT dari tabel `pemeriksaans` JOIN `balitas` & `users`
$pemeriksaanList = [
    [
        'id'           => 1,
        'tanggal'      => '2025-03-10',
        'nama_balita'  => 'Aisyah Putri',
        'umur_bulan'   => 24,
        'bb'           => 11.5,
        'tb'           => 85.0,
        'status_gizi'  => 'Gizi Baik',
        'nakes'        => 'drg. Rina',
    ],
    [
        'id'           => 2,
        'tanggal'      => '2025-03-09',
        'nama_balita'  => 'Raka Dwi Pratama',
        'umur_bulan'   => 20,
        'bb'           => 10.1,
        'tb'           => 81.0,
        'status_gizi'  => 'Gizi Kurang',
        'nakes'        => 'dr. Budi',
    ],
    [
        'id'           => 3,
        'tanggal'      => '2025-03-08',
        'nama_balita'  => 'Nadia Salsabila',
        'umur_bulan'   => 18,
        'bb'           => 9.7,
        'tb'           => 79.0,
        'status_gizi'  => 'Risiko Gizi Lebih',
        'nakes'        => 'drg. Rina',
    ],
];

function badgeClassNakes($status) {
    switch ($status) {
        case 'Gizi Baik':
            return 'bg-success';
        case 'Gizi Kurang':
        case 'Gizi Buruk':
            return 'bg-danger';
        case 'Risiko Gizi Lebih':
            return 'bg-warning text-dark';
        case 'Gizi Lebih':
        case 'Obesitas':
            return 'bg-primary';
        default:
            return 'bg-secondary';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Pemeriksaan - Gizi Balita (Nakes)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <style>
     :root {
      --brand-main: #0f9d58;
      --brand-soft: #e0f7ec;
      --brand-dark: #0b7542;
    }

    body {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      background: radial-gradient(circle at top left, #eef7ff 0, #f8fffb 35%, #ffffff 100%);
    }

    .navbar-nakes {
      background: linear-gradient(120deg, #0b5ed7, #0f9d58);
    }
    .navbar-nakes .navbar-brand,
    .navbar-nakes .nav-link {
      color: #fdfdfd !important;
    }
    .navbar-nakes .nav-link {
      opacity: .9;
    }
    .navbar-nakes .nav-link:hover {
      opacity: 1;
    }
    .navbar-nakes .nav-link.active {
      font-weight: 600;
      border-bottom: 2px solid #ffffffcc;
    }

    /* ✅ Dropdown tidak putih lagi, ikut tema navbar */
    .navbar-nakes .dropdown-menu {
      background: linear-gradient(120deg, #0b5ed7cc, #0f9d58cc) !important;
      border-radius: 0.75rem;
      border: none;
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
      backdrop-filter: blur(6px);
      padding-top: .4rem;
      padding-bottom: .4rem;
    }

    .navbar-nakes .dropdown-item {
      color: #ffffff !important;
      font-size: 0.9rem;
    }

    .navbar-nakes .dropdown-item:hover {
      background-color: rgba(255,255,255,0.16) !important;
      color: #ffffff !important;
    }

    .navbar-nakes .dropdown-item.text-danger {
      color: #ffb3b8 !important;
    }
    .navbar-nakes .dropdown-item.text-danger:hover {
      background-color: rgba(255, 99, 132, 0.2) !important;
      color: #ffe6e8 !important;
    }

    .page-wrapper {
      flex: 1 0 auto;
      padding: 28px 0 40px;
    }
    .card-soft {
      border: none;
      border-radius: 1.1rem;
      box-shadow: 0 14px 32px rgba(0,0,0,0.08);
      background: #ffffff;
    }

    .table thead th {
      font-size: .8rem;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: #6c757d;
      border-bottom-width: 1px;
    }
    .table td {
      vertical-align: middle;
      font-size: .86rem;
    }
    .badge-status {
      font-size: .75rem;
      padding: .25rem .6rem;
      border-radius: 999px;
    }

    footer {
      flex-shrink: 0;
      background: linear-gradient(120deg, #0b3a60, #0b7542);
      color: #e2f6fa;
      font-size: .85rem;
    }
  </style>
</head>
<body>

<!-- =============== NAVBAR NAKES =============== -->
<nav class="navbar navbar-expand-lg navbar-nakes shadow-sm">
  <div class="container-fluid px-4 px-md-5">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= BASE_URL ?>/nakes/dashboard.php">
      <i class="bi bi-hospital me-2"></i>
      <span>GiziBalita | Nakes</span>
    </a>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNakes">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNakes">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">

        <li class="nav-item mx-lg-1">
          <a class="nav-link" href="<?= BASE_URL ?>/nakes/dashboard.php">Dashboard</a>
        </li>

        <li class="nav-item mx-lg-1">
          <a class="nav-link" href="<?= BASE_URL ?>/nakes/balita_list.php">Data Balita</a>
        </li>

        <li class="nav-item mx-lg-1">
          <a class="nav-link" href="<?= BASE_URL ?>/nakes/pemeriksaan_input.php">Input Pemeriksaan</a>
        </li>

        <li class="nav-item mx-lg-1">
          <a class="nav-link active" href="<?= BASE_URL ?>/nakes/pemeriksaan_list.php">Riwayat Pemeriksaan</a>
        </li>

        <li class="nav-item mx-lg-1">
          <a class="nav-link" href="<?= BASE_URL ?>/nakes/artikel_manage.php">Artikel Edukasi</a>
        </li>

        <li class="nav-item dropdown ms-lg-3">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="<?=  BASE_URL ?>/nakes/profile.php" data-bs-toggle="dropdown">
            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;">
              <i class="bi bi-person-badge-fill"></i>
            </div>
            <span class="d-none d-sm-inline">
              <?= htmlspecialchars($_SESSION['name'] ?? 'Tenaga Kesehatan'); ?>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end mt-2">
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/nakes/profile.php">
                <i class="bi bi-person-circle me-2"></i> Profil
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
              </a>
            </li>
          </ul>
        </li>

      </ul>
    </div>
  </div>
</nav>
<!-- ========= END NAVBAR ========= -->


<!-- =============== MAIN CONTENT =============== -->
<div class="page-wrapper">
  <div class="container-fluid px-4 px-md-5">

    <!-- Header -->
    <div class="row mb-3">
      <div class="col-12 d-md-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-1">Riwayat Pemeriksaan Gizi</h4>
          <p class="text-muted mb-0">
            Daftar seluruh pemeriksaan status gizi balita yang pernah dicatat di sistem.
          </p>
        </div>
        <div class="mt-3 mt-md-0">
          <a href="<?= BASE_URL ?>/nakes/pemeriksaan_input.php" class="btn btn-primary btn-sm">
            <i class="bi bi-clipboard2-plus me-1"></i> Input Pemeriksaan Baru
          </a>
        </div>
      </div>
    </div>

    <!-- Filter / Pencarian -->
    <div class="card card-soft mb-4">
      <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Nama Balita / Ortu</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input
                type="text"
                name="q"
                class="form-control"
                placeholder="Cari nama balita / orang tua..."
                value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
              >
            </div>
          </div>

          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Periode Tanggal</label>
            <div class="d-flex gap-1">
              <input
                type="date"
                name="tgl_awal"
                class="form-control form-control-sm"
                value="<?= htmlspecialchars($_GET['tgl_awal'] ?? '') ?>"
              >
              <span class="align-self-center small text-muted">s.d</span>
              <input
                type="date"
                name="tgl_akhir"
                class="form-control form-control-sm"
                value="<?= htmlspecialchars($_GET['tgl_akhir'] ?? '') ?>"
              >
            </div>
          </div>

          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Status Gizi</label>
            <select name="status" class="form-select form-select-sm">
              <option value="">Semua</option>
              <?php
              $statusOpt = [
                  'Gizi Buruk',
                  'Gizi Kurang',
                  'Gizi Baik',
                  'Risiko Gizi Lebih',
                  'Gizi Lebih',
                  'Obesitas',
              ];
              $statusQ = $_GET['status'] ?? '';
              foreach ($statusOpt as $s):
              ?>
                <option value="<?= htmlspecialchars($s) ?>" <?= $statusQ === $s ? 'selected' : '' ?>>
                  <?= htmlspecialchars($s) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-3 text-md-end">
            <button type="submit" class="btn btn-success btn-sm me-1">
              <i class="bi bi-funnel me-1"></i> Filter
            </button>
            <a href="<?= BASE_URL ?>/nakes/pemeriksaan_list.php" class="btn btn-outline-secondary btn-sm">
              Reset
            </a>
          </div>
        </form>
      </div>
    </div>

    <!-- Tabel Riwayat Pemeriksaan -->
    <div class="card card-soft">
      <div class="card-body">
        <?php if (empty($pemeriksaanList)): ?>
          <div class="text-center py-5">
            <i class="bi bi-clipboard2-x fs-1 text-muted mb-3"></i>
            <h6 class="mb-1">Belum ada data pemeriksaan</h6>
            <p class="text-muted small mb-3">
              Lakukan pemeriksaan pertama lalu input data melalui menu Input Pemeriksaan.
            </p>
            <a href="<?= BASE_URL ?>/nakes/pemeriksaan_input.php" class="btn btn-primary btn-sm">
              <i class="bi bi-clipboard2-plus me-1"></i> Input Pemeriksaan Baru
            </a>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th style="width:5%;">#</th>
                  <th>Tanggal</th>
                  <th>Balita</th>
                  <th>Umur (bln)</th>
                  <th>BB (kg)</th>
                  <th>TB (cm)</th>
                  <th>Status Gizi</th>
                  <th>Petugas</th>
                  <th style="width:14%;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; ?>
                <?php foreach ($pemeriksaanList as $p): ?>
                  <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($p['tanggal']); ?></td>
                    <td><?= htmlspecialchars($p['nama_balita']); ?></td>
                    <td><?= (int)$p['umur_bulan']; ?></td>
                    <td><?= htmlspecialchars($p['bb']); ?></td>
                    <td><?= htmlspecialchars($p['tb']); ?></td>
                    <td>
                      <span class="badge badge-status <?= badgeClassNakes($p['status_gizi']); ?>">
                        <?= htmlspecialchars($p['status_gizi']); ?>
                      </span>
                    </td>
                    <td><?= htmlspecialchars($p['nakes']); ?></td>
                    <td>
                      <div class="btn-group btn-group-sm" role="group">
                        <a
                          href="<?= BASE_URL ?>/nakes/pemeriksaan_detail.php?id=<?= (int)$p['id'] ?>"
                          class="btn btn-outline-secondary"
                          title="Detail"
                        >
                          <i class="bi bi-eye"></i>
                        </a>
                        <a
                          href="#"
                          class="btn btn-outline-secondary"
                          title="Cetak"
                        >
                          <i class="bi bi-printer"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>
<!-- ========= END MAIN CONTENT ========= -->


<!-- =============== FOOTER =============== -->
<footer>
  <div class="container-fluid px-4 px-md-5 py-3">
    <div class="d-md-flex justify-content-between align-items-center">
      <div class="mb-2 mb-md-0">
        &copy; <?= date('Y') ?> <strong>GiziBalita</strong>. Sistem Monitoring Gizi Balita.
      </div>
      <div class="text-md-end">
        <span class="me-2">Gunakan riwayat ini untuk memantau tren gizi dan melakukan tindak lanjut.</span>
      </div>
    </div>
  </div>
</footer>
<!-- ========= END FOOTER ========= -->


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
