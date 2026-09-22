<?php

require_once __DIR__ .
    '/../app/middleware/auth.php';

require_once __DIR__ .
    '/../app/config/theme.php';

require_once __DIR__ .
    '/../app/config/database.php';

require_once __DIR__ .
    '/../app/helpers/functions.php';


/*
|--------------------------------------------------------------------------
| Get Active Wallet Types
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        name,
        description

    FROM wallet_types

    WHERE status = 'Active'

    ORDER BY name ASC
");

$walletTypes =
    $stmt->fetchAll();


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
        Add Wallet -
        <?= e(APP_NAME) ?>
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>assets/css/style.css"
    >

</head>


<body>


<div
    class="container
    py-4"
>


    <!-- Header -->

    <div class="mb-4">


        <a
            href="<?= BASE_URL ?>wallets/"
            class="btn
            btn-outline-secondary
            btn-sm
            mb-3"
        >

            <i
                class="bi
                bi-arrow-left
                me-1"
            ></i>

            Back to Wallets

        </a>


        <h2
            class="fw-bold"
        >

            Add Wallet

        </h2>


        <p
            class="text-muted"
        >

            Add a bank account,
            e-wallet, cash, or other
            money source.

        </p>

    </div>



    <!-- Form -->

    <div
        class="card
        border-0
        shadow-sm"
    >

        <div
            class="card-body
            p-4"
        >


            <form
                action="create_process.php"
                method="POST"
            >


                <!-- Wallet Type -->

                <div class="mb-3">

                    <label
                        for="wallet_type_id"
                        class="form-label"
                    >

                        Wallet Type

                    </label>


                    <select
                        name="wallet_type_id"
                        id="wallet_type_id"
                        class="form-select"
                        required
                    >

                        <option
                            value=""
                        >

                            Select wallet type

                        </option>


                        <?php foreach (
                            $walletTypes
                            as $type
                        ): ?>

                            <option
                                value="<?= (int) $type['id'] ?>"
                            >

                                <?= e(
                                    $type['name']
                                ) ?>

                            </option>

                        <?php endforeach; ?>


                    </select>

                </div>



                <!-- Wallet Name -->

                <div class="mb-3">

                    <label
                        for="name"
                        class="form-label"
                    >

                        Wallet Name

                    </label>


                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control"
                        maxlength="100"
                        placeholder="e.g. BPI Savings"
                        required
                    >

                </div>



                <!-- Description -->

                <div class="mb-3">

                    <label
                        for="description"
                        class="form-label"
                    >

                        Description
                        <span
                            class="text-muted"
                        >
                            (Optional)
                        </span>

                    </label>


                    <textarea
                        name="description"
                        id="description"
                        class="form-control"
                        rows="3"
                        maxlength="255"
                        placeholder="Optional description"
                    ></textarea>

                </div>



                <!-- Initial Balance -->

                <div class="mb-3">

                    <label
                        for="initial_balance"
                        class="form-label"
                    >

                        Current / Initial Balance

                    </label>


                    <div
                        class="input-group"
                    >

                        <span
                            class="input-group-text"
                        >

                            ₱

                        </span>


                        <input
                            type="number"
                            name="initial_balance"
                            id="initial_balance"
                            class="form-control"
                            min="0"
                            step="0.01"
                            value="0.00"
                            required
                        >

                    </div>


                    <div
                        class="form-text"
                    >

                        Enter the amount currently
                        available in this wallet.

                    </div>

                </div>



                <!-- Currency -->

                <div class="mb-4">

                    <label
                        for="currency"
                        class="form-label"
                    >

                        Currency

                    </label>


                    <select
                        name="currency"
                        id="currency"
                        class="form-select"
                    >

                        <option
                            value="PHP"
                            selected
                        >

                            PHP - Philippine Peso

                        </option>

                        <option
                            value="USD"
                        >

                            USD - US Dollar

                        </option>

                        <option
                            value="EUR"
                        >

                            EUR - Euro

                        </option>

                    </select>

                </div>



                <!-- Buttons -->

                <div
                    class="d-flex
                    flex-column
                    flex-sm-row
                    gap-2"
                >

                    <button
                        type="submit"
                        class="btn
                        btn-success"
                    >

                        <i
                            class="bi
                            bi-check-circle
                            me-1"
                        ></i>

                        Save Wallet

                    </button>


                    <a
                        href="<?= BASE_URL ?>wallets/"
                        class="btn
                        btn-outline-secondary"
                    >

                        Cancel

                    </a>

                </div>


            </form>


        </div>

    </div>


</div>


</body>

</html>