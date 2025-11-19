<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_ortu();

/**
 * List artikel edukasi untuk ORANG TUA
 * - hanya menampilkan status = 'Terbit'
 * - bisa filter kata kunci & kategori
 */

$q        = trim($_GET['q'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');

// build query
$sql = "
  SELECT a.id,
         a.judul,
         a.kategori,
         a.created_at,
         a.konten,
         u.name AS penulis
  FROM artikels a
  LEFT JOIN users u ON u.id = a.penulis_id
  WHERE a.status = 'Terbit'
";
$params = [];
$types  = '';

if ($q !== '') {
    $sql      .= " AND a.judul LIKE ?";
    $params[] = '%'.$q.'%';
    $types    .= 's';
}

if ($kategori !== '') {
    $sql      .= " AND a.kategori = ?";
    $params[] = $kategori;
    $types    .= 's';
}

$sql .= " ORDER BY a.created_at DESC";

$stmt = $mysqli->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

$artikels = [];
while ($row = $res->fetch_assoc()) {
    // buat snippet singkat dari konten
    $konten = strip_tags($row['konten'] ?? '');
    if (mb_strlen($konten) > 160) {
        $konten = mb_substr($konten, 0, 160) . '...';
    }
    $row['snippet'] = $konten;
    $artikels[] = $row;
}
$stmt->close();

// opsi kategori sama dengan sisi nakes
$kategoriOpt = [
    'Gizi Buruk',
    'Gizi Kurang',
    'Gizi Baik',
    'Risiko Gizi Lebih',
    'Gizi Lebih',
    'Obesitas',
    'Stunting',
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Artikel Edukasi Gizi - Orang Tua</title>
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
      background: radial-gradient(circle at top left, #e0f7ec 0, #ffffff 45%, #fdfdfd 100%);
    }

    .navbar-ortu {
      background: #ffffff;
      box-shadow: 0 4px 18px rgba(15, 157, 88, 0.12);
    }
    .navbar-ortu .navbar-brand {
      color: var(--brand-dark) !important;
    }
    .navbar-ortu .nav-link {
      color: #555 !important;
      font-size: .92rem;
    }
    .navbar-ortu .nav-link.active {
      color: var(--brand-main) !important;
      font-weight: 600;
    }

    .page-wrapper {
      flex: 1 0 auto;
      padding: 28px 0 40px;
    }

    .card-article {
      border: none;
      border-radius: 1.1rem;
      box-shadow: 0 14px 32px rgba(0,0,0,0.06);
      background: #ffffff;
      transition: transform .18s ease, box-shadow .18s ease;
    }
    .card-article:hover {
      transform: translateY(-3px);
      box-shadow: 0 18px 40px rgba(0,0,0,0.08);
    }

    .badge-kategori {
      background: var(--brand-soft);
      color: var(--brand-dark);
      border-radius: 999px;
      font-size: .75rem;
      padding: .25rem .7rem;
    }

    footer {
      flex-shrink: 0;
      background: linear-gradient(120deg, #0b7542, #0b3a60);
      color: #e9fdf2;
      font-size: .85rem;
    }
  </style>
</head>
<body>

<!-- NAVBAR ORANG TUA -->
<nav class="navbar navbar-expand-lg navbar-ortu">
  <div class="container-fluid px-4 px-md-5">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= BASE_URL ?>/ortu/dashboard.php">
      <i class="bi bi-heart-fill text-success me-2"></i>
      <span>GiziBalita | Orang Tua</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarOrtu">
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
          <a class="nav-link active" href="<?= BASE_URL ?>/ortu/artikel_list.php">Artikel</a>
        </li>
        <li class="nav-item dropdown ms-lg-3">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;">
              <i class="bi bi-person-heart text-success"></i>
            </div>
            <span class="d-none d-sm-inline">
              <?= htmlspecialchars($_SESSION['name'] ?? 'Orang Tua'); ?>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/ortu/profile.php"><i class="bi bi-person-circle me-2"></i> Profil</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
<!-- END NAVBAR -->

<div class="page-wrapper">
  <div class="container-fluid px-4 px-md-5">

    <!-- Header & Filter -->
    <div class="row mb-3">
      <div class="col-12 col-lg-7">
        <h4 class="mb-1">Artikel Edukasi Gizi Balita</h4>
        <p class="text-muted mb-0">
          Kumpulan artikel untuk membantu Ayah dan Bunda memahami kebutuhan gizi dan tumbuh kembang balita.
        </p>
      </div>
    </div>

    <div class="card card-article mb-4">
      <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
          <div class="col-md-6">
            <label class="form-label small text-muted mb-1">Cari Artikel</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input
                type="text"
                name="q"
                class="form-control"
                placeholder="Judul atau kata kunci..."
                value="<?= htmlspecialchars($q) ?>"
              >
            </div>
          </div>

          <div class="col-md-4">
            <label class="form-label small text-muted mb-1">Kategori</label>
            <select name="kategori" class="form-select form-select-sm">
              <option value="">Semua</option>
              <?php foreach ($kategoriOpt as $k): ?>
                <option value="<?= htmlspecialchars($k) ?>" <?= $kategori === $k ? 'selected' : '' ?>>
                  <?= htmlspecialchars($k) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-2 text-md-end">
            <button type="submit" class="btn btn-success btn-sm me-1">
              <i class="bi bi-funnel me-1"></i> Filter
            </button>
            <a href="<?= BASE_URL ?>/ortu/artikel_list.php" class="btn btn-outline-secondary btn-sm">
              Reset
            </a>
          </div>
        </form>
      </div>
    </div>

    <!-- List Artikel -->
    <?php if (empty($artikels)): ?>
      <div class="text-center py-5">
        <i class="bi bi-journal-x fs-1 text-muted mb-3"></i>
        <h6 class="mb-1">Belum ada artikel tersedia</h6>
        <p class="text-muted small mb-0">
          Artikel edukasi akan muncul di sini setelah tenaga kesehatan menerbitkannya.
        </p>
      </div>
    <?php else: ?>
      <div class="row g-3 g-md-4">
        <?php foreach ($artikels as $a): ?>
          <div class="col-md-6 col-xl-4">
            <div class="card card-article h-100">
              <div class="card-body d-flex flex-column">

                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="badge badge-kategori">
                    <?= htmlspecialchars($a['kategori']); ?>
                  </span>
                  <small class="text-muted">
                    <i class="bi bi-calendar-event me-1"></i>
                    <?= htmlspecialchars(date('d M Y', strtotime($a['created_at']))); ?>
                  </small>
                </div>

                <h6 class="fw-semibold mb-2">
                  <?= htmlspecialchars($a['judul']); ?>
                </h6>

                <p class="text-muted small flex-grow-1 mb-3">
                  <?= htmlspecialchars($a['snippet']); ?>
                </p>

                <div class="d-flex justify-content-between align-items-center mt-auto">
                  <small class="text-muted small">
                    <i class="bi bi-person-heart me-1 text-success"></i>
                    <?= htmlspecialchars($a['penulis'] ?? 'Tenaga Kesehatan'); ?>
                  </small>
                  <a
                    href="<?= BASE_URL ?>/ortu/artikel_baca.php?id=<?= (int)$a['id']; ?>"
                    class="btn btn-sm btn-outline-success"
                  >
                    Baca Artikel <i class="bi bi-arrow-right-short"></i>
                  </a>
                </div>

              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</div>

<footer class="py-3 mt-4">
  <div class="container-fluid px-4 px-md-5">
    <div class="d-md-flex justify-content-between align-items-center">
      <div class="mb-2 mb-md-0">
        &copy; <?= date('Y') ?> <strong>GiziBalita</strong>. Edukasi gizi untuk mendukung tumbuh kembang balita.
      </div>
      <div class="text-md-end small">
        Informasi di artikel bersifat edukatif, bukan pengganti konsultasi langsung dengan tenaga kesehatan.
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
