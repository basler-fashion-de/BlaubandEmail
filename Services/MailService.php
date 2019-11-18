<?php

namespace BlaubandEmail\Services;

use BlaubandEmail\Models\LoggedMail;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\CachedConfigReader;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Mail\Mail;
use Shopware\Models\Order\Document\Document;
use Shopware\Models\Order\Order;
use Shopware\Models\User\User;
use Shopware_Components_TemplateMail;
use Zend_Mime;
use Zend_Mime_Part;

class MailService implements MailServiceInterface
{
    /** @var ModelManager */
    private $modelManager;

    /** @var CachedConfigReader */
    private $cachedConfigReader;

    /** @var \Enlight_Controller_Front */
    private $frontProxy;

    /** @var Shopware_Components_TemplateMail $templateMail */
    private $templateMail;

    /** @var Order */
    protected $order = null;

    /** @var User */
    protected $customer = null;

    //If more orders (Batch)
    private $orderCounter = 0;

    /**
     * Mail constructor.
     * @param ModelManager $modelManager
     */
    public function __construct(
        ModelManager $modelManager,
        CachedConfigReader $cachedConfigReader,
        \Enlight_Controller_Front $frontProxy,
        $templateMail
    )
    {
        $this->modelManager = $modelManager;
        $this->cachedConfigReader = $cachedConfigReader;
        $this->frontProxy = $frontProxy;
        $this->templateMail = $templateMail;

    }

    /**
     * @param \Enlight_Components_Mail $mail
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
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

        $mailModel->setAttachments(json_encode($mail->getParts()));

        if (empty($mailModel->getTo())) {
            $mailModel->setTo('-');
        }

        if (empty($mailModel->getSubject())) {
            $mailModel->setSubject('-');
        }

        if (empty($mailModel->getBody())) {
            $mailModel->setBody('-');
        }

        $this->modelManager->persist($mailModel);
        $this->modelManager->flush($mailModel);
    }

    /**
     * @param $to
     * @param $bcc
     * @param $context
     * @param $isHtml
     * @param array $files
     * @param string $template
     * @throws \Enlight_Exception
     */
    public function sendMail($to, $bcc, $context, $isHtml, $files = [], $template = 'blaubandMail')
    {
        /* @var $mailModel \Shopware\Models\Mail\Mail */
        $mailModel = $this->modelManager->getRepository(Mail::class)->findOneBy(['name' => $template]);
        $mailModel->setIsHtml($isHtml);

        $mail = $this->templateMail->createMail($mailModel, $context);
        $mail->addTo($to, $to);

        if (!empty($bcc)) {
            $mail->addBcc($bcc);
        }

        foreach ($files as $file) {
            $content = file_get_contents($file['tmp_name']);
            $zendAttachment = new Zend_Mime_Part($content);
            $zendAttachment->type = $file['type'];
            $zendAttachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
            $zendAttachment->encoding = Zend_Mime::ENCODING_BASE64;
            $zendAttachment->filename = $file['name'];

            $mail->addAttachment($zendAttachment);
        }

        $mail->send();
    }

    /**
     * This function try to define variables by POST or GET Parameter
     */
    private function findVariables()
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

        //BatchProcess (1 Order)
        if (isset($_POST['number']) && $this->frontProxy->Request()->getActionName() == 'batchProcess') {
            $this->setOrderByNumber($_POST['number']);
            if (null !== $this->order) {
                $this->customer = $this->order->getCustomer();
                return;
            }
        }

        //BatchProcess (Multiple Orders)
        if (isset($_POST['orders'])) {
            $this->setOrderByNumber($_POST['orders'][$this->orderCounter]['number']);
            $this->orderCounter++;
            if (null !== $this->order) {
                $this->customer = $this->order->getCustomer();
                return;
            }
        }

        //Registration
        if (isset($_POST['register']['personal']['email'])) {
            $this->setUserByEmail($_POST['register']['personal']['email']);
        }
    }

    private function skipMail(\Enlight_Components_Mail $mail)
    {
        $config = $this->cachedConfigReader->getByPluginName('BlaubandEmail');

        if (isset($_POST['testmail'])) {
            // Bei Testmails keine Speicherung
            return true;
        }

        if (!$config['SAVE_NEWSLETTER_MAILS']) {
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
    private function setOrderByNumber($orderNumber)
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
    private function setUserByEmail($customerEmail)
    {
        $repo = $this->modelManager->getRepository(Customer::class);
        $customer = $repo->findBy(['email' => $customerEmail]);
        $customer = array_shift($customer);

        if (!empty($customer)) {
            $this->customer = $customer;
            return;
        }
    }
}
