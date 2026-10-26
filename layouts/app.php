<?php
// layouts/app.php
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= e($pageTitle) ?> | Donor Darah Kampus</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini layout-fixed">

<div class="wrapper">

    <?php require __DIR__ . '/navbar.php'; ?>

    <?php require __DIR__ . '/sidebar.php'; ?>

    <div class="content-wrapper">

        <section class="content-header">
            <div class="container-fluid">
                <h1><?= e($pageTitle) ?></h1>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <?php require $contentFile; ?>
            </div>
        </section>

    </div>

    <?php require __DIR__ . '/footer.php'; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>
</html>