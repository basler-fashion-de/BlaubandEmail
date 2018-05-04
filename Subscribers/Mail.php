<?php

namespace BlaubandEmail\Subscribers;

use BlaubandEmail\Models\LoggedMail;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;

class Mail implements SubscriberInterface
{

    /** @var ModelManager */
    private $modelManager;

    /**
     * Mail constructor.
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Components_Mail_Send' => 'onMailSend'
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onMailSend(\Enlight_Event_EventArgs $args)
    {
        try{
            /** @var \Enlight_Components_Mail $mail */
            $mail = $args->get('mail');

            $mailModel = new LoggedMail();
            $mailModel->setSubject($mail->getSubject());
            $mailModel->setFrom($mail->getFrom());
            $mailModel->setTo(implode(', ', $mail->getTo()));

            if (strlen($mail->getPlainBody()) === 0) {
                $mailModel->setBody($mail->getPlainBodyText());
                $mailModel->setIsHtml(false);
            } else {
                $mailModel->setBody($mail->getPlainBody());
                $mailModel->setIsHtml(true);
            }

            if(isset($_POST['orderId'])){
                $order = $this->modelManager->find(Order::class, $_POST['orderId']);

                if(!empty($order)){
                    $mailModel->setOrder($order);
                    $mailModel->setCustomer($order->getCustomer());
                }
            }

            $this->modelManager->persist($mailModel);
            $this->modelManager->flush($mailModel);
        }catch (\Exception $e){
            //Do nothing
        }
    }

}