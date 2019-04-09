<?php

namespace ErdmannFreunde\ContaoNewsNewsletterBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\NewsBundle\ContaoNewsBundle;
use Contao\NewsletterBundle\ContaoNewsletterBundle;
use ErdmannFreunde\ContaoNewsNewsletterBundle\ErdmannFreundeContaoNewsNewsletterBundle;

class Plugin implements BundlePluginInterface
{

    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ErdmannFreundeContaoNewsNewsletterBundle::class)
                ->setLoadAfter(
                    [
                        ContaoCoreBundle::class,
                        ContaoNewsBundle::class,
                        ContaoNewsletterBundle::class,
                        'haste',
                        'notification_center'
                    ]
                )
        ];
    }
}
