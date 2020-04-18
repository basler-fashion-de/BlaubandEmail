<?php

namespace BlaubandEmail\Subscribers;

use Enlight\Event\SubscriberInterface;

class ExtendCustomer implements SubscriberInterface
{
    /** @var string */
    private $pluginDirectory;

    /**
     * ExtendCustomer constructor.
     * @param string $pluginDirectory
     */
    public function __construct($pluginDirectory)
    {
        $this->pluginDirectory = $pluginDirectory;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Customer' => 'onPostDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Order' => 'onPostDispatch'
        ];
    }

    public function onPostDispatch(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Backend_Customer $subject */
        $subject = $args->getSubject();

        $view = $subject->View();
        $request = $subject->Request();

        $view->addTemplateDir($this->pluginDirectory . '/Resources/views');

        if ($request->getActionName() == 'index') {
            $view->extendsTemplate('backend/blauband_email/app.js');
        }

        if ($request->getActionName() == 'load' && strtolower($request->getControllerName()) == 'customer') {
            $view->extendsTemplate('backend/blauband_email/view/detail/window.js');
        }

        if ($request->getActionName() == 'load' && strtolower($request->getControllerName()) == 'order') {
            $view->extendsTemplate('backend/blauband_email/view/order/window.js');
        }
    }
}