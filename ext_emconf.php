<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Versatile and Interactive Display - List Component',
    'description' => 'Generic listing of records with versatile ways of interacting with the data, e.g. advanced filter, inline editing, mass editing, ... Veni, vidi, vici!',
    'category' => 'module',
    'author' => 'Fabien Udriot',
    'author_email' => 'fabien@ecodev.ch',
    'module' => '',
    'state' => 'stable',
    'version' => '2.6.0-dev',
    'autoload' => [
        'psr-4' => ['Fab\\Vidi\\' => 'Classes']
    ],
    'constraints' =>
        [
            'depends' =>
                [
                    'typo3' => '7.6.0-8.99.99',
                ],
            'conflicts' =>
                [
                ],
            'suggests' =>
                [
                ],
        ]
];
