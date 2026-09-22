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
| Current User
|--------------------------------------------------------------------------
*/

$user = currentUser();

$userId = currentUserId();


/*
|--------------------------------------------------------------------------
| Date Filters
|--------------------------------------------------------------------------
*/

$startDate =
    trim(
        $_GET['start_date'] ?? ''
    );

$endDate =
    trim(
        $_GET['end_date'] ?? ''
    );


/*
|--------------------------------------------------------------------------
| Validate Start Date
|--------------------------------------------------------------------------
*/

if ($startDate !== '') {

    $startObject =
        DateTime::createFromFormat(
            'Y-m-d',
            $startDate
        );

    if (
        !$startObject ||
        $startObject->format('Y-m-d') !== $startDate
    ) {

        $startDate = '';

    }

}


/*
|--------------------------------------------------------------------------
| Validate End Date
|--------------------------------------------------------------------------
*/

if ($endDate !== '') {

    $endObject =
        DateTime::createFromFormat(
            'Y-m-d',
            $endDate
        );

    if (
        !$endObject ||
        $endObject->format('Y-m-d') !== $endDate
    ) {

        $endDate = '';

    }

}


/*
|--------------------------------------------------------------------------
| Correct Reversed Date Range
|--------------------------------------------------------------------------
*/

if (
    $startDate !== ''
    &&
    $endDate !== ''
    &&
    $startDate > $endDate
) {

    [
        $startDate,
        $endDate
    ] = [
        $endDate,
        $startDate
    ];

}


/*
|--------------------------------------------------------------------------
| Report Period
|--------------------------------------------------------------------------
*/

$reportStart =
    $startDate !== ''
        ? date(
            'M d, Y',
            strtotime($startDate)
        )
        : 'Beginning';


$reportEnd =
    $endDate !== ''
        ? date(
            'M d, Y',
            strtotime($endDate)
        )
        : 'Present';


/*
|--------------------------------------------------------------------------
| Current Wallet Balances
|--------------------------------------------------------------------------
|
| IMPORTANT:
| The wallet balances are NOT date filtered.
|
| They represent the current actual balance,
| matching Dashboard and My Wallets.
|
|--------------------------------------------------------------------------
*/

$walletSql = "
    SELECT

        w.id,

        w.name,

        w.initial_balance,

        w.currency,

        wt.name AS wallet_type,

        (
            w.initial_balance

            /* Income */

            + COALESCE(
                (
                    SELECT SUM(t.amount)

                    FROM transactions t

                    WHERE t.wallet_id = w.id
                      AND t.user_id = :income_user_id
                      AND t.type = 'Income'

                ),
                0
            )

            /* Expense */

            - COALESCE(
                (
                    SELECT SUM(t.amount)

                    FROM transactions t

                    WHERE t.wallet_id = w.id
                      AND t.user_id = :expense_user_id
                      AND t.type = 'Expense'

                ),
                0
            )

            /* Adjustment */

            + COALESCE(
                (
                    SELECT SUM(t.amount)

                    FROM transactions t

                    WHERE t.wallet_id = w.id
                      AND t.user_id = :adjustment_user_id
                      AND t.type = 'Adjustment'

                ),
                0
            )

            /* Transfer Sent */

            - COALESCE(
                (
                    SELECT SUM(source_t.amount)

                    FROM transaction_transfers tt

                    INNER JOIN transactions source_t
                        ON tt.transaction_id =
                           source_t.id

                    WHERE source_t.wallet_id = w.id
                      AND source_t.user_id =
                          :sent_user_id

                ),
                0
            )

            /* Transfer Received */

            + COALESCE(
                (
                    SELECT SUM(destination_t.amount)

                    FROM transaction_transfers tt

                    INNER JOIN transactions destination_t
                        ON tt.destination_transaction_id =
                           destination_t.id

                    WHERE destination_t.wallet_id = w.id
                      AND destination_t.user_id =
                          :received_user_id

                ),
                0
            )

        ) AS current_balance

    FROM wallets w

    INNER JOIN wallet_types wt
        ON w.wallet_type_id = wt.id

    WHERE w.user_id = :wallet_user_id

      AND w.status = 'Active'

    ORDER BY w.name ASC
";


$walletStmt =
    $pdo->prepare(
        $walletSql
    );


$walletStmt->execute([

    ':income_user_id' =>
        $userId,

    ':expense_user_id' =>
        $userId,

    ':adjustment_user_id' =>
        $userId,

    ':sent_user_id' =>
        $userId,

    ':received_user_id' =>
        $userId,

    ':wallet_user_id' =>
        $userId

]);


$wallets =
    $walletStmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Current Total Balance
|--------------------------------------------------------------------------
*/

$totalBalance = 0;


foreach (
    $wallets
    as $wallet
) {

    $totalBalance +=
        (float)
        $wallet['current_balance'];

}


/*
|--------------------------------------------------------------------------
| Build Date Conditions
|--------------------------------------------------------------------------
*/

$dateConditions = '';

$dateParams = [
    ':user_id' =>
        $userId
];


if ($startDate !== '') {

    $dateConditions .= "
        AND transaction_date >= :start_date
    ";

    $dateParams[':start_date'] =
        $startDate;

}


if ($endDate !== '') {

    $dateConditions .= "
        AND transaction_date <= :end_date
    ";

    $dateParams[':end_date'] =
        $endDate;

}


/*
|--------------------------------------------------------------------------
| Total Income
|--------------------------------------------------------------------------
*/

$incomeSql = "
    SELECT
        COALESCE(
            SUM(amount),
            0
        )

    FROM transactions

    WHERE user_id = :user_id

      AND type = 'Income'

      $dateConditions
";


$incomeStmt =
    $pdo->prepare(
        $incomeSql
    );


$incomeStmt->execute(
    $dateParams
);


$totalIncome =
    (float)
    $incomeStmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Total Expenses
|--------------------------------------------------------------------------
*/

$expenseSql = "
    SELECT
        COALESCE(
            SUM(amount),
            0
        )

    FROM transactions

    WHERE user_id = :user_id

      AND type = 'Expense'

      $dateConditions
";


$expenseStmt =
    $pdo->prepare(
        $expenseSql
    );


$expenseStmt->execute(
    $dateParams
);


$totalExpense =
    (float)
    $expenseStmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Total Adjustments
|--------------------------------------------------------------------------
*/

$adjustmentSql = "
    SELECT
        COALESCE(
            SUM(amount),
            0
        )

    FROM transactions

    WHERE user_id = :user_id

      AND type = 'Adjustment'

      $dateConditions
";


$adjustmentStmt =
    $pdo->prepare(
        $adjustmentSql
    );


$adjustmentStmt->execute(
    $dateParams
);


$totalAdjustment =
    (float)
    $adjustmentStmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Expenses By Category
|--------------------------------------------------------------------------
*/

$categoryExpenseSql = "
    SELECT

        COALESCE(
            c.name,
            'Uncategorized'
        ) AS category_name,

        SUM(t.amount) AS total_expense

    FROM transactions t

    LEFT JOIN categories c
        ON t.category_id = c.id

    WHERE t.user_id = :user_id

      AND t.type = 'Expense'

      $dateConditions

    GROUP BY

        COALESCE(
            c.name,
            'Uncategorized'
        )

    ORDER BY
        total_expense DESC
";


$categoryExpenseStmt =
    $pdo->prepare(
        $categoryExpenseSql
    );


$categoryExpenseStmt->execute(
    $dateParams
);


$categoryExpenses =
    $categoryExpenseStmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Prepare Chart Data
|--------------------------------------------------------------------------
*/

$categoryLabels = [];

$categoryAmounts = [];

foreach (
    $categoryExpenses
    as $categoryExpense
) {

    $categoryLabels[] =
        $categoryExpense[
            'category_name'
        ];

    $categoryAmounts[] =
        (float)
        $categoryExpense[
            'total_expense'
        ];

}


/*
|--------------------------------------------------------------------------
| Net Cash Flow
|--------------------------------------------------------------------------
*/

$netCashFlow =
    $totalIncome
    -
    $totalExpense
    +
    $totalAdjustment;


/*
|--------------------------------------------------------------------------
| Generated Transaction List
|--------------------------------------------------------------------------
*/

$transactionSql = "
    SELECT

        t.id,

        t.transaction_date,

        t.type,

        t.amount,

        t.description,

        w.name AS wallet_name,

        c.name AS category_name

    FROM transactions t

    INNER JOIN wallets w
        ON t.wallet_id = w.id

    LEFT JOIN categories c
        ON t.category_id = c.id

    WHERE t.user_id = :user_id

      $dateConditions

    ORDER BY

        t.transaction_date ASC,

        t.id ASC
";


$transactionStmt =
    $pdo->prepare(
        $transactionSql
    );


$transactionStmt->execute(
    $dateParams
);


$transactions =
    $transactionStmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Money Formatter
|--------------------------------------------------------------------------
*/

function generatedReportMoney(
    float $amount,
    string $currency = 'PHP'
): string {

    $symbol =
        $currency === 'PHP'
            ? '₱'
            : $currency . ' ';

    return $symbol .
        number_format(
            $amount,
            2
        );

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Generated Financial Report -
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


<script
    src="https://cdn.jsdelivr.net/npm/chart.js"
></script>


<style>

    body {

        background:
            #f8fafc;

        color:
            #1e293b;

    }


    .report-container {

        max-width:
            1200px;

        margin:
            0 auto;

        padding:
            24px;

    }


    .report-header {

        background:
            linear-gradient(
                135deg,
                #198754,
                #157347
            );

        color:
            #ffffff;

        border-radius:
            16px;

        padding:
            28px;

        margin-bottom:
            24px;

    }


    .report-header h1 {

        margin:
            0;

        font-weight:
            700;

    }


    .report-period {

        margin-top:
            6px;

        opacity:
            0.85;

    }


    .report-card {

        border:
            0;

        border-radius:
            15px;

        box-shadow:
            0 3px 15px
            rgba(
                0,
                0,
                0,
                0.06
            );

        margin-bottom:
            20px;

    }


    .summary-value {

        font-size:
            24px;

        font-weight:
            700;

    }


    .income {

        color:
            #15803d;

    }


    .expense {

        color:
            #dc2626;

    }


    .adjustment {

        color:
            #7c3aed;

    }


    .flow-positive {

        color:
            #15803d;

    }


    .flow-negative {

        color:
            #dc2626;

    }


    .table th {

        font-size:
            12px;

        color:
            #64748b;

        text-transform:
            uppercase;

        white-space:
            nowrap;

    }


    .table td {

        vertical-align:
            middle;

    }


    .report-actions {

        display:
            flex;

        gap:
            10px;

        margin-bottom:
            20px;

    }


    @media (
        max-width: 767.98px
    ) {

        .report-container {

            padding:
                14px;

        }


        .report-header {

            padding:
                20px;

        }


        .report-header h1 {

            font-size:
                24px;

        }


        .summary-value {

            font-size:
                20px;

        }


        .report-actions {

            flex-direction:
                column;

        }


        .report-actions .btn {

            width:
                100%;

        }

    }


    @media print {

        body {

            background:
                #ffffff;

        }


        .report-container {

            max-width:
                none;

            padding:
                0;

        }


        .report-actions {

            display:
                none;

        }


        .report-header {

            color:
                #000000;

            background:
                #ffffff;

            border:
                1px solid
                #cccccc;

        }


        .report-card {

            box-shadow:
                none;

            border:
                1px solid
                #dddddd;

        }

    }

</style>

</head>


<body>


<div class="report-container">


    <!-- Report Actions -->

    <div class="report-actions">

        <a
            href="<?= BASE_URL ?>reports/"
            class="btn btn-outline-secondary"
        >

            <i
                class="
                    bi
                    bi-arrow-left
                    me-1
                "
            ></i>

            Back to Reports

        </a>


        <button
            type="button"
            class="btn btn-success"
            onclick="window.print()"
        >

            <i
                class="
                    bi
                    bi-printer
                    me-1
                "
            ></i>

            Print Report

        </button>

    </div>


    <!-- Report Header -->

    <div class="report-header">

        <h1>

            Financial Report

        </h1>


        <div class="report-period">

            <?= e(APP_NAME) ?>

            <br>

            Reporting Period:

            <strong>

                <?= e(
                    $reportStart
                ) ?>

                -

                <?= e(
                    $reportEnd
                ) ?>

            </strong>

        </div>

    </div>


    <!-- Summary -->

    <div
        class="
            row
            g-3
            mb-4
        "
    >


        <!-- Current Balance -->

        <div
            class="
                col-12
                col-md-6
                col-xl-3
            "
        >

            <div class="card report-card h-100">

                <div class="card-body">

                    <div class="text-muted small">

                        Current Balance

                    </div>


                    <div
                        class="
                            summary-value
                            mt-2
                        "
                    >

                        <?= generatedReportMoney(
                            $totalBalance
                        ) ?>

                    </div>

                </div>

            </div>

        </div>


        <!-- Income -->

        <div
            class="
                col-6
                col-md-6
                col-xl-3
            "
        >

            <div class="card report-card h-100">

                <div class="card-body">

                    <div class="text-muted small">

                        Total Income

                    </div>


                    <div
                        class="
                            summary-value
                            income
                            mt-2
                        "
                    >

                        <?= generatedReportMoney(
                            $totalIncome
                        ) ?>

                    </div>

                </div>

            </div>

        </div>


        <!-- Expenses -->

        <div
            class="
                col-6
                col-md-6
                col-xl-3
            "
        >

            <div class="card report-card h-100">

                <div class="card-body">

                    <div class="text-muted small">

                        Total Expenses

                    </div>


                    <div
                        class="
                            summary-value
                            expense
                            mt-2
                        "
                    >

                        <?= generatedReportMoney(
                            $totalExpense
                        ) ?>

                    </div>

                </div>

            </div>

        </div>


        <!-- Net Cash Flow -->

        <div
            class="
                col-12
                col-md-6
                col-xl-3
            "
        >

            <div class="card report-card h-100">

                <div class="card-body">

                    <div class="text-muted small">

                        Net Cash Flow

                    </div>


                    <div
                        class="
                            summary-value
                            mt-2
                            <?= $netCashFlow >= 0
                                ? 'flow-positive'
                                : 'flow-negative'
                            ?>"
                    >

                        <?= generatedReportMoney(
                            $netCashFlow
                        ) ?>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- Adjustments -->

    <div class="card report-card">

        <div class="card-body">

            <h5 class="fw-bold mb-3">

                Adjustments

            </h5>


            <div
                class="
                    summary-value
                    adjustment
                "
            >

                <?= generatedReportMoney(
                    $totalAdjustment
                ) ?>

            </div>

        </div>

    </div>

<!-- =====================================================
     EXPENSES BY CATEGORY
====================================================== -->

<div
    class="
        card
        report-card
        mb-4
    "
>

    <div
        class="
            card-body
        "
    >

        <div
            class="
                d-flex
                justify-content-between
                align-items-center
                mb-3
            "
        >

            <h5
                class="
                    fw-bold
                    mb-0
                "
            >

                Expenses by Category

            </h5>


            <span
                class="
                    text-muted
                    small
                "
            >

                Highest Expense

            </span>

        </div>


        <?php if (
            empty($categoryExpenses)
        ): ?>

            <div
                class="
                    text-center
                    text-muted
                    py-4
                "
            >

                <i
                    class="
                        bi
                        bi-bar-chart
                        fs-1
                    "
                ></i>


                <p
                    class="
                        mb-0
                        mt-2
                    "
                >

                    No expense data found
                    for this period.

                </p>

            </div>

        <?php else: ?>

            <div
                style="
                    position: relative;
                    height: 420px;
                "
            >

                <canvas
                    id="expenseCategoryChart"
                ></canvas>

            </div>


            <?php

                $highestCategory =
                    $categoryExpenses[0];

            ?>

            <div
                class="
                    alert
                    alert-light
                    border
                    mt-3
                    mb-0
                "
            >

                <i
                    class="
                        bi
                        bi-trophy
                        me-1
                    "
                ></i>

                <strong>
                    Highest Expense:
                </strong>

                <?= e(
                    $highestCategory[
                        'category_name'
                    ]
                ) ?>

                —

                <?= generatedReportMoney(
                    (float)
                    $highestCategory[
                        'total_expense'
                    ]
                ) ?>

            </div>

        <?php endif; ?>

    </div>

</div>


    <!-- Wallet Breakdown -->

    <div class="card report-card">

        <div class="card-body">

            <h5 class="fw-bold mb-3">

                Wallet Breakdown

            </h5>


            <?php if (
                empty($wallets)
            ): ?>

                <p class="text-muted mb-0">

                    No active wallets found.

                </p>

            <?php else: ?>

                <div class="table-responsive">

                    <table
                        class="
                            table
                            table-hover
                            mb-0
                        "
                    >

                        <thead>

                            <tr>

                                <th>
                                    Wallet
                                </th>

                                <th>
                                    Type
                                </th>

                                <th
                                    class="text-end"
                                >
                                    Current Balance
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach (
                                $wallets
                                as $wallet
                            ): ?>

                                <tr>

                                    <td>

                                        <strong>

                                            <?= e(
                                                $wallet[
                                                    'name'
                                                ]
                                            ) ?>

                                        </strong>

                                    </td>


                                    <td>

                                        <?= e(
                                            $wallet[
                                                'wallet_type'
                                            ]
                                        ) ?>

                                    </td>


                                    <td
                                        class="
                                            text-end
                                            fw-bold
                                        "
                                    >

                                        <?= generatedReportMoney(
                                            (float)
                                            $wallet[
                                                'current_balance'
                                            ],
                                            $wallet[
                                                'currency'
                                            ]
                                        ) ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>


    <!-- Transactions -->

    <div class="card report-card">

        <div class="card-body">

            <div
                class="
                    d-flex
                    justify-content-between
                    align-items-center
                    mb-3
                "
            >

                <h5 class="fw-bold mb-0">

                    Transactions

                </h5>


                <span class="text-muted small">

                    <?= count(
                        $transactions
                    ) ?>

                    transaction<?= count(
                        $transactions
                    ) === 1
                        ? ''
                        : 's'
                    ?>

                </span>

            </div>


            <?php if (
                empty($transactions)
            ): ?>

                <p class="text-muted mb-0">

                    No transactions found for this period.

                </p>

            <?php else: ?>

                <div class="table-responsive">

                    <table
                        class="
                            table
                            table-hover
                            mb-0
                        "
                    >

                        <thead>

                            <tr>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Description
                                </th>

                                <th>
                                    Wallet
                                </th>

                                <th>
                                    Category
                                </th>

                                <th>
                                    Type
                                </th>

                                <th
                                    class="text-end"
                                >
                                    Amount
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach (
                                $transactions
                                as $transaction
                            ): ?>

                                <tr>

                                    <td>

                                        <?= date(
                                            'M d, Y',
                                            strtotime(
                                                $transaction[
                                                    'transaction_date'
                                                ]
                                            )
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= e(
                                            $transaction[
                                                'description'
                                            ]
                                            ?:
                                            (
                                                $transaction[
                                                    'type'
                                                ]
                                                .
                                                ' transaction'
                                            )
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= e(
                                            $transaction[
                                                'wallet_name'
                                            ]
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= e(
                                            $transaction[
                                                'category_name'
                                            ] ??
                                            '—'
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= e(
                                            $transaction[
                                                'type'
                                            ]
                                        ) ?>

                                    </td>


                                    <td
                                        class="
                                            text-end
                                            fw-bold
                                        "
                                    >

                                        <?php if (
                                            $transaction[
                                                'type'
                                            ] === 'Income'
                                        ): ?>

                                            <span
                                                class="
                                                    income
                                                "
                                            >

                                                +

                                            </span>

                                        <?php elseif (
                                            $transaction[
                                                'type'
                                            ] === 'Expense'
                                        ): ?>

                                            <span
                                                class="
                                                    expense
                                                "
                                            >

                                                -

                                            </span>

                                        <?php elseif (
                                            $transaction[
                                                'type'
                                            ] === 'Adjustment'
                                        ): ?>

                                            <span
                                                class="
                                                    adjustment
                                                "
                                            >

                                                <?=
                                                (float)
                                                $transaction[
                                                    'amount'
                                                ] >= 0
                                                    ? '+'
                                                    : ''
                                                ?>

                                            </span>

                                        <?php endif; ?>


                                        <?= generatedReportMoney(
                                            abs(
                                                (float)
                                                $transaction[
                                                    'amount'
                                                ]
                                            )
                                        ) ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>


</div>

<script>

const expenseCategoryLabels =
    <?= json_encode(
        $categoryLabels,
        JSON_UNESCAPED_UNICODE
    ) ?>;


const expenseCategoryAmounts =
    <?= json_encode(
        $categoryAmounts
    ) ?>;


const expenseCategoryCanvas =
    document.getElementById(
        'expenseCategoryChart'
    );


if (
    expenseCategoryCanvas &&
    expenseCategoryLabels.length > 0
) {

    new Chart(
        expenseCategoryCanvas,
        {

            type: 'bar',

            data: {

                labels:
                    expenseCategoryLabels,

                datasets: [

                    {
                        label:
                            'Total Expenses',

                        data:
                            expenseCategoryAmounts,

                        borderWidth:
                            1
                    }

                ]

            },

            options: {

                responsive:
                    true,

                maintainAspectRatio:
                    false,

                indexAxis:
                    'y',

                layout: {

                    padding: {

                        right:
                            90

                    }

                },

                plugins: {

                    legend: {

                        display:
                            false

                    },

                    tooltip: {

                        callbacks: {

                            label:
                                function (
                                    context
                                ) {

                                    return (
                                        ' ₱' +
                                        Number(
                                            context.raw
                                        ).toLocaleString(
                                            'en-PH',
                                            {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2
                                            }
                                        )
                                    );

                                }

                        }

                    }

                },

                scales: {

                    x: {

                        beginAtZero:
                            true,

                        ticks: {

                            callback:
                                function (
                                    value
                                ) {

                                    return (
                                        '₱' +
                                        Number(
                                            value
                                        ).toLocaleString(
                                            'en-PH'
                                        )
                                    );

                                }

                        }

                    }

                }

            },

            plugins: [

                {

                    id:
                        'expenseAmountLabels',

                    afterDatasetsDraw:
                        function (
                            chart
                        ) {

                            const {
                                ctx
                            } = chart;

                            ctx.save();

                            chart.data.datasets
                                .forEach(
                                    function (
                                        dataset,
                                        datasetIndex
                                    ) {

                                        const meta =
                                            chart.getDatasetMeta(
                                                datasetIndex
                                            );


                                        meta.data.forEach(
                                            function (
                                                bar,
                                                index
                                            ) {

                                                const amount =
                                                    dataset.data[
                                                        index
                                                    ];


                                                const formattedAmount =
                                                    '₱' +
                                                    Number(
                                                        amount
                                                    ).toLocaleString(
                                                        'en-PH',
                                                        {
                                                            minimumFractionDigits: 2,
                                                            maximumFractionDigits: 2
                                                        }
                                                    );


                                                ctx.font =
                                                    '600 12px Arial';


                                                ctx.fillStyle =
                                                    '#334155';


                                                ctx.textAlign =
                                                    'left';


                                                ctx.textBaseline =
                                                    'middle';


                                                ctx.fillText(

                                                    formattedAmount,

                                                    bar.x + 8,

                                                    bar.y

                                                );

                                            }
                                        );

                                    }
                                );

                            ctx.restore();

                        }

                }

            ]

        }
    );

}

</script>

</body>

</html>