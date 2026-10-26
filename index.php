<?php

require_once __DIR__ . '/includes/functions.php';

$pages = [
    'dashboard' => [
        'title' => 'Dashboard',
        'file' => 'dashboard.php',
    ],
    'pendonor-create' => [
        'title' => 'Tambah Data Pendonor',
        'file' => 'pendonor_create.php',
    ],
    'pendonor-index' => [
        'title' => 'Daftar Pendonor',
        'file' => 'pendonor_index.php',
    ],
    'kegiatan-index' => [
        'title' => 'Data Kegiatan Donor',
        'file' => 'kegiatan_index.php',
    ],
    'pemeriksaan-create' => [
        'title' => 'Pemeriksaan Donor',
        'file' => 'pemeriksaan_create.php',
    ],
];

$page = $_GET['page'] ?? 'dashboard';

if (!isset($pages[$page])) {
    http_response_code(404);
    $page = 'dashboard';
    $pageTitle = 'Halaman Tidak Ditemukan';
} else {
    $pageTitle = $pages[$page]['title'];
}

$contentFile = __DIR__ . '/pages/' . $pages[$page]['file'];

// Proses POST harus dilakukan sebelum layout menghasilkan HTML.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($page === 'pendonor-create') {
        require __DIR__ . '/actions/pendonor_store.php';
    }
}

require __DIR__ . '/layouts/app.php';