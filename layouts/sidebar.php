<aside class="main-sidebar sidebar-dark-primary elevation-4">

    <!-- Logo aplikasi -->
    <a href="index.php?page=dashboard" class="brand-link">
        <i class="fas fa-heartbeat ml-3 mr-2"></i>
        <span class="brand-text font-weight-light">
            Donor Darah
        </span>
    </a>

    <div class="sidebar">

        <!-- Informasi pengguna -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <i class="fas fa-user-circle fa-2x text-light"></i>
            </div>

            <div class="info">
                <span class="d-block text-white">
                    Panel Petugas
                </span>
            </div>
        </div>

        <!-- Menu aplikasi -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column"
                data-widget="treeview"
                role="menu">

                <li class="nav-item">
                    <a href="index.php?page=dashboard"
                       class="nav-link">

                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-header">DATA PENDONOR</li>

                <li class="nav-item">
                    <a href="index.php?page=pendonor-create"
                       class="nav-link">

                        <i class="nav-icon fas fa-user-plus"></i>
                        <p>Tambah Pendonor</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="index.php?page=pendonor-index"
                       class="nav-link">

                        <i class="nav-icon fas fa-users"></i>
                        <p>Daftar Pendonor</p>
                    </a>
                </li>

                <!-- <li class="nav-header">KEGIATAN DONOR</li>

                <li class="nav-item">
                    <a href="index.php?page=kegiatan-index"
                       class="nav-link">

                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>Data Kegiatan</p>
                    </a>
                </li> -->

                <li class="nav-header">PEMERIKSAAN</li>

                <li class="nav-item">
                    <a href="index.php?page=pemeriksaan-index"
                       class="nav-link">

                        <i class="nav-icon fas fa-stethoscope"></i>
                        <p>Pemeriksaan Donor</p>
                    </a>
                </li>

            </ul>
        </nav>

    </div>
</aside>