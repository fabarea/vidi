<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$tca = [
    'grid' => [
        'excluded_fields' => 'image, imagewidth, imageorient, imagecols, imageborder, image_noRows, image_effects, image_compression, tx_impexp_origuid, image_zoom,
                              spaceAfter, spaceBefore,
                              uploads_description, uploads_type,
                              media, assets, table_caption, table_delimiter, table_enclosure, table_header_position, table_tfoot, table_bgColor, table_border, table_cellpadding, table_cellspacing,
                              icon, icon_position, icon_size, icon_type, icon_color, icon_background, uploads_description, uploads_type,
                              header_link, header_layout, header_position,
                              bullets_type, section_frame,
                              target, linkToTop, menu_type, list_type, select_key,
                              file_collections, filelink_size, filelink_sorting,
                              external_media_ratio, external_media_source',
        'columns' => [
            '__checkbox' => [
                'renderer' => new Fab\Vidi\Grid\CheckBoxRenderer(),
            ],
            'uid' => [
                'visible' => false,
                'label' => 'Id',
                'width' => '5px',
            ],
            'header' => [
                'editable' => true,
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/tt_content.xlf:header',
            ],
            'tstamp' => [
                'visible' => false,
                'format' => 'Fab\Vidi\Formatter\Date',
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:tstamp',
            ],
            'crdate' => [
                'visible' => false,
                'format' => 'Fab\Vidi\Formatter\Date',
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:crdate',
            ],
            'hidden' => [
                'renderer' => 'Fab\Vidi\Grid\VisibilityRenderer',
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:active',
                'width' => '3%',
            ],
            '__buttons' => [
                'renderer' => new Fab\Vidi\Grid\ButtonGroupRenderer(),
            ],
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA']['tt_content'], $tca);
