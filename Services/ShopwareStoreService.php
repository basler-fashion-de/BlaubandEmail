<?php

namespace BlaubandEmail\Services;

use Shopware\Bundle\PluginInstallerBundle\Context\ListingRequest;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginViewService;

class ShopwareStoreService
{
    private $auth;

    private $shopwareVersion;

    /** @var PluginViewService */
    private $pluginViewService;

    public function __construct($container, $pluginViewService)
    {
        $this->auth = $container->get('auth');
        $this->pluginViewService = $pluginViewService;

        if($container->hasParameter('shopware.release.version')){
            $this->shopwareVersion = $container->getParameter('shopware.release.version');
        }else{
            $this->shopwareVersion = \Shopware::VERSION;
        }
    }

    public function getPluginsBySearchTerm($searchTerm)
    {
        $filter[] = ['property' => 'search', 'value' => $searchTerm];
        $sort[] = ['property' => 'release'];

        $resultTotal = 1;

        for ($i = 0; $i < $resultTotal; $i = $i + 10) {
            $context = new ListingRequest(
                $this->auth->getIdentity()->locale->getLocale(),
                $this->shopwareVersion,
                $i,
                $i + 10,
                $filter,
                $sort
            );

            /** @var \Shopware\Bundle\PluginInstallerBundle\Struct\ListingResultStruct $listingResult */
            $listingResult = $this->pluginViewService->getStoreListing($context);
            $resultTotal = $listingResult->getTotalCount();
            $pluginArray = [];

            /** @var \Shopware\Bundle\PluginInstallerBundle\Struct\PluginStruct $plugin */
            foreach ($listingResult->getPlugins() as $plugin) {
                $pluginArray[] = $plugin->jsonSerialize();
            }
        }

        return $pluginArray;
    }

    public function getEksPlugins()
    {
        //Es fehlen noch EKS Plugins
    }

    public function getBlaubandPlugins()
    {
        $blacklist = ['BlaubandEmail'];
        $plugins = $this->getPluginsBySearchTerm('Blauband');

        return $this->filterByBlackList($plugins, $blacklist);
    }

    private function filterByBlackList($plugins, $blacklist)
    {
        $return = [];

        foreach ($plugins as $plugin) {
            if (!in_array($plugin['technicalName'], $blacklist)) {
                $return[] = $plugin;
            }
        }

        return $return;
    }
}