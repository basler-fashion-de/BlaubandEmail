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

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (position defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     * <code>
     * return array(
     *     'eventName0' => 'callback0',
     *     'eventName1' => array('callback1'),
     *     'eventName2' => array('callback2', 10),
     *     'eventName3' => array(
     *         array('callback3_0', 5),
     *         array('callback3_1'),
     *         array('callback3_2')
     *     )
     * );
     *
     * </code>
     *
     * @return array The event names to listen to
     */
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