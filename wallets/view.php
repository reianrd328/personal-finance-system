
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
| Get Wallet
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        w.id,
        w.name,
        w.description,
        w.initial_balance,
        w.currency,
        w.status,
        w.created_at,
        w.updated_at,

        wt.name AS wallet_type,
        wt.description AS wallet_type_description

    FROM wallets w

    INNER JOIN wallet_types wt
        ON w.wallet_type_id = wt.id

    WHERE w.id = :id

    AND w.user_id = :user_id

    LIMIT 1
";


$stmt = $pdo->prepare($sql);

$stmt->execute([

    ':id' =>
        $walletId,

    ':user_id' =>
        currentUserId()

]);


$wallet = $stmt->fetch();


if (!$wallet) {

    setFlash(
        'danger',
        'Wallet not found.'
    );

    redirect(
        BASE_URL . 'wallets/'
    );

}


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
        <?= e($wallet['name']) ?> -
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
    class="container-fluid
    p-3 p-md-4"
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


        <div
            class="d-flex
            flex-column
            flex-sm-row
            justify-content-between
            align-items-sm-center
            gap-3"
        >

            <div>

                <h2
                    class="fw-bold
                    mb-1"
                >

                    <?= e(
                        $wallet['name']
                    ) ?>

                </h2>


                <div
                    class="text-muted"
                >

                    <?= e(
                        $wallet[
                            'wallet_type'
                        ]
                    ) ?>

                </div>

            </div>


            <div>

                <a
                    href="<?= BASE_URL ?>wallets/edit.php?id=<?= (int) $wallet['id'] ?>"
                    class="btn
                    btn-outline-primary"
                >

                    <i
                        class="bi
                        bi-pencil
                        me-1"
                    ></i>

                    Edit Wallet

                </a>

            </div>

        </div>

    </div>



    <!-- Balance -->

    <div
        class="card
        border-0
        shadow-sm
        mb-4"
    >

        <div
            class="card-body
            p-4"
        >

            <div
                class="text-muted
                small"
            >

                Current Balance

            </div>


            <div
                class="display-5
                fw-bold
                mt-2"
            >

                <?= e(
                    formatMoney(
                        $wallet[
                            'initial_balance'
                        ]
                    )
                ) ?>

            </div>


            <span
                class="badge
                <?= $wallet['status']
                    === 'Active'
                    ? 'bg-success'
                    : 'bg-secondary'
                ?>"
            >

                <?= e(
                    $wallet['status']
                ) ?>

            </span>

        </div>

    </div>



    <!-- Wallet Information -->

    <div
        class="card
        border-0
        shadow-sm
        mb-4"
    >

        <div
            class="card-body
            p-4"
        >

            <h5
                class="fw-bold
                mb-3"
            >

                Wallet Information

            </h5>


            <div
                class="row
                g-3"
            >


                <div
                    class="col-12
                    col-md-6"
                >

                    <div
                        class="text-muted
                        small"
                    >

                        Wallet Type

                    </div>

                    <div
                        class="fw-semibold"
                    >

                        <?= e(
                            $wallet[
                                'wallet_type'
                            ]
                        ) ?>

                    </div>

                </div>


                <div
                    class="col-12
                    col-md-6"
                >

                    <div
                        class="text-muted
                        small"
                    >

                        Currency

                    </div>

                    <div
                        class="fw-semibold"
                    >

                        <?= e(
                            $wallet[
                                'currency'
                            ]
                        ) ?>

                    </div>

                </div>


                <div
                    class="col-12"
                >

                    <div
                        class="text-muted
                        small"
                    >

                        Description

                    </div>

                    <div
                        class="fw-semibold"
                    >

                        <?= !empty(
                            $wallet[
                                'description'
                            ]
                        )
                            ? e(
                                $wallet[
                                    'description'
                                ]
                            )
                            : 'No description'
                        ?>

                    </div>

                </div>


                <div
                    class="col-12
                    col-md-6"
                >

                    <div
                        class="text-muted
                        small"
                    >

                        Created

                    </div>

                    <div
                        class="fw-semibold"
                    >

                        <?= e(
                            date(
                                'M d, Y h:i A',
                                strtotime(
                                    $wallet[
                                        'created_at'
                                    ]
                                )
                            )
                        ) ?>

                    </div>

                </div>


                <div
                    class="col-12
                    col-md-6"
                >

                    <div
                        class="text-muted
                        small"
                    >

                        Last Updated

                    </div>

                    <div
                        class="fw-semibold"
                    >

                        <?= e(
                            date(
                                'M d, Y h:i A',
                                strtotime(
                                    $wallet[
                                        'updated_at'
                                    ]
                                )
                            )
                        ) ?>

                    </div>

                </div>


            </div>

        </div>

    </div>



    <!-- Transactions Placeholder -->

    <div
        class="card
        border-0
        shadow-sm"
    >

        <div
            class="card-body
            p-4
            text-center"
        >

            <i
                class="bi
                bi-receipt
                display-5
                text-muted"
            ></i>


            <h5
                class="fw-bold
                mt-3"
            >

                Transactions

            </h5>


            <p
                class="text-muted
                mb-0"
            >

                Income, expenses, and transfers
                for this wallet will appear here.

            </p>

        </div>

    </div>


</div>


</body>

</html>

