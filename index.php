<?php

require_once __DIR__ . '/includes/functions.php';

/*
|--------------------------------------------------------------------------
| 1. ROUTE AJAX
|--------------------------------------------------------------------------
| Harus diproses sebelum layout menghasilkan HTML.
| Controller mengembalikan respons JSON untuk permintaan AJAX.
*/
if (($_GET['action'] ?? '') === 'pemeriksaan-store') {
    require __DIR__ . '/actions/pemeriksaan_store.php';
    exit;
}

/*
|--------------------------------------------------------------------------
| 2. DAFTAR HALAMAN
|--------------------------------------------------------------------------
*/
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

/*
|--------------------------------------------------------------------------
| 3. TENTUKAN HALAMAN YANG DIBUKA
|--------------------------------------------------------------------------
*/
$page = $_GET['page'] ?? 'dashboard';

if (!isset($pages[$page])) {
    http_response_code(404);
    $pageTitle = 'Halaman Tidak Ditemukan';
    $contentFile = __DIR__ . '/pages/404.php';
} else {
    $pageTitle = $pages[$page]['title'];
    $contentFile = __DIR__ . '/pages/' . $pages[$page]['file'];
}

/*
|--------------------------------------------------------------------------
| 4. PROSES FORM POST BIASA
|--------------------------------------------------------------------------
| Proses penyimpanan pendonor sebelum layout menghasilkan HTML.
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($page === 'pendonor-create') {
        require __DIR__ . '/actions/pendonor_store.php';
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| 5. TAMPILKAN LAYOUT UTAMA
|--------------------------------------------------------------------------
*/
require __DIR__ . '/layouts/app.php';