<?php

require_once __DIR__ .
    '/../app/middleware/auth.php';

require_once __DIR__ .
    '/../app/config/database.php';

require_once __DIR__ .
    '/../app/helpers/functions.php';


/*
|--------------------------------------------------------------------------
| Get Wallet ID
|--------------------------------------------------------------------------
*/

$walletId =
    filter_input(
        INPUT_GET,
        'id',
        FILTER_VALIDATE_INT
    );


if (!$walletId) {

    setFlash(
        'danger',
        'Invalid wallet.'
    );

    redirect(
        BASE_URL . 'wallets/'
    );

}


/*
|--------------------------------------------------------------------------
| Archive Only User's Own Wallet
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE wallets

    SET status = 'Archived'

    WHERE id = :id

    AND user_id = :user_id

    AND status = 'Active'
";


$stmt = $pdo->prepare($sql);


$stmt->execute([

    ':id' =>
        $walletId,

    ':user_id' =>
        currentUserId()

]);


if (
    $stmt->rowCount() > 0
) {

    setFlash(
        'success',
        'Wallet archived successfully.'
    );

} else {

    setFlash(
        'danger',
        'Wallet not found or already archived.'
    );

}


redirect(
    BASE_URL . 'wallets/'
);