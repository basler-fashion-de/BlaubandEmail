<?php

use Shopware\Components\CSRFWhitelistAware;

class Shopware_Controllers_Backend_BlaubandStore extends \Enlight_Controller_Action implements CSRFWhitelistAware
{
    public function getWhitelistedCSRFActions()
    {
        return [
            'index',
        ];
    }

    public function preDispatch()
    {
        $this->view->addTemplateDir(__DIR__ . "/../../Resources/views");
    }

    public function indexAction()
    {
        /** @var \BlaubandEmail\Services\ShopwareStoreService $storeService */
        $storeService = $this->get('blauband_email.services.shopware_store_service');
        $this->view->assign('eksPlugins', $storeService->getEksPlugins());
        $this->view->assign('blaubandPlugins', $storeService->getBlaubandPlugins());
    }
}