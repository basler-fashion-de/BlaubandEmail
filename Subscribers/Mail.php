<?php

namespace BlaubandEmail\Subscribers;

use BlaubandEmail\Models\LoggedMail;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Order\Order;

class Mail implements SubscriberInterface
{

    /** @var ModelManager */
    private $modelManager;

    /** @var null  */
    private $tempOrderMail;

    /**
     * Mail constructor.
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
        $this->tempOrderMail = null;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Components_Mail_Send' => 'onMailSend',
            'Shopware_Modules_Order_SendMail_BeforeSend' => 'prepareOrderNumber'
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
        try{
            /** @var \Enlight_Components_Mail $mail */
            $mail = $args->get('mail');

            $bcc = array_diff($mail->getRecipients(), $mail->getTo());

            $mailModel = new LoggedMail();
            $mailModel->setSubject($mail->getSubject());
            $mailModel->setFrom($mail->getFrom());
            $mailModel->setBcc(implode(', ', $bcc));
            $mailModel->setTo(implode(', ', $mail->getTo()));

            if (strlen($mail->getPlainBody()) === 0) {
                $mailModel->setBody($mail->getPlainBodyText());
                $mailModel->setIsHtml(false);
            } else {
                $mailModel->setBody($mail->getPlainBody());
                $mailModel->setIsHtml(true);
            }

            //Versuchen weitere Daten zu bekommen um diese zu ergänzen

            //Check OrderId
            if(isset($_POST['orderId'])){
                $order = $this->modelManager->find(Order::class, $_POST['orderId']);

                if(!empty($order)){
                    $mailModel->setOrder($order);
                    $mailModel->setCustomer($order->getCustomer());
                    $this->modelManager->persist($mailModel);
                    $this->modelManager->flush($mailModel);
                    return;
                }
            }

            //Check OrderNumber
            $orderNumber = false;
            if(isset($args->getSubject()->sOrderNumber)){
                $orderNumber = $args->getSubject()->sOrderNumber;
            }

            if(isset($_POST['orderNumber'])){
                $orderNumber = $_POST['orderNumber'];
            }

            if($orderNumber !== false){
                $repo = $this->modelManager->getRepository(Order::class);
                $order = $repo->findBy(['number' => $orderNumber]);
                $order = array_shift($order);

                if(!empty($order)){
                    $mailModel->setOrder($order);
                    $mailModel->setCustomer($order->getCustomer());
                    $this->modelManager->persist($mailModel);
                    $this->modelManager->flush($mailModel);
                    return;
                }
            }

            //Check CustomerMail
            $customerEmail = false;
            //Registrierung
            if(isset($_POST['register']['personal']['email'])){
                $customerEmail = $_POST['register']['personal']['email'];
            }

            //Newsletter
            if(isset($_POST['newsletter'])){
                $customerEmail = $_POST['newsletter'];
            }

            if($customerEmail !== false){
                $repo = $this->modelManager->getRepository(Customer::class);
                $customer = $repo->findBy(['email' => $customerEmail]);
                $customer = array_shift($customer);

                if(!empty($customer)){
                    $mailModel->setCustomer($customer);
                    $this->modelManager->persist($mailModel);
                    $this->modelManager->flush($mailModel);
                    return;
                }
            }

            $this->modelManager->persist($mailModel);
            $this->modelManager->flush($mailModel);
        }catch (\Exception $e){
            Shopware()->Container()->get('pluginlogger')->addInfo('Blauband Mail: '.$e->getMessage());
        }
    }
}