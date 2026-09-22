
<?php

require_once __DIR__ .
    '/../app/middleware/auth.php';

require_once __DIR__ .
    '/../app/config/database.php';

require_once __DIR__ .
    '/../app/helpers/functions.php';


/*
|--------------------------------------------------------------------------
| Only POST
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD']
    !== 'POST'
) {

    redirect(
        BASE_URL . 'wallets/'
    );

}


/*
|--------------------------------------------------------------------------
| Get Input
|--------------------------------------------------------------------------
*/

$walletId =
    filter_input(
        INPUT_POST,
        'id',
        FILTER_VALIDATE_INT
    );

$walletTypeId =
    filter_input(
        INPUT_POST,
        'wallet_type_id',
        FILTER_VALIDATE_INT
    );

$name =
    trim(
        $_POST['name'] ?? ''
    );

$description =
    trim(
        $_POST['description'] ?? ''
    );

$initialBalance =
    $_POST['initial_balance']
    ?? '0';

$currency =
    $_POST['currency']
    ?? 'PHP';


/*
|--------------------------------------------------------------------------
| Validate Wallet
|--------------------------------------------------------------------------
*/

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
| Verify Wallet Ownership
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM wallets

    WHERE id = :id

    AND user_id = :user_id

    LIMIT 1
");

$stmt->execute([

    ':id' =>
        $walletId,

    ':user_id' =>
        currentUserId()

]);


if (!$stmt->fetch()) {

    setFlash(
        'danger',
        'Wallet not found.'
    );

    redirect(
        BASE_URL . 'wallets/'
    );

}


/*
|--------------------------------------------------------------------------
| Validate Input
|--------------------------------------------------------------------------
*/

if (
    !$walletTypeId ||
    $name === ''
) {

    setFlash(
        'danger',
        'Please complete all required fields.'
    );

    redirect(
        BASE_URL .
        'wallets/edit.php?id=' .
        $walletId
    );

}


if (
    !is_numeric($initialBalance) ||
    (float) $initialBalance < 0
) {

    setFlash(
        'danger',
        'Please enter a valid balance.'
    );

    redirect(
        BASE_URL .
        'wallets/edit.php?id=' .
        $walletId
    );

}


/*
|--------------------------------------------------------------------------
| Allowed Currencies
|--------------------------------------------------------------------------
*/

$allowedCurrencies = [

    'PHP',
    'USD',
    'EUR'

];


if (
    !in_array(
        $currency,
        $allowedCurrencies,
        true
    )
) {

    $currency = 'PHP';

}


/*
|--------------------------------------------------------------------------
| Verify Wallet Type
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id

    FROM wallet_types

    WHERE id = :id

    AND status = 'Active'

    LIMIT 1
");

$stmt->execute([

    ':id' =>
        $walletTypeId

]);


if (!$stmt->fetch()) {

    setFlash(
        'danger',
        'Invalid wallet type.'
    );

    redirect(
        BASE_URL .
        'wallets/edit.php?id=' .
        $walletId
    );

}


/*
|--------------------------------------------------------------------------
| Update Wallet
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE wallets

    SET
        wallet_type_id = :wallet_type_id,
        name = :name,
        description = :description,
        initial_balance = :initial_balance,
        currency = :currency

    WHERE id = :id

    AND user_id = :user_id
";


$stmt = $pdo->prepare($sql);


try {

    $stmt->execute([

        ':wallet_type_id' =>
            $walletTypeId,

        ':name' =>
            $name,

        ':description' =>
            $description !== ''
                ? $description
                : null,

        ':initial_balance' =>
            number_format(
                (float) $initialBalance,
                2,
                '.',
                ''
            ),

        ':currency' =>
            $currency,

        ':id' =>
            $walletId,

        ':user_id' =>
            currentUserId()

    ]);


    setFlash(
        'success',
        'Wallet updated successfully.'
    );


    redirect(
        BASE_URL .
        'wallets/view.php?id=' .
        $walletId
    );


} catch (PDOException $e) {

    setFlash(
        'danger',
        'Unable to update the wallet.'
    );


    redirect(
        BASE_URL .
        'wallets/edit.php?id=' .
        $walletId
    );

}

