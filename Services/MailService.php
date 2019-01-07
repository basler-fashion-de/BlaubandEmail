<?php

namespace BlaubandEmail\Services;

use BlaubandEmail\Models\LoggedMail;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\CachedConfigReader;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Order\Document\Document;
use Shopware\Models\Order\Order;
use Shopware\Models\User\User;

class MailService
{
    /** @var ModelManager */
    private $modelManager;

    /** @var CachedConfigReader */
    private $cachedConfigReader;

    /** @var Order */
    protected $order = null;

    /** @var User */
    protected $customer = null;

    /**
     * Mail constructor.
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager, CachedConfigReader $cachedConfigReader)
    {
        $this->modelManager = $modelManager;
        $this->cachedConfigReader = $cachedConfigReader;
    }

    public function saveMail(\Enlight_Components_Mail $mail)
    {
        if ($this->skipMail($mail)) {
            return;
        }

        $this->findVariables();

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

        if (null !== $this->order) {
            $mailModel->setOrder($this->order);
        }

        if (null !== $this->customer) {
            $mailModel->setCustomer($this->customer);
        }

        $this->modelManager->persist($mailModel);
        $this->modelManager->flush($mailModel);
    }

    /**
     * This function try to define variables by POST or GET Parameter
     */
    public function findVariables()
    {
        if (isset($_POST['documentId'])) {
            $dokument = $this->modelManager->find(Document::class, $_POST['documentId']);

            if (!empty($dokument)) {
                $this->order = $dokument->getOrder();
                $this->customer = $dokument->getOrder()->getCustomer();
                return;
            }
        }

        if (isset($_POST['orderId'])) {
            $order = $this->modelManager->find(Order::class, $_POST['orderId']);

            if (!empty($order)) {
                $this->order = $order;
                $this->customer = $order->getCustomer();
                return;
            }
        }

        if (isset($_POST['orderNumber'])) {
            $this->setOrderByNumber($_POST['orderNumber']);
            if (null !== $this->order) {
                $this->customer = $this->order->getCustomer();
                return;
            }
        }

        if (isset($_POST['register']['personal']['email'])) {
            $this->setUserByEmail($_POST['register']['personal']['email']);
        }
    }

    public function skipMail(\Enlight_Components_Mail $mail)
    {
        $config = $this->cachedConfigReader->getByPluginName('BlaubandEmail');

        if (isset($_POST['testmail'])) {
            // Bei Testmails keine Speicherung
            return true;
        }

        if(!$config['SAVE_NEWSLETTER_MAILS']){
            // Bei Newsletter keine Speicherung
            if (isset($_POST['newsletter'])) {
                return true;
            }

            if (
                strtolower($_GET['module']) == 'backend' &&
                strtolower($_GET['controller']) == 'newsletter' &&
                strtolower($_GET['action']) == 'cron'
            ) {
                // Der Newsletter wird nicht über ein normlen CronJob gestartet sondern immer über eine URL.
                // Deshalb ist dieser Weg erstmal ok
                return true;
            }
        }


        return false;
    }

    /**
     * @param $orderNumber
     */
    public function setOrderByNumber($orderNumber)
    {
        $repo = $this->modelManager->getRepository(Order::class);
        $order = $repo->findBy(['number' => $orderNumber]);
        $order = array_shift($order);

        if (!empty($order)) {
            $this->order = $order;
            return;
        }
    }

    /**
     * @param $customerEmail
     */
    public function setUserByEmail($customerEmail)
    {
        $repo = $this->modelManager->getRepository(Customer::class);
        $customer = $repo->findBy(['email' => $customerEmail]);
        $customer = array_shift($customer);

        if (!empty($customer)) {
            $this->customer = $customer;
            return;
        }
    }

    /**
     * @param Order $order
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @param User $customer
     */
    public function setCustomer(User $customer)
    {
        $this->customer = $customer;
    }


}