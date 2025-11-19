<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_nakes();

// TODO: Nanti ganti ini dengan SELECT dari tabel `balitas`
$balitaList = [
    ['id' => 1, 'nama' => 'Aisyah Putri',       'ortu' => 'Siti Rahma'],
    ['id' => 2, 'nama' => 'Raka Dwi Pratama',   'ortu' => 'Budi Santoso'],
    ['id' => 3, 'nama' => 'Nadia Salsabila',    'ortu' => 'Dina Lestari'],
];

// ambil balita_id dari URL jika ada (?balita_id=1)
$selectedBalitaId = isset($_GET['balita_id']) ? (int)$_GET['balita_id'] : 0;

// placeholder pesan (nanti bisa diisi dari hasil proses POST)
$error   = $error   ?? '';
$success = $success ?? '';

// placeholder hasil prediksi dari API ML (nanti diisi setelah proses)
$hasilPrediksi = $hasilPrediksi ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Input Pemeriksaan Gizi - Gizi Balita (Nakes)</title>
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
          <a class="nav-link active" href="<?= BASE_URL ?>/nakes/pemeriksaan_input.php">Input Pemeriksaan</a>
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

    <!-- Header -->
    <div class="row mb-3">
      <div class="col-12">
        <h4 class="mb-1">Input Pemeriksaan Status Gizi</h4>
        <p class="text-muted mb-0">
          Masukkan data antropometri balita, lalu sistem akan mengirim ke model ML untuk memprediksi status gizi.
        </p>
      </div>
    </div>

    <!-- Card Form -->
    <div class="row justify-content-center">
      <div class="col-lg-8 col-xl-7">
        <div class="card card-soft">
          <div class="card-body">

            <!-- Pesan error / success -->
            <?php if (!empty($error)): ?>
              <div class="alert alert-danger py-2 small">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <?= htmlspecialchars($error) ?>
              </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
              <div class="alert alert-success py-2 small">
                <i class="bi bi-check-circle me-1"></i>
                <?= htmlspecialchars($success) ?>
              </div>
            <?php endif; ?>

            <form method="post" autocomplete="off">
              <!-- PILIH BALITA -->
              <div class="mb-3">
                <label class="form-label small text-muted">Pilih Balita</label>
                <select name="balita_id" class="form-select form-select-sm" required>
                  <option value="">-- Pilih Balita --</option>
                  <?php foreach ($balitaList as $b): ?>
                    <option
                      value="<?= (int)$b['id']; ?>"
                      <?= $selectedBalitaId === (int)$b['id'] ? 'selected' : ''; ?>
                    >
                      <?= htmlspecialchars($b['nama']) ?> — (Ortu: <?= htmlspecialchars($b['ortu']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- TANGGAL PERIKSA -->
              <div class="mb-3">
                <label class="form-label small text-muted">Tanggal Pemeriksaan</label>
                <input
                  type="date"
                  name="tanggal"
                  class="form-control form-control-sm"
                  value="<?= date('Y-m-d'); ?>"
                  required
                >
              </div>

              <div class="row g-2">
                <!-- UMUR BULAN -->
                <div class="col-md-4">
                  <label class="form-label small text-muted">Umur (bulan)</label>
                  <input
                    type="number"
                    name="umur_bulan"
                    class="form-control form-control-sm"
                    min="0"
                    max="60"
                    step="1"
                    required
                  >
                </div>

                <!-- BB -->
                <div class="col-md-4">
                  <label class="form-label small text-muted">Berat Badan (kg)</label>
                  <input
                    type="number"
                    name="berat_badan"
                    class="form-control form-control-sm"
                    min="0"
                    max="40"
                    step="0.1"
                    required
                  >
                </div>

                <!-- TB -->
                <div class="col-md-4">
                  <label class="form-label small text-muted">Tinggi/Panjang Badan (cm)</label>
                  <input
                    type="number"
                    name="tinggi_badan"
                    class="form-control form-control-sm"
                    min="0"
                    max="120"
                    step="0.1"
                    required
                  >
                </div>
              </div>

              <div class="row g-2 mt-2">
                <!-- JENIS KELAMIN -->
                <div class="col-md-6">
                  <label class="form-label small text-muted d-block">Jenis Kelamin</label>
                  <div class="form-check form-check-inline">
                    <input
                      class="form-check-input"
                      type="radio"
                      name="jenis_kelamin"
                      id="jkL"
                      value="L"
                      required
                    >
                    <label class="form-check-label small" for="jkL">Laki-laki</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input
                      class="form-check-input"
                      type="radio"
                      name="jenis_kelamin"
                      id="jkP"
                      value="P"
                      required
                    >
                    <label class="form-check-label small" for="jkP">Perempuan</label>
                  </div>
                </div>

                <!-- LINGKAR LENGAN -->
                <div class="col-md-6">
                  <label class="form-label small text-muted">
                    Lingkar Lengan Atas (cm) <span class="text-muted">(opsional)</span>
                  </label>
                  <input
                    type="number"
                    name="lingkar_lengan"
                    class="form-control form-control-sm"
                    min="0"
                    max="30"
                    step="0.1"
                    placeholder="Boleh dikosongkan"
                  >
                </div>
              </div>

              <hr class="my-3">

              <div class="d-flex justify-content-between align-items-center">
                <a href="<?= BASE_URL ?>/nakes/pemeriksaan_list.php" class="btn btn-link btn-sm text-muted text-decoration-none">
                  <i class="bi bi-arrow-left"></i> Kembali ke riwayat
                </a>

                <div class="d-flex gap-2">
                  <button type="reset" class="btn btn-outline-secondary btn-sm">
                    Reset
                  </button>
                  <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-cpu me-1"></i>
                    Simpan & Prediksi Status Gizi
                  </button>
                </div>
              </div>
            </form>

            <?php if (!empty($hasilPrediksi)): ?>
              <hr class="mt-4">
              <div class="alert alert-info small mb-0">
                <i class="bi bi-activity me-1"></i>
                Hasil Prediksi: <strong><?= htmlspecialchars($hasilPrediksi); ?></strong>
              </div>
            <?php endif; ?>

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
        <span class="me-2">Pastikan pengukuran dilakukan dengan alat yang terkalibrasi.</span>
      </div>
    </div>
  </div>
</footer>
<!-- ========= END FOOTER ========= -->


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
