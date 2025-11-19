<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/config.php';
require_ortu();

// TODO: ganti dummy data ini dengan data dari tabel `pemeriksaans` untuk balita milik orang tua
$umurBulan   = [12, 14, 16, 18, 20, 22, 24];
$beratBadan  = [8.5, 8.9, 9.3, 9.8, 10.2, 10.8, 11.5];
$tinggiBadan = [72, 74, 76, 78, 80, 83, 85];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Grafik Perkembangan - Gizi Balita</title>
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
      background: radial-gradient(circle at top left, #f1fff7 0, #f8fffb 40%, #ffffff 100%);
    }

    /* NAVBAR */
    .navbar-custom {
      background: linear-gradient(120deg, var(--brand-main), #34c785);
    }
    .navbar-custom .navbar-brand,
    .navbar-custom .nav-link,
    .navbar-custom .dropdown-item {
      color: #fdfdfd !important;
    }
    .navbar-custom .nav-link {
      opacity: .85;
    }
    .navbar-custom .nav-link:hover {
      opacity: 1;
    }
    .navbar-custom .nav-link.active {
      font-weight: 600;
      border-bottom: 2px solid #ffffffcc;
    }
    .navbar-custom .dropdown-menu {
      background: #ffffff;
      border-radius: .75rem;
      border: none;
      box-shadow: 0 12px 30px rgba(0,0,0,0.1);
      padding-top: .5rem;
      padding-bottom: .5rem;
    }
    .navbar-custom .dropdown-item {
      color: #444 !important;
    }
    .navbar-custom .dropdown-item.text-danger {
      color: #dc3545 !important;
    }

    /* MAIN */
    .page-wrapper {
      flex: 1 0 auto;
      padding: 32px 0 40px;
    }
    .card-soft {
      border: none;
      border-radius: 1.2rem;
      box-shadow: 0 16px 35px rgba(0,0,0,0.06);
      background: #ffffff;
    }

    /* FOOTER */
    footer {
      flex-shrink: 0;
      background: linear-gradient(120deg, #0b4125, #0b7542);
      color: #e2f6ea;
      font-size: .85rem;
    }
    footer a {
      color: #b8f3d1;
      text-decoration: none;
    }
    footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<!-- =============== NAVBAR =============== -->
<nav class="navbar navbar-expand-lg navbar-custom shadow-sm">
  <div class="container-fluid px-4 px-md-5">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= BASE_URL ?>/ortu/dashboard.php">
      <i class="bi bi-heart-pulse-fill me-2"></i>
      <span>GiziBalita | Orang Tua</span>
    </a>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarOrtu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarOrtu">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">

        <li class="nav-item mx-lg-1">
          <a class="nav-link" href="<?= BASE_URL ?>/ortu/dashboard.php">
            Beranda
          </a>
        </li>

        <li class="nav-item mx-lg-1">
          <a class="nav-link" href="<?= BASE_URL ?>/ortu/pemeriksaan_riwayat.php">
            Riwayat Gizi
          </a>
        </li>

        <li class="nav-item mx-lg-1">
          <a class="nav-link active" href="<?= BASE_URL ?>/ortu/grafik_perkembangan.php">
            Grafik Perkembangan
          </a>
        </li>

        <li class="nav-item mx-lg-1">
          <a class="nav-link" href="<?= BASE_URL ?>/ortu/artikel_list.php">
            Artikel
          </a>
        </li>

        <li class="nav-item dropdown ms-lg-3">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;">
              <i class="bi bi-person-fill"></i>
            </div>
            <span class="d-none d-sm-inline">
              <?= htmlspecialchars($_SESSION['name'] ?? 'Orang Tua'); ?>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end mt-2">
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/ortu/profile.php">
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
<!-- ============ END NAVBAR ============ -->


<!-- =============== MAIN CONTENT =============== -->
<div class="page-wrapper">
  <div class="container-fluid px-4 px-md-5">

    <div class="row mb-3">
      <div class="col-12">
        <h4 class="mb-1">Grafik Perkembangan Balita</h4>
        <p class="text-muted mb-0">
          Pantau perubahan berat badan dan tinggi badan balita berdasarkan umur dalam bulan.
        </p>
      </div>
    </div>

    <div class="row g-4">
      <!-- Grafik BB/U -->
      <div class="col-md-6">
        <div class="card card-soft h-100">
          <div class="card-body">
            <h6 class="text-uppercase text-muted mb-3">Berat Badan per Umur (BB/U)</h6>
            <canvas id="chartBBU" height="220"></canvas>
            <p class="small text-muted mt-3 mb-0">
              Garis ini menunjukkan perkembangan berat badan balita dari waktu ke waktu. 
              Konsultasikan ke tenaga kesehatan bila berat badan tidak bertambah sesuai usia.
            </p>
          </div>
        </div>
      </div>

      <!-- Grafik TB/U -->
      <div class="col-md-6">
        <div class="card card-soft h-100">
          <div class="card-body">
            <h6 class="text-uppercase text-muted mb-3">Tinggi Badan per Umur (TB/U)</h6>
            <canvas id="chartTBU" height="220"></canvas>
            <p class="small text-muted mt-3 mb-0">
              Tinggi badan membantu memantau kemungkinan stunting. 
              Pastikan balita mendapatkan asupan gizi dan stimulasi yang cukup.
            </p>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<!-- ============ END MAIN CONTENT ============ -->


<!-- =============== FOOTER =============== -->
<footer>
  <div class="container-fluid px-4 px-md-5 py-3">
    <div class="d-md-flex justify-content-between align-items-center">
      <div class="mb-2 mb-md-0">
        &copy; <?= date('Y') ?> <strong>GiziBalita</strong>. Semua hak dilindungi.
      </div>
      <div class="text-md-end">
        <span class="me-2">Gunakan grafik ini untuk memantau pertumbuhan balita secara berkala.</span>
      </div>
    </div>
  </div>
</footer>
<!-- ============ END FOOTER ============ -->


<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  const umurLabels   = <?= json_encode($umurBulan) ?>;
  const dataBB       = <?= json_encode($beratBadan) ?>;
  const dataTB       = <?= json_encode($tinggiBadan) ?>;

  const ctxBB = document.getElementById('chartBBU').getContext('2d');
  new Chart(ctxBB, {
    type: 'line',
    data: {
      labels: umurLabels,
      datasets: [{
        label: 'Berat Badan (kg)',
        data: dataBB,
        borderWidth: 2,
        tension: 0.3,
        pointRadius: 3
      }]
    },
    options: {
      plugins: { legend: { display: true } },
      scales: {
        x: {
          title: { display: true, text: 'Umur (bulan)' }
        },
        y: {
          title: { display: true, text: 'Berat Badan (kg)' }
        }
      }
    }
  });

  const ctxTB = document.getElementById('chartTBU').getContext('2d');
  new Chart(ctxTB, {
    type: 'line',
    data: {
      labels: umurLabels,
      datasets: [{
        label: 'Tinggi Badan (cm)',
        data: dataTB,
        borderWidth: 2,
        tension: 0.3,
        pointRadius: 3
      }]
    },
    options: {
      plugins: { legend: { display: true } },
      scales: {
        x: {
          title: { display: true, text: 'Umur (bulan)' }
        },
        y: {
          title: { display: true, text: 'Tinggi Badan (cm)' }
        }
      }
    }
  });
</script>

</body>
</html>
