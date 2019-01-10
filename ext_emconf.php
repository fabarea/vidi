<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Versatile and Interactive Display - List Component',
    'description' => 'Generic listing of records with versatile ways of interacting with the data, e.g. advanced filter, inline editing, mass editing, ... Veni, vidi, vici!',
    'category' => 'module',
    'author' => 'Fabien Udriot',
    'author_email' => 'fabien@ecodev.ch',
    'module' => '',
    'state' => 'stable',
    'version' => '3.0.0',
    'autoload' => [
        'psr-4' => ['Fab\\Vidi\\' => 'Classes']
    ],
    'constraints' =>
        [
            'depends' =>
                [
                    'typo3' => '9.5.0-9.5.99',
                ],
            'conflicts' =>
                [
                ],
            'suggests' =>
                [
                ],
        ]
];
