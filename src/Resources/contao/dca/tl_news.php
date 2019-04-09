<?php


$GLOBALS['TL_DCA']['tl_news']['fields']['newsletter'] = [
    'label'   => &$GLOBALS['TL_LANG']['tl_news']['newsletter'],
    'exclude' => true,
    'eval'    => ['doNotCopy' => true],
    'sql'     => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_news']['list']['operations']['newsletter'] = [
    'label'           => &$GLOBALS['TL_LANG']['tl_news']['sendNewsletter'],
    'icon'            => 'bundles/erdmannfreundecontaonewsnewsletter/newsletter.png',
    'button_callback' => [
        'erdmannfreunde.contao_news_newsletter.listener.data_container.news.newsletter_operation_button',
        'onButtonCallback'
    ]
];
