<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_nakes();

// TODO: Nanti ini diambil dari database
$totalBalita        = 24;
$totalPemeriksaan   = 120;
$totalArtikel       = 8;
$persenGiziBaik     = 72;

// Dummy riwayat terakhir
$latest = [
    [
        'tanggal'      => '2025-03-10',
        'nama_balita'  => 'Aisyah Putri',
        'umur_bulan'   => 24,
        'bb'           => 11.5,
        'tb'           => 85,
        'status_gizi'  => 'Gizi Baik',
    ],
    [
        'tanggal'      => '2025-03-09',
        'nama_balita'  => 'Raka Dwi',
        'umur_bulan'   => 20,
        'bb'           => 10.2,
        'tb'           => 81,
        'status_gizi'  => 'Gizi Kurang',
    ],
    [
        'tanggal'      => '2025-03-08',
        'nama_balita'  => 'Nadia Salsabila',
        'umur_bulan'   => 18,
        'bb'           => 9.6,
        'tb'           => 79,
        'status_gizi'  => 'Risiko Gizi Lebih',
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
  <title>Dashboard Tenaga Kesehatan - Gizi Balita</title>
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
          <a class="nav-link active" href="<?= BASE_URL ?>/nakes/dashboard.php">Dashboard</a>
        </li>

        <li class="nav-item mx-lg-1">
          <a class="nav-link" href="<?= BASE_URL ?>/nakes/balita_list.php">Data Balita</a>
        </li>

        <li class="nav-item mx-lg-1">
          <a class="nav-link" href="<?= BASE_URL ?>/nakes/pemeriksaan_input.php">Input Pemeriksaan</a>
        </li>

        <li class="nav-item mx-lg-1">
          <a class="nav-link" href="<?= BASE_URL ?>/nakes/pemeriksaan_list.php">Riwayat Pemeriksaan</a>
        </li>

        <li class="nav-item mx-lg-1">
          <a class="nav-link" href="<?= BASE_URL ?>/nakes/artikel_manage.php">Artikel Edukasi</a>
        </li>

        <li class="nav-item dropdown ms-lg-3">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;">
              <i class="bi bi-person-badge-fill"></i>
            </div>
            <span class="d-none d-sm-inline">
              <?= htmlspecialchars($_SESSION['name'] ?? 'Tenaga Kesehatan'); ?>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end mt-2">
            <li>
              <a class="dropdown-item" href="<?=  BASE_URL ?>/nakes/profile.php">
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

    <!-- Header & Quick Info -->
    <div class="row mb-4">
      <div class="col-12 d-md-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-1">Dashboard Tenaga Kesehatan</h4>
          <p class="text-muted mb-0">
            Selamat datang, <?= htmlspecialchars($_SESSION['name'] ?? '') ?> 👋  
            Kelola data balita, pemeriksaan gizi, dan artikel edukasi di sini.
          </p>
        </div>
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3 col-sm-6">
        <div class="card card-soft">
          <div class="card-body">
            <div class="stat-label">Total Balita</div>
            <div class="d-flex align-items-center justify-content-between">
              <div class="stat-value"><?= (int)$totalBalita; ?></div>
              <div class="text-muted"><i class="bi bi-people-fill fs-4 text-primary"></i></div>
            </div>
            <small class="text-muted">Jumlah balita yang terdaftar di sistem.</small>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6">
        <div class="card card-soft">
          <div class="card-body">
            <div class="stat-label">Total Pemeriksaan</div>
            <div class="d-flex align-items-center justify-content-between">
              <div class="stat-value"><?= (int)$totalPemeriksaan; ?></div>
              <div class="text-muted"><i class="bi bi-clipboard2-pulse fs-4 text-success"></i></div>
            </div>
            <small class="text-muted">Seluruh pemeriksaan yang pernah dicatat.</small>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6">
        <div class="card card-soft">
          <div class="card-body">
            <div class="stat-label">Artikel Edukasi</div>
            <div class="d-flex align-items-center justify-content-between">
              <div class="stat-value"><?= (int)$totalArtikel; ?></div>
              <div class="text-muted"><i class="bi bi-journal-text fs-4 text-warning"></i></div>
            </div>
            <small class="text-muted">Artikel yang dapat dibaca oleh orang tua.</small>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6">
        <div class="card card-soft">
          <div class="card-body">
            <div class="stat-label">% Gizi Baik</div>
            <div class="d-flex align-items-center justify-content-between">
              <div class="stat-value"><?= (int)$persenGiziBaik; ?>%</div>
              <div class="text-muted"><i class="bi bi-graph-up-arrow fs-4 text-success"></i></div>
            </div>
            <small class="text-muted">Perkiraan proporsi balita dengan gizi baik.</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Riwayat Pemeriksaan Terakhir -->
    <div class="row g-3">
      <div class="col-lg-8">
        <div class="card card-soft">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="mb-0">Pemeriksaan Terakhir</h6>
              <a href="<?= BASE_URL ?>/nakes/pemeriksaan_list.php" class="small text-decoration-none">
                Lihat semua <i class="bi bi-arrow-right-short"></i>
              </a>
            </div>

            <?php if (empty($latest)): ?>
              <p class="text-muted small mb-0">
                Belum ada data pemeriksaan.
              </p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Tanggal</th>
                      <th>Balita</th>
                      <th>Umur (bln)</th>
                      <th>BB (kg)</th>
                      <th>TB (cm)</th>
                      <th>Status Gizi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($latest as $row): ?>
                      <tr>
                        <td><?= htmlspecialchars($row['tanggal']); ?></td>
                        <td><?= htmlspecialchars($row['nama_balita']); ?></td>
                        <td><?= (int)$row['umur_bulan']; ?></td>
                        <td><?= htmlspecialchars($row['bb']); ?></td>
                        <td><?= htmlspecialchars($row['tb']); ?></td>
                        <td>
                          <span class="badge badge-status <?= badgeClassNakes($row['status_gizi']); ?>">
                            <?= htmlspecialchars($row['status_gizi']); ?>
                          </span>
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

      <!-- Quick Actions -->
      <div class="col-lg-4">
        <div class="card card-soft mb-3">
          <div class="card-body">
            <h6 class="mb-3">Aksi Cepat</h6>
            <div class="d-grid gap-2">
              <a href="<?= BASE_URL ?>/nakes/pemeriksaan_input.php" class="btn btn-primary btn-sm">
                <i class="bi bi-clipboard2-plus me-1"></i> Input Pemeriksaan Baru
              </a>
              <a href="<?= BASE_URL ?>/nakes/balita_add.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-person-plus me-1"></i> Tambah Data Balita
              </a>
              <a href="<?= BASE_URL ?>/nakes/artikel_manage.php" class="btn btn-outline-success btn-sm">
                <i class="bi bi-journal-plus me-1"></i> Buat Artikel Edukasi
              </a>
            </div>
          </div>
        </div>

        <div class="card card-soft">
          <div class="card-body">
            <h6 class="mb-2">Catatan</h6>
            <p class="small text-muted mb-0">
              Gunakan dashboard ini untuk memantau tren gizi balita di wilayah Anda dan memberikan intervensi lebih cepat jika ditemukan kasus gizi kurang, gizi buruk, atau stunting.
            </p>
          </div>
        </div>
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
        <span class="me-2">Peran Anda sangat penting dalam mencegah stunting dan masalah gizi lainnya.</span>
      </div>
    </div>
  </div>
</footer>
<!-- ========= END FOOTER ========= -->


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
