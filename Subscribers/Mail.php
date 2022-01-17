<?php

namespace BlaubandEmail\Subscribers;

use BlaubandEmail\Services\MailService;
use BlaubandEmail\Services\MailServiceInterface;
use Enlight\Event\SubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Mail implements SubscriberInterface
{
    /** @var MailServiceInterface */
    private $mailService;

    public function __construct(MailServiceInterface $mailService)
    {
        $this->mailService = $mailService;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Components_Mail_Send' => 'onMailSend',
            'Shopware_Modules_Order_SendMail_BeforeSend' => 'prepareOrderNumber',
        ];
    }

    public function prepareOrderNumber(\Enlight_Event_EventArgs $args)
    {
        $_POST['orderNumber'] = $args->get('context')['sOrderNumber'];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onMailSend(\Enlight_Event_EventArgs $args)
    {
        try {
            /** @var \Enlight_Components_Mail $mail */
            $mail = $args->get('mail');

            if (isset($args->getSubject()->sOrderNumber)) {
                $_POST['orderNumber'] = $args->getSubject()->sOrderNumber;
            }

            $this->mailService->saveMail($mail);

        } catch (\Exception $e) {
            Shopware()->Container()->get('pluginlogger')->addRecord(200, 'Blauband Mail: ' . $e->getMessage());
        }
    }
}