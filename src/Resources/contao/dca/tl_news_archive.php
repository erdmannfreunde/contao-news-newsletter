<?php


\System::loadLanguageFile('tl_module');

$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['__selector__'][] = 'newsletter';

$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default'] = str_replace(
    'jumpTo;',
    'jumpTo;{newsletter_legend:hide},newsletter;',
    $GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default']
);

$GLOBALS['TL_DCA']['tl_news_archive']['subpalettes']['newsletter'] = 'newsletter_channel,nc_notification';

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['newsletter'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_news_archive']['newsletter'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['submitOnChange' => true],
    'sql'       => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['newsletter_channel'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_news_archive']['newsletter_channel'],
    'exclude'    => true,
    'inputType'  => 'select',
    'foreignKey' => 'tl_newsletter_channel.title',
    'eval'       => ['mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql'        => "int(10) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['nc_notification'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_module']['nc_notification'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => [
        'erdmannfreunde.contao_news_newsletter.listener.data_container.news_archive.notification_choices',
        'onOptionsCallback'
    ],
    'eval'             => ['mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql'              => "int(10) unsigned NOT NULL default '0'",
    'relation'         => ['type' => 'hasOne', 'load' => 'lazy', 'table' => 'tl_nc_notification'],
];
