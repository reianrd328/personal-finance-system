<?php

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/middleware/admin.php';


/*
|--------------------------------------------------------------------------
| Start Session
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Check Request Method
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect(
        BASE_URL . 'transactions/'
    );

}


/*
|--------------------------------------------------------------------------
| Current User
|--------------------------------------------------------------------------
*/

$userId =
    (int) ($_SESSION['user_id'] ?? 0);


/*
|--------------------------------------------------------------------------
| Get Transaction ID
|--------------------------------------------------------------------------
*/

$transactionId =
    isset($_POST['id'])
        ? (int) $_POST['id']
        : 0;


if ($transactionId <= 0) {

    setFlash(
        'danger',
        'Invalid transaction selected.'
    );

    redirect(
        BASE_URL . 'transactions/'
    );

}


try {

    /*
    |--------------------------------------------------------------------------
    | Verify Transaction Ownership
    |--------------------------------------------------------------------------
    |
    | Administrator privileges are required by admin.php.
    | The transaction must also belong to the currently logged-in account.
    | This prevents an administrator from deleting another user's transaction
    | through a manually crafted request while keeping the existing page scope.
    |
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            user_id,
            type
        FROM transactions
        WHERE id = :id
          AND user_id = :user_id
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':id' => $transactionId,
        ':user_id' => $userId
    ]);

    $transaction = $stmt->fetch();


    if (!$transaction) {

        setFlash(
            'danger',
            'Transaction not found or you do not have permission to delete it.'
        );

        redirect(
            BASE_URL . 'transactions/'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Delete Transaction
    |--------------------------------------------------------------------------
    |
    | Transfers consist of two linked transaction records. If the selected
    | transaction is part of a transfer, delete the transfer link and both
    | transaction records together.
    |
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();


    $transferSql = "
        SELECT
            transaction_id,
            destination_transaction_id
        FROM transaction_transfers
        WHERE transaction_id = :transaction_id
           OR destination_transaction_id = :destination_transaction_id
        LIMIT 1
    ";

    $transferStmt =
        $pdo->prepare($transferSql);

    $transferStmt->execute([
        ':transaction_id' => $transactionId,
        ':destination_transaction_id' => $transactionId
    ]);

    $transfer = $transferStmt->fetch();


    if ($transfer) {

        $sourceId =
            (int) $transfer['transaction_id'];

        $destinationId =
            (int) $transfer['destination_transaction_id'];


        /*
        |--------------------------------------------------------------------------
        | Delete Transfer Link First
        |--------------------------------------------------------------------------
        */

        $deleteLinkSql = "
            DELETE FROM transaction_transfers
            WHERE transaction_id = :transaction_id
               OR destination_transaction_id = :destination_transaction_id
        ";

        $deleteLinkStmt =
            $pdo->prepare($deleteLinkSql);

        $deleteLinkStmt->execute([
            ':transaction_id' => $transactionId,
            ':destination_transaction_id' => $transactionId
        ]);


        /*
        |--------------------------------------------------------------------------
        | Delete Both Sides Of Transfer
        |--------------------------------------------------------------------------
        */

        $deleteTransactionsSql = "
            DELETE FROM transactions
            WHERE id IN (:source_id, :destination_id)
        ";

        $deleteTransactionsStmt =
            $pdo->prepare($deleteTransactionsSql);

        $deleteTransactionsStmt->execute([
            ':source_id' => $sourceId,
            ':destination_id' => $destinationId
        ]);


        $pdo->commit();

        setFlash(
            'success',
            'Transfer transaction deleted successfully.'
        );

    } else {

        /*
        |--------------------------------------------------------------------------
        | Delete Normal Transaction
        |--------------------------------------------------------------------------
        */

        $deleteSql = "
            DELETE FROM transactions
            WHERE id = :id
              AND user_id = :user_id
        ";

        $deleteStmt =
            $pdo->prepare($deleteSql);

        $deleteStmt->execute([
            ':id' => $transactionId,
            ':user_id' => $userId
        ]);


        if ($deleteStmt->rowCount() !== 1) {

            throw new Exception(
                'The transaction could not be deleted.'
            );

        }


        $pdo->commit();

        setFlash(
            'success',
            'Transaction deleted successfully.'
        );

    }

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }

    setFlash(
        'danger',
        'Unable to delete transaction: ' . $e->getMessage()
    );

}


redirect(
    BASE_URL . 'transactions/'
);
