<?php

namespace ErdmannFreunde\ContaoNewsNewsletterBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use ErdmannFreunde\ContaoNewsNewsletterBundle\ErdmannFreundeContaoNewsNewsletterBundle;

class Plugin implements BundlePluginInterface
{

    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(ErdmannFreundeContaoNewsNewsletterBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
                ->setLoadAfter(['notification_center'])
            ,
        ];
    }

}
