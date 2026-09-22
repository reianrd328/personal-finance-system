<?php

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    redirect(BASE_URL . 'auth/login.php');
}

$userId = (int) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Get Active Wallets
|--------------------------------------------------------------------------
*/

$walletSql = "
    SELECT
        id,
        name,
        currency,
        initial_balance
    FROM wallets
    WHERE user_id = :user_id
      AND status = 'Active'
    ORDER BY name ASC
";

$walletStmt = $pdo->prepare($walletSql);

$walletStmt->execute([
    ':user_id' => $userId
]);

$wallets = $walletStmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Get Active Categories
|--------------------------------------------------------------------------
*/

$categorySql = "
    SELECT
        id,
        name,
        type
    FROM categories
    WHERE status = 'Active'
      AND (
            user_id = :user_id
            OR user_id IS NULL
          )
    ORDER BY type, name ASC
";

$categoryStmt = $pdo->prepare($categorySql);

$categoryStmt->execute([
    ':user_id' => $userId
]);

$categories = $categoryStmt->fetchAll();

?>

<!DOCTYPE html>

<html lang="en">

<head>


<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Add Transaction</title>

<link
    rel="stylesheet"
    href="<?= BASE_URL ?>assets/css/style.css"
>

<style>

    .transaction-container {
        width: 100%;
        max-width: 700px;
        margin: 0 auto;
        padding: 16px;
    }

    .page-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 10px;
        text-decoration: none;
        background: #f1f5f9;
        color: #1e293b;
        font-size: 20px;
    }

    .page-title {
        margin: 0;
        font-size: 22px;
    }

    .transaction-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-label {
        display: block;
        margin-bottom: 7px;
        font-weight: 600;
        color: #334155;
    }

    .form-control {
        width: 100%;
        box-sizing: border-box;
        padding: 13px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 16px;
        background: #ffffff;
    }

    .form-control:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
    }

    .type-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .type-option {
        position: relative;
    }

    .type-option input {
        position: absolute;
        opacity: 0;
    }

    .type-option label {
        display: block;
        padding: 13px 10px;
        text-align: center;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        background: #ffffff;
    }

    .type-option input:checked + label {
        border-color: #2563eb;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .transfer-fields {
        display: none;
    }

    .transfer-fields.active {
        display: block;
    }

    .normal-wallet-field {
        display: block;
    }

    .normal-wallet-field.hidden {
        display: none;
    }

    .category-field {
        display: block;
    }

    .category-field.hidden {
        display: none;
    }

    .submit-button {
        width: 100%;
        border: none;
        border-radius: 12px;
        padding: 14px;
        background: #2563eb;
        color: #ffffff;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
    }

    .submit-button:active {
        transform: scale(0.99);
    }

    .cancel-button {
        display: block;
        width: 100%;
        box-sizing: border-box;
        margin-top: 10px;
        padding: 13px;
        text-align: center;
        text-decoration: none;
        border-radius: 12px;
        background: #f1f5f9;
        color: #334155;
        font-weight: 600;
    }

    @media (min-width: 768px) {

        .transaction-container {
            padding: 30px;
        }

        .transaction-card {
            padding: 30px;
        }

        .type-grid {
            grid-template-columns: repeat(4, 1fr);
        }

    }

</style>


</head>

<body>

<div class="transaction-container">


<div class="page-header">

    <a
        href="<?= BASE_URL ?>transactions/"
        class="back-button"
    >
        ←
    </a>

    <h1 class="page-title">
        Add Transaction
    </h1>

</div>


<div class="transaction-card">

    <form
        action="<?= BASE_URL ?>transactions/create_process.php"
        method="POST"
    >

        <!-- Transaction Type -->

        <div class="form-group">

            <label class="form-label">
                Transaction Type
            </label>

            <div class="type-grid">

                <div class="type-option">

                    <input
                        type="radio"
                        name="type"
                        id="type_income"
                        value="Income"
                        checked
                    >

                    <label for="type_income">
                        Income
                    </label>

                </div>

                <div class="type-option">

                    <input
                        type="radio"
                        name="type"
                        id="type_expense"
                        value="Expense"
                    >

                    <label for="type_expense">
                        Expense
                    </label>

                </div>

                <div class="type-option">

                    <input
                        type="radio"
                        name="type"
                        id="type_transfer"
                        value="Transfer"
                    >

                    <label for="type_transfer">
                        Transfer
                    </label>

                </div>

                <div class="type-option">

                    <input
                        type="radio"
                        name="type"
                        id="type_adjustment"
                        value="Adjustment"
                    >

                    <label for="type_adjustment">
                        Adjustment
                    </label>

                </div>

            </div>

        </div>


        <!-- Normal Wallet -->

        <!-- Adjustment Type -->

<div
    class="form-group adjustment-field"
    id="adjustmentField"
    style="display: none;"
>

    <label
        for="adjustment_type"
        class="form-label"
    >
        Adjustment Type
    </label>

    <select
        name="adjustment_type"
        id="adjustment_type"
        class="form-control"
    >

        <option value="Increase">
            Increase Balance
        </option>

        <option value="Decrease">
            Decrease Balance
        </option>

    </select>

</div>

        <div
            class="form-group normal-wallet-field"
            id="normalWalletField"
        >

            <label
                for="wallet_id"
                class="form-label"
            >
                Wallet
            </label>

            <select
                name="wallet_id"
                id="wallet_id"
                class="form-control"
            >

                <option value="">
                    Select Wallet
                </option>


                <?php foreach ($wallets as $wallet): ?>

                    <option
                        value="<?= (int) $wallet['id'] ?>"
                    >
                        <?= htmlspecialchars($wallet['name']) ?>
                        (<?= htmlspecialchars($wallet['currency']) ?>)
                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <!-- Transfer Wallets -->

        <div
            class="transfer-fields"
            id="transferFields"
        >

            <div class="form-group">

                <label
                    for="from_wallet_id"
                    class="form-label"
                >
                    Transfer From
                </label>

                <select
                    name="from_wallet_id"
                    id="from_wallet_id"
                    class="form-control"
                >

                    <option value="">
                        Select Source Wallet
                    </option>

                    <?php foreach ($wallets as $wallet): ?>

                        <option
                            value="<?= (int) $wallet['id'] ?>"
                        >
                            <?= htmlspecialchars($wallet['name']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-group">

                <label
                    for="to_wallet_id"
                    class="form-label"
                >
                    Transfer To
                </label>

                <select
                    name="to_wallet_id"
                    id="to_wallet_id"
                    class="form-control"
                >

                    <option value="">
                        Select Destination Wallet
                    </option>

                    <?php foreach ($wallets as $wallet): ?>

                        <option
                            value="<?= (int) $wallet['id'] ?>"
                        >
                            <?= htmlspecialchars($wallet['name']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

        </div>


        <!-- Category -->

        <div
            class="form-group category-field"
            id="categoryField"
        >

            <label
                for="category_id"
                class="form-label"
            >
                Category
            </label>

            <select
                name="category_id"
                id="category_id"
                class="form-control"
            >

                <option value="">
                    Select Category
                </option>

                <?php foreach ($categories as $category): ?>

                    <option
                        value="<?= (int) $category['id'] ?>"
                        data-type="<?= htmlspecialchars($category['type']) ?>"
                    >
                        <?= htmlspecialchars($category['name']) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <!-- Amount -->

        <div class="form-group">

            <label
                for="amount"
                class="form-label"
            >
                Amount
            </label>

            <input
                type="number"
                name="amount"
                id="amount"
                class="form-control"
                step="0.01"
                min="0.01"
                placeholder="0.00"
                required
            >

        </div>


        <!-- Date -->

        <div class="form-group">

            <label
                for="transaction_date"
                class="form-label"
            >
                Transaction Date
            </label>

            <input
                type="date"
                name="transaction_date"
                id="transaction_date"
                class="form-control"
                value="<?= date('Y-m-d') ?>"
                required
            >

        </div>


        <!-- Description -->

        <div class="form-group">

            <label
                for="description"
                class="form-label"
            >
                Description
            </label>

            <input
                type="text"
                name="description"
                id="description"
                class="form-control"
                maxlength="255"
                placeholder="Example: Monthly salary"
            >

        </div>


        <!-- Notes -->

        <div class="form-group">

            <label
                for="notes"
                class="form-label"
            >
                Notes
            </label>

            <textarea
                name="notes"
                id="notes"
                class="form-control"
                rows="4"
                placeholder="Optional notes"
            ></textarea>

        </div>


        <button
            type="submit"
            class="submit-button"
        >
            Save Transaction
        </button>


        <a
            href="<?= BASE_URL ?>dashboard/"
            class="cancel-button"
        >
            Cancel
        </a>

    </form>

</div>


</div>

<script>

const typeInputs =
    document.querySelectorAll(
        'input[name="type"]'
    );

const normalWalletField =
    document.getElementById(
        'normalWalletField'
    );

const transferFields =
    document.getElementById(
        'transferFields'
    );

const categoryField =
    document.getElementById(
        'categoryField'
    );

const adjustmentField =
    document.getElementById(
        'adjustmentField'
    );

const categorySelect =
    document.getElementById(
        'category_id'
    );

const categoryOptions =
    Array.from(
        categorySelect.options
    );


function updateTransactionForm() {

    const selectedType =
        document.querySelector(
            'input[name="type"]:checked'
        ).value;


    /*
    |--------------------------------------------------------------------------
    | Transfer
    |--------------------------------------------------------------------------
    */

    if (selectedType === 'Transfer') {

        normalWalletField.classList.add(
            'hidden'
        );

        transferFields.classList.add(
            'active'
        );

        categoryField.classList.add(
            'hidden'
        );

        adjustmentField.style.display =
            'none';

        categorySelect.value = '';

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Normal Wallet
    |--------------------------------------------------------------------------
    */

    normalWalletField.classList.remove(
        'hidden'
    );

    transferFields.classList.remove(
        'active'
    );


    /*
    |--------------------------------------------------------------------------
    | Adjustment
    |--------------------------------------------------------------------------
    */

    if (selectedType === 'Adjustment') {

        categoryField.classList.add(
            'hidden'
        );

        adjustmentField.style.display =
            'block';

        categorySelect.value = '';

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Income / Expense
    |--------------------------------------------------------------------------
    */

    adjustmentField.style.display =
        'none';

    categoryField.classList.remove(
        'hidden'
    );


    categoryOptions.forEach(
        option => {

            if (!option.value) {

                option.hidden = false;

                return;

            }

            const optionType =
                option.dataset.type;

            option.hidden =
                optionType !== selectedType;

        }
    );


    categorySelect.value = '';

}


typeInputs.forEach(
    input => {

        input.addEventListener(
            'change',
            updateTransactionForm
        );

    }
);


updateTransactionForm();

</script>

</body>

</html>
