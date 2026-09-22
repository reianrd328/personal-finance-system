<?php

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/functions.php';


/*
|--------------------------------------------------------------------------
| Only Allow POST Requests
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect(
        BASE_URL . 'transactions/create.php'
    );

}


/*
|--------------------------------------------------------------------------
| Check Login
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {

    redirect(
        BASE_URL . 'auth/login.php'
    );

}

$userId = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Get Form Data
|--------------------------------------------------------------------------
*/

$type = trim(
    $_POST['type'] ?? ''
);

$walletId = (int) (
    $_POST['wallet_id'] ?? 0
);

$fromWalletId = (int) (
    $_POST['from_wallet_id'] ?? 0
);

$toWalletId = (int) (
    $_POST['to_wallet_id'] ?? 0
);

$categoryId = (int) (
    $_POST['category_id'] ?? 0
);

$amount = trim(
    $_POST['amount'] ?? ''
);

$adjustmentType = trim(
    $_POST['adjustment_type'] ?? ''
);

$transactionDate =
    $_POST['transaction_date'] ?? '';

$description = trim(
    $_POST['description'] ?? ''
);

$notes = trim(
    $_POST['notes'] ?? ''
);


/*
|--------------------------------------------------------------------------
| Validate Transaction Type
|--------------------------------------------------------------------------
*/

$allowedTypes = [
    'Income',
    'Expense',
    'Transfer',
    'Adjustment'
];

if (!in_array($type, $allowedTypes, true)) {

    setFlash(
        'danger',
        'Invalid transaction type.'
    );

    redirect(
        BASE_URL . 'transactions/create.php'
    );

}

/*
|--------------------------------------------------------------------------
| Validate Adjustment Type
|--------------------------------------------------------------------------
*/

if ($type === 'Adjustment') {

    $allowedAdjustmentTypes = [
        'Increase',
        'Decrease'
    ];

    if (
        !in_array(
            $adjustmentType,
            $allowedAdjustmentTypes,
            true
        )
    ) {

        setFlash(
            'danger',
            'Please select a valid adjustment type.'
        );

        redirect(
            BASE_URL . 'transactions/create.php'
        );

    }

}

/*
|--------------------------------------------------------------------------
| Validate Amount
|--------------------------------------------------------------------------
*/

if (
    $amount === '' ||
    !is_numeric($amount) ||
    (float) $amount <= 0
) {

    setFlash(
        'danger',
        'Please enter a valid transaction amount.'
    );

    redirect(
        BASE_URL . 'transactions/create.php'
    );

}

$amount = number_format(
    (float) $amount,
    2,
    '.',
    ''
);

/*
|--------------------------------------------------------------------------
| Adjustment Sign
|--------------------------------------------------------------------------
*/

if ($type === 'Adjustment') {

    if ($adjustmentType === 'Decrease') {

        $amount = '-' . $amount;

    }

}

/*
|--------------------------------------------------------------------------
| Validate Transaction Date
|--------------------------------------------------------------------------
*/

$dateObject = DateTime::createFromFormat(
    'Y-m-d',
    $transactionDate
);

if (
    !$dateObject ||
    $dateObject->format('Y-m-d') !== $transactionDate
) {

    setFlash(
        'danger',
        'Please enter a valid transaction date.'
    );

    redirect(
        BASE_URL . 'transactions/create.php'
    );

}


/*
|--------------------------------------------------------------------------
| Validate Description Length
|--------------------------------------------------------------------------
*/

if (strlen($description) > 255) {

    setFlash(
        'danger',
        'Description is too long.'
    );

    redirect(
        BASE_URL . 'transactions/create.php'
    );

}


/*
|--------------------------------------------------------------------------
| Validate Notes Length
|--------------------------------------------------------------------------
*/

if (strlen($notes) > 10000) {

    setFlash(
        'danger',
        'Notes are too long.'
    );

    redirect(
        BASE_URL . 'transactions/create.php'
    );

}


/*
|--------------------------------------------------------------------------
| TRANSFER
|--------------------------------------------------------------------------
|
| A transfer creates TWO transaction records:
|
| Transaction A
| Source wallet
| Transfer
|
| Transaction B
| Destination wallet
| Transfer
|
| They are linked through transaction_transfers.
|
|--------------------------------------------------------------------------
*/

if ($type === 'Transfer') {

    /*
    |--------------------------------------------------------------------------
    | Validate Wallet IDs
    |--------------------------------------------------------------------------
    */

    if (
        $fromWalletId <= 0 ||
        $toWalletId <= 0
    ) {

        setFlash(
            'danger',
            'Please select both source and destination wallets.'
        );

        redirect(
            BASE_URL . 'transactions/create.php'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Prevent Same Wallet Transfer
    |--------------------------------------------------------------------------
    */

    if ($fromWalletId === $toWalletId) {

        setFlash(
            'danger',
            'Source and destination wallets cannot be the same.'
        );

        redirect(
            BASE_URL . 'transactions/create.php'
        );

    }


    try {

        /*
        |--------------------------------------------------------------------------
        | Start Database Transaction
        |--------------------------------------------------------------------------
        */

        $pdo->beginTransaction();


        /*
        |--------------------------------------------------------------------------
        | Validate Source Wallet
        |--------------------------------------------------------------------------
        */

        $walletSql = "
            SELECT
                id,
                name
            FROM wallets
            WHERE id = :wallet_id
              AND user_id = :user_id
              AND status = 'Active'
            LIMIT 1
        ";

        $walletStmt =
            $pdo->prepare($walletSql);


        $walletStmt->execute([

            ':wallet_id' =>
                $fromWalletId,

            ':user_id' =>
                $userId

        ]);


        $fromWallet =
            $walletStmt->fetch();


        if (!$fromWallet) {

            throw new Exception(
                'The source wallet is invalid.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Destination Wallet
        |--------------------------------------------------------------------------
        */

        $walletStmt->execute([

            ':wallet_id' =>
                $toWalletId,

            ':user_id' =>
                $userId

        ]);


        $toWallet =
            $walletStmt->fetch();


        if (!$toWallet) {

            throw new Exception(
                'The destination wallet is invalid.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Create Source Transaction
        |--------------------------------------------------------------------------
        */

        $transactionSql = "
            INSERT INTO transactions (
                user_id,
                wallet_id,
                category_id,
                type,
                amount,
                transaction_date,
                description,
                notes
            )

            VALUES (
                :user_id,
                :wallet_id,
                NULL,
                'Transfer',
                :amount,
                :transaction_date,
                :description,
                :notes
            )
        ";

        $transactionStmt =
            $pdo->prepare(
                $transactionSql
            );


        $transactionStmt->execute([

            ':user_id' =>
                $userId,

            ':wallet_id' =>
                $fromWalletId,

            ':amount' =>
                $amount,

            ':transaction_date' =>
                $transactionDate,

            ':description' =>
                $description,

            ':notes' =>
                $notes

        ]);


        $sourceTransactionId =
            (int) $pdo->lastInsertId();


        /*
        |--------------------------------------------------------------------------
        | Create Destination Transaction
        |--------------------------------------------------------------------------
        */

        $transactionStmt->execute([

            ':user_id' =>
                $userId,

            ':wallet_id' =>
                $toWalletId,

            ':amount' =>
                $amount,

            ':transaction_date' =>
                $transactionDate,

            ':description' =>
                $description,

            ':notes' =>
                $notes

        ]);


        $destinationTransactionId =
            (int) $pdo->lastInsertId();


        /*
        |--------------------------------------------------------------------------
        | Link The Two Transactions
        |--------------------------------------------------------------------------
        */

        $transferSql = "
            INSERT INTO transaction_transfers (
                transaction_id,
                destination_transaction_id
            )

            VALUES (
                :transaction_id,
                :destination_transaction_id
            )
        ";

        $transferStmt =
            $pdo->prepare(
                $transferSql
            );


        $transferStmt->execute([

            ':transaction_id' =>
                $sourceTransactionId,

            ':destination_transaction_id' =>
                $destinationTransactionId

        ]);


        /*
        |--------------------------------------------------------------------------
        | Commit
        |--------------------------------------------------------------------------
        */

        $pdo->commit();


        setFlash(
            'success',
            'Transfer recorded successfully.'
        );


        redirect(
            BASE_URL . 'transactions/'
        );


    } catch (Throwable $e) {

        /*
        |--------------------------------------------------------------------------
        | Rollback
        |--------------------------------------------------------------------------
        */

        if ($pdo->inTransaction()) {

            $pdo->rollBack();

        }


        /*
        |--------------------------------------------------------------------------
        | Development Error
        |--------------------------------------------------------------------------
        */

        setFlash(
            'danger',
            'Unable to record transfer: ' .
            $e->getMessage()
        );


        redirect(
            BASE_URL . 'transactions/create.php'
        );

    }

}


/*
|--------------------------------------------------------------------------
| NORMAL TRANSACTIONS
|--------------------------------------------------------------------------
|
| Income
| Expense
| Adjustment
|
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Validate Wallet
|--------------------------------------------------------------------------
*/

if ($walletId <= 0) {

    setFlash(
        'danger',
        'Please select a wallet.'
    );

    redirect(
        BASE_URL . 'transactions/create.php'
    );

}


try {

    /*
    |--------------------------------------------------------------------------
    | Validate Wallet Ownership
    |--------------------------------------------------------------------------
    */

    $walletSql = "
        SELECT
            id,
            name
        FROM wallets
        WHERE id = :wallet_id
          AND user_id = :user_id
          AND status = 'Active'
        LIMIT 1
    ";

    $walletStmt =
        $pdo->prepare($walletSql);


    $walletStmt->execute([

        ':wallet_id' =>
            $walletId,

        ':user_id' =>
            $userId

    ]);


    $wallet =
        $walletStmt->fetch();


    if (!$wallet) {

        throw new Exception(
            'The selected wallet is invalid.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Category Validation
    |--------------------------------------------------------------------------
    |
    | Income and Expense require a category.
    |
    | Adjustment does not require one.
    |
    |--------------------------------------------------------------------------
    */

    if (
        $type === 'Income' ||
        $type === 'Expense'
    ) {

        if ($categoryId <= 0) {

            throw new Exception(
                'Please select a category.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Make Sure Category Belongs To User
        |--------------------------------------------------------------------------
        */

        $categorySql = "
            SELECT
                id,
                type
            FROM categories
            WHERE id = :category_id
              AND status = 'Active'
              AND (
                    user_id = :user_id
                    OR user_id IS NULL
                  )
            LIMIT 1
        ";


        $categoryStmt =
            $pdo->prepare(
                $categorySql
            );


        $categoryStmt->execute([

            ':category_id' =>
                $categoryId,

            ':user_id' =>
                $userId

        ]);


        $category =
            $categoryStmt->fetch();


        if (!$category) {

            throw new Exception(
                'The selected category is invalid.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Make Sure Category Type Matches Transaction
        |--------------------------------------------------------------------------
        */

        if ($category['type'] !== $type) {

            throw new Exception(
                'The selected category does not match the transaction type.'
            );

        }

    } else {

        $categoryId = null;

    }


    /*
    |--------------------------------------------------------------------------
    | Insert Transaction
    |--------------------------------------------------------------------------
    */

    $sql = "
        INSERT INTO transactions (
            user_id,
            wallet_id,
            category_id,
            type,
            amount,
            transaction_date,
            description,
            notes
        )

        VALUES (
            :user_id,
            :wallet_id,
            :category_id,
            :type,
            :amount,
            :transaction_date,
            :description,
            :notes
        )
    ";


    $stmt =
        $pdo->prepare($sql);


    $stmt->execute([

        ':user_id' =>
            $userId,

        ':wallet_id' =>
            $walletId,

        ':category_id' =>
            $categoryId,

        ':type' =>
            $type,

        ':amount' =>
            $amount,

        ':transaction_date' =>
            $transactionDate,

        ':description' =>
            $description,

        ':notes' =>
            $notes

    ]);


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    setFlash(
        'success',
        $type . ' transaction recorded successfully.'
    );


    redirect(
        BASE_URL . 'transactions/'
    );


} catch (Throwable $e) {

    setFlash(
        'danger',
        'Unable to save transaction: ' .
        $e->getMessage()
    );


    redirect(
        BASE_URL . 'transactions/create.php'
    );

}
?>
