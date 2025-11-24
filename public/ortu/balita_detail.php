<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_ortu();

/**
 * Ambil ID user ortu dari session.
 * Di login ortu sebaiknya set:
 *   $_SESSION['user_id'] = <id user>;
 *   $_SESSION['name']    = <nama ortu>;
 */
$ortu_id = (int)($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);

/* ==========================
   1. Ambil detail data balita
   ========================== */
$balita = null;
$umur_bulan = "-";

if ($ortu_id > 0) {
    try {
        $sql = "SELECT 
                    id,
                    nama_balita,
                    tanggal_lahir,
                    jenis_kelamin
                FROM balitas
                WHERE user_ortu_id = ?
                LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $ortu_id);
            $stmt->execute();
            $balita = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
    } catch (Throwable $e) {
        // optional: log error kalau mau
        // error_log('Error ambil detail balita: '.$e->getMessage());
    }
}

/* Hitung umur dalam bulan (konsisten dengan dashboard) */
if ($balita && !empty($balita['tanggal_lahir']) && $balita['tanggal_lahir'] !== '0000-00-00') {
    try {
        $lahir = new DateTime(trim($balita['tanggal_lahir']));
        $now   = new DateTime();

        if ($lahir <= $now) {
            $diff       = $now->diff($lahir);
            $umur_bulan = ($diff->y * 12) + $diff->m;
            if ($umur_bulan < 0) {
                $umur_bulan = 0;
            }
        } else {
            $umur_bulan = 0;
        }
    } catch (Throwable $e) {
        $umur_bulan = "-";
    }
}

/* Helper format tanggal sederhana (dd-mm-YYYY) */
function format_tanggal_id(?string $tanggal): string {
    if (!$tanggal || $tanggal === '0000-00-00') return '-';
    try {
        $dt = new DateTime($tanggal);
        return $dt->format('d-m-Y');
    } catch (Throwable $e) {
        return $tanggal;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Data Balita</title>
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

    /* MAIN CONTENT */
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
    .chip {
      display: inline-flex;
      align-items: center;
      border-radius: 999px;
      padding: .35rem .75rem;
      background-color: #f3faf6;
      font-size: .8rem;
      color: #3d6653;
      border: 1px solid #e0f2ea;
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

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-custom shadow-sm">
  <div class="container-fluid px-4 px-md-5">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= BASE_URL ?>/ortu/dashboard.php">
      <i class="bi bi-heart-pulse-fill me-2"></i>
      <span>GiziBalita</span>
    </a>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarOrtu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarOrtu">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">

        <li class="nav-item mx-lg-1">
          <a class="nav-link" href="<?= BASE_URL ?>/ortu/dashboard.php">Beranda</a>
        </li>

        <li class="nav-item mx-lg-1">
          <a class="nav-link" href="<?= BASE_URL ?>/ortu/pemeriksaan_riwayat.php">Riwayat Gizi</a>
        </li>

        <li class="nav-item mx-lg-1">
          <a class="nav-link" href="<?= BASE_URL ?>/ortu/grafik_perkembangan.php">Grafik Perkembangan</a>
        </li>

        <li class="nav-item mx-lg-1">
          <a class="nav-link" href="<?= BASE_URL ?>/ortu/artikel_list.php">Artikel</a>
        </li>

        <li class="nav-item dropdown ms-lg-3">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;">
              <i class="bi bi-person-fill"></i>
            </div>
            <span class="d-none d-sm-inline"><?= htmlspecialchars($_SESSION['name'] ?? 'Orang Tua'); ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end mt-2">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/ortu/profile.php"><i class="bi bi-person-circle me-2"></i> Profil</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
          </ul>
        </li>

      </ul>
    </div>
  </div>
</nav>

<!-- MAIN CONTENT -->
<div class="page-wrapper">
  <div class="container-fluid px-4 px-md-5">
    <div class="row g-4">

      <!-- Header kecil + tombol kembali -->
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0">Detail Data Balita</h5>
          <a href="<?= BASE_URL ?>/ortu/dashboard.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
          </a>
        </div>
        <p class="text-muted mb-3">
          Informasi detail tentang balita yang terdaftar pada akun ini.
        </p>
      </div>

      <!-- Card detail balita -->
      <div class="col-12 col-lg-8">
        <div class="card card-soft">
          <div class="card-body">
            <?php if ($balita): ?>
              <h4 class="mb-3"><?= htmlspecialchars($balita['nama_balita']) ?></h4>

              <div class="row mb-2">
                <div class="col-sm-4 text-muted">Nama Balita</div>
                <div class="col-sm-8"><?= htmlspecialchars($balita['nama_balita']) ?></div>
              </div>

              <div class="row mb-2">
                <div class="col-sm-4 text-muted">Tanggal Lahir</div>
                <div class="col-sm-8"><?= htmlspecialchars(format_tanggal_id($balita['tanggal_lahir'] ?? null)) ?></div>
              </div>

              <div class="row mb-2">
                <div class="col-sm-4 text-muted">Umur</div>
                <div class="col-sm-8">
                  <?= is_numeric($umur_bulan) ? $umur_bulan . ' bulan' : $umur_bulan; ?>
                </div>
              </div>

              <div class="row mb-2">
                <div class="col-sm-4 text-muted">Jenis Kelamin</div>
                <div class="col-sm-8">
                  <?= ($balita['jenis_kelamin'] ?? '') === 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                </div>
              </div>

              <hr class="my-3">

              <p class="small text-muted mb-1">
                Jika ada perbedaan data, silakan hubungi petugas kesehatan untuk pembaruan.
              </p>

            <?php else: ?>
              <div class="text-center py-5">
                <i class="bi bi-exclamation-circle fs-1 text-muted mb-3"></i>
                <h6 class="mb-1">Belum ada data balita</h6>
                <p class="text-muted small mb-3">
                  Data balita akan muncul setelah diinput oleh tenaga kesehatan atau admin puskesmas.
                </p>
                <a href="<?= BASE_URL ?>/ortu/dashboard.php" class="btn btn-success btn-sm">
                  <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Info tambahan / tips -->
      <div class="col-12 col-lg-4">
        <div class="card card-soft">
          <div class="card-body">
            <h6 class="text-uppercase text-muted mb-3">Tips</h6>
            <p class="small text-muted mb-2">
              Data balita digunakan sebagai dasar pemantauan status gizi dan tumbuh kembang.
            </p>
            <p class="small text-muted mb-0">
              Pastikan tanggal lahir dan jenis kelamin tercatat dengan benar agar perhitungan umur
              dan interpretasi status gizi sesuai.
            </p>
          </div>
        </div>
      </div>

    </div> <!-- row -->
  </div>
</div>

<!-- FOOTER -->
<footer>
  <div class="container-fluid px-4 px-md-5 py-3">
    <div class="d-md-flex justify-content-between align-items-center">
      <div>&copy; <?= date('Y') ?> <strong>GiziBalita</strong>. Semua hak dilindungi.</div>
      <div class="text-md-end">Dibuat untuk membantu orang tua memantau gizi balita.</div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
