<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/config.php';
require_ortu();

// TODO: nanti ganti dummy data ini dengan SELECT dari tabel `artikels`
$artikels = [
    [
        'id'       => 1,
        'judul'    => 'Panduan Nutrisi Seimbang untuk Balita 1–3 Tahun',
        'ringkasan'=> 'Memahami kebutuhan gizi harian balita agar tumbuh optimal, termasuk sumber karbohidrat, protein, lemak sehat, buah, dan sayur.',
        'tgl'      => '2025-03-01',
        'kategori' => 'Gizi Baik',
    ],
    [
        'id'       => 2,
        'judul'    => 'Mencegah Stunting Melalui Pola Makan yang Tepat',
        'ringkasan'=> 'Stunting dapat dicegah dengan pemenuhan gizi sejak dini. Kenali jenis makanan yang mendukung tinggi badan anak.',
        'tgl'      => '2025-02-20',
        'kategori' => 'Gizi Kurang',
    ],
    [
        'id'       => 3,
        'judul'    => 'Mengatasi Berat Badan Berlebih pada Balita',
        'ringkasan'=> 'Berat badan berlebih perlu ditangani dengan bijak. Hindari diet ketat dan fokus pada pola makan seimbang.',
        'tgl'      => '2025-02-10',
        'kategori' => 'Gizi Lebih',
    ],
];

// Dummy kategori filter
$kategoriList = [
    ''                => 'Semua Status Gizi',
    'Gizi Buruk'      => 'Gizi Buruk',
    'Gizi Kurang'     => 'Gizi Kurang',
    'Gizi Baik'       => 'Gizi Baik',
    'Risiko Gizi Lebih' => 'Risiko Gizi Lebih',
    'Gizi Lebih'      => 'Gizi Lebih',
    'Obesitas'        => 'Obesitas',
];

// Ambil filter dari query string (sementara belum dipakai untuk filter beneran)
$q         = trim($_GET['q'] ?? '');
$kategoriQ = $_GET['kategori'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Artikel Edukasi Gizi - Gizi Balita</title>
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
    .article-card {
      border-radius: 1rem;
      border: none;
      box-shadow: 0 10px 25px rgba(0,0,0,0.04);
      height: 100%;
    }
    .article-tag {
      font-size: .75rem;
      padding: .2rem .6rem;
      border-radius: 999px;
      background: #f0f9f4;
      color: #256f46;
      border: 1px solid #ddf1e4;
    }
    .article-date {
      font-size: .8rem;
      color: #6c757d;
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
          <a class="nav-link" href="<?= BASE_URL ?>/ortu/grafik_perkembangan.php">
            Grafik Perkembangan
          </a>
        </li>

        <li class="nav-item mx-lg-1">
          <a class="nav-link active" href="<?= BASE_URL ?>/ortu/artikel_list.php">
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
        <h4 class="mb-1">Artikel Edukasi Gizi Balita</h4>
        <p class="text-muted mb-0">
          Baca artikel yang disusun oleh tenaga kesehatan untuk mendukung tumbuh kembang balita Anda.
        </p>
      </div>
    </div>

    <!-- Filter & Search -->
    <div class="card card-soft mb-4">
      <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
          <div class="col-md-5">
            <label class="form-label small text-muted mb-1">Cari Artikel</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input
                type="text"
                name="q"
                class="form-control"
                placeholder="Ketik judul atau kata kunci..."
                value="<?= htmlspecialchars($q) ?>"
              >
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label small text-muted mb-1">Filter Status Gizi</label>
            <select name="kategori" class="form-select">
              <?php foreach ($kategoriList as $key => $label): ?>
                <option value="<?= htmlspecialchars($key) ?>" <?= $kategoriQ === $key ? 'selected' : '' ?>>
                  <?= htmlspecialchars($label) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3 text-md-end">
            <button type="submit" class="btn btn-success btn-sm me-1">
              <i class="bi bi-funnel me-1"></i> Terapkan
            </button>
            <a href="<?= BASE_URL ?>/ortu/artikel_list.php" class="btn btn-outline-secondary btn-sm">
              Reset
            </a>
          </div>
        </form>
      </div>
    </div>

    <!-- Grid Artikel -->
    <div class="row g-3">
      <?php if (empty($artikels)): ?>
        <div class="col-12">
          <div class="card card-soft">
            <div class="card-body text-center py-5">
              <i class="bi bi-journal-x fs-1 text-muted mb-3"></i>
              <h6 class="mb-1">Belum ada artikel</h6>
              <p class="text-muted small mb-0">
                Artikel edukasi akan tampil di sini setelah tenaga kesehatan menambahkannya.
              </p>
            </div>
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($artikels as $a): ?>
          <div class="col-md-6 col-xl-4">
            <div class="card article-card">
              <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="article-date">
                    <i class="bi bi-calendar3 me-1"></i>
                    <?= htmlspecialchars($a['tgl']) ?>
                  </span>
                  <?php if (!empty($a['kategori'])): ?>
                    <span class="article-tag">
                      <?= htmlspecialchars($a['kategori']) ?>
                    </span>
                  <?php endif; ?>
                </div>

                <h6 class="card-title mb-2"><?= htmlspecialchars($a['judul']) ?></h6>

                <p class="text-muted small flex-grow-1 mb-3">
                  <?= htmlspecialchars($a['ringkasan']) ?>
                </p>

                <a href="<?= BASE_URL ?>/ortu/artikel_baca.php?id=<?= (int)$a['id'] ?>" class="btn btn-outline-success btn-sm align-self-start">
                  Baca Selengkapnya <i class="bi bi-arrow-right-short"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
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
        <span class="me-2">Artikel ini bersifat edukatif, tetap konsultasikan dengan tenaga kesehatan.</span>
      </div>
    </div>
  </div>
</footer>
<!-- ============ END FOOTER ============ -->


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
