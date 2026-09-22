<?php

require_once __DIR__ .
    '/app/config/config.php';

require_once __DIR__ .
    '/app/config/theme.php';

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width,
    initial-scale=1.0"
>

<meta
    name="theme-color"
    content="<?= PRIMARY_COLOR ?>"
>

<title>
    <?= APP_NAME ?>
</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="<?= BASE_URL ?>assets/css/style.css"
>

</head>

<body>

<div class="container py-5">

<div class="text-center">

    <h1 class="fw-bold">

        <?= APP_NAME ?>

    </h1>

    <p class="text-muted">

        Personal Finance Management System

    </p>

    <div class="mt-4">

        <span class="badge bg-success">

            Version <?= APP_VERSION ?>

        </span>

    </div>

    <!-- Login Button -->
    <div class="mt-4">

        <a
            href="<?= BASE_URL ?>auth/login.php"
            class="btn btn-primary px-4"
        >
            Login
        </a>

    </div>

</div>

</div>

</body>

</html>
