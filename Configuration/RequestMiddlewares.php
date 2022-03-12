<?php

return [
    'frontend' => [
        'user_registration/double_opt_in' => [
            'target' => \ACME\UserAccountExt\Middleware\DoubleOptInMiddleware::class,
            'before' => [
                'typo3/cms-frontend/eid',
            ],
        ],
    ],
];
