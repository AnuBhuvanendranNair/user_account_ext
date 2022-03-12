<?php
return [
    'ctrl' => [
        'title' => 'Double Opt In Form Einträge',
        'label' => 'email',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            ],
        'searchFields' => 'email,firstname,lastname,verified',
        'iconfile'=>'EXT:core/Resources/Public/Icons/T3Icons/content/content-elements-mailform.svg'

    ],
    'interface' => [
        'maxDBListItems' => 30,
        'maxSingleDBListItems' => 50
    ],
    'types' => [
        '0' => ['showitem' => 'deleted,hidden,pid,email,firstname,lastname,hash,verified'],
    ],
    'columns' => [
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        0 => '',
                        1 => '',
                        'invertStateDisplay' => true
                    ]
                ],
            ]
        ],

        'email' => [
            'exclude' => false,
            'label' => 'LLL:EXT:user_account_ext/Resources/Private/Language/locallang_db.xlf:email',
            'config' => [
                'type' => 'input',
                'size' => '255',
                'eval' => 'trim,required'
            ],
        ],
        'firstname' => [
            'exclude' => false,
            'label' => 'LLL:EXT:user_account_ext/Resources/Private/Language/locallang_db.xlf:firstname',
            'config' => [
                'type' => 'input',
                'size' => '255',
                'eval' => 'trim,required'
            ]
        ],
        'lastname' => [
            'exclude' => false,
            'label' => 'LLL:EXT:user_account_ext/Resources/Private/Language/locallang_db.xlf:lastname',
            'config' => [
                'type' => 'input',
                'size' => '255',
                'eval' => 'trim,required'
            ]
        ],
        'verified' => [
            'exclued' => false,
            'label' => 'LLL:EXT:user_account_ext/Resources/Private/Language/locallang_db.xlf:verified',
            'config' => [
                'type' => 'check',
            ]
        ]

    ],
];
