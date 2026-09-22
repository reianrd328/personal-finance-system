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
| Current User
|--------------------------------------------------------------------------
*/

$userId = currentUserId();


/*
|--------------------------------------------------------------------------
| Get Input
|--------------------------------------------------------------------------
*/

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
| Validate Wallet Type
|--------------------------------------------------------------------------
*/

if (!$walletTypeId) {

    setFlash(
        'danger',
        'Please select a wallet type.'
    );

    redirect(
        BASE_URL . 'wallets/create.php'
    );

}


/*
|--------------------------------------------------------------------------
| Validate Name
|--------------------------------------------------------------------------
*/

if ($name === '') {

    setFlash(
        'danger',
        'Please enter a wallet name.'
    );

    redirect(
        BASE_URL . 'wallets/create.php'
    );

}


/*
|--------------------------------------------------------------------------
| Validate Balance
|--------------------------------------------------------------------------
*/

if (
    !is_numeric($initialBalance) ||
    (float) $initialBalance < 0
) {

    setFlash(
        'danger',
        'Please enter a valid balance.'
    );

    redirect(
        BASE_URL . 'wallets/create.php'
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
| Verify Wallet Type Exists
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
        BASE_URL . 'wallets/create.php'
    );

}


/*
|--------------------------------------------------------------------------
| Insert Wallet
|--------------------------------------------------------------------------
*/

$sql = "
    INSERT INTO wallets
    (
        user_id,
        wallet_type_id,
        name,
        description,
        initial_balance,
        currency,
        status
    )

    VALUES
    (
        :user_id,
        :wallet_type_id,
        :name,
        :description,
        :initial_balance,
        :currency,
        'Active'
    )
";


$stmt = $pdo->prepare($sql);


try {

    $stmt->execute([

        ':user_id' =>
            $userId,

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
            $currency

    ]);


    setFlash(
        'success',
        'Wallet added successfully.'
    );


    redirect(
        BASE_URL . 'wallets/'
    );


} catch (PDOException $e) {

    setFlash(
        'danger',
        'Unable to create the wallet.'
    );


    redirect(
        BASE_URL . 'wallets/create.php'
    );

}