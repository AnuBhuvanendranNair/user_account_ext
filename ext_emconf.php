<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'User Account Handler',
    'description' => 'A TYPO3 extension which facilitates user registration and associated functionalities incorporating TYPO3\'s core fe_login extension.',
    'category' => 'fe',
    'author' => 'Anu Bhuvanendran Nair',
    'author_email' => 'anu93nair@gmail.com',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
