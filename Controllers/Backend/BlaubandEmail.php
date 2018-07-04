<?php

use BlaubandEmail\Models\LoggedMail;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use Shopware\Models\Shop\Shop;

class Shopware_Controllers_Backend_BlaubandEmail extends \Enlight_Controller_Action implements CSRFWhitelistAware
{
    private $customSnippetNamespace = 'blauband/mail_custom_snippets';

    /* @var $db \Doctrine\DBAL\Connection */
    private $db;

    /** @var Shopware_Components_Config $config */
    private $config;

    /** @var array $pluginConfig */
    private $pluginConfig;

    /** @var ModelManager $modelManager */
    private $modelManager;

    /** @var \Shopware_Components_Snippet_Manager $snippets */
    private $snippets;

    /** @var Shopware_Components_TemplateMail $templateMail */
    private $templateMail;

    public function getWhitelistedCSRFActions()
    {
        return [
            'index',
            'send',
            'executeSend',
        ];
    }

    public function preDispatch()
    {
        $this->db = $this->container->get('dbal_connection');
        $this->config = $this->container->get('config');
        $this->pluginConfig = $this->container->get('shopware.plugin.cached_config_reader')->getByPluginName('BlaubandEmail');
        $this->modelManager = $this->container->get('models');
        $this->snippets = $this->container->get('snippets');

        $this->view->addTemplateDir(__DIR__ . "/../../Resources/views");
    }

    public function indexAction()
    {
        $isOwnFrame = $this->request->getParam('frame') === '1';
        $customerId = $this->request->getParam('customerId');
        $orderId = $this->request->getParam('orderId');

        $limit = 20;
        $offset = empty(
        $this->request->getParam('offset')) ?
            0 :
            $this->request->getParam('offset');

        $repository = $this->modelManager->getRepository(LoggedMail::class);
        $criteria = [];
        $orderBy = ['createDate' => 'DESC'];

        if ($customerId) {
            $criteria['customerId'] = $customerId;
        }

        if ($orderId) {
            $criteria['orderId'] = $orderId;
        }

        $allMails = $repository->findBy($criteria, $orderBy, $limit, $offset);
        $total = $repository->findBy($criteria);

        $this->view->assign('isOwnFrame', $isOwnFrame);
        $this->view->assign('customerId', $customerId);
        $this->view->assign('orderId', $orderId);
        $this->view->assign('mails', $allMails);
        $this->view->assign('offset', $offset);
        $this->view->assign('limit', $limit);
        $this->view->assign('total', $total);
    }

    public function sendAction()
    {
        $orderId = $this->request->getParam('orderId');
        $isOwnFrame = $this->request->getParam('frame') === '1';
        $customerId = $this->request->getParam('customerId');

        if (empty($customerId)) {
            /** @var Order $o */
            $o = $this->modelManager->find(Order::class, $orderId);
            $customerId = $o->getCustomer()->getId();
        }

        $customer = $this->db->fetchAll('SELECT * FROM s_user WHERE id = :id', ['id' => $customerId]);
        $customer = $customer[0];

        $owner = $this->config->get('masterdata::mail');

        $currentUser = $this->container->get('auth')->getAdapter(0)->getResultRowObject('email');
        $currentUser = $currentUser->email;

        $users = $this->db->fetchAll(
            'SELECT email FROM s_core_auth WHERE email != ":currentUser" && email != ":owner" ORDER BY email',
            ['currentUser' => $currentUser, 'owner' => $owner]
        );

        $list = [$owner, $currentUser];
        foreach ($users as $user) {
            $list[] = $user['email'];
        }
        $list = array_unique($list);

        $repository = $this->modelManager->getRepository(Shop::class);
        $shops = $repository->findAll();
        $customSnippets = [];
        foreach ($shops as $shop){
            $this->snippets->setShop($shop);

            $name = $shop->getName().' / '.$shop->getLocale()->getLocale();
            $data = $this->snippets->getNamespace($this->customSnippetNamespace)->toArray();
            $customSnippets[$shop->getId()]['name'] = $name;
            $customSnippets[$shop->getId()]['data'] = $data;
        }

        $templateContext = [
            'customer' => $customer,
            'shopName' => $this->config->get('shopName'),
        ];

        if (!empty($orderId)) {
            $order = $this->db->fetchAll(
                "SELECT * FROM s_order WHERE id = :id",
                ['id' => $orderId]
            );

            $templateContext['order'] = $order[0];
        }

        $stringCompiler = new Shopware_Components_StringCompiler($this->view->Engine());

        $content = $stringCompiler->compileString($this->pluginConfig['CONTENT_TEMPLATE'], $templateContext);
        $this->view->assign('bodyContent', $content);

        $content = $stringCompiler->compileString($this->pluginConfig['SUBJECT_TEMPLATE'], $templateContext);
        $this->view->assign('subjectContent', $content);

        $content = $stringCompiler->compileString($this->config->get('emailfooterplain'), $templateContext);
        $this->view->assign('footer', $content);

        $content = $stringCompiler->compileString($this->config->get('emailheaderplain'), $templateContext);
        $this->view->assign('header', $content);

        $this->view->assign('orderId', $orderId);
        $this->view->assign('customerId', $customerId);
        $this->view->assign('fromMailAddresses', $list);
        $this->view->assign('toMailAddress', $customer['email']);
        $this->view->assign('toFullName', $customer['firstname'] . ' ' . $customer['lastname']);
        $this->view->assign('toNumber', $customer['customernumber']);
        $this->view->assign('isOwnFrame', $isOwnFrame);
        $this->view->assign('customSnippets', $customSnippets);
    }

    public function executeSendAction()
    {
        try {
            $to = $this->request->getParam('mailTo');

            if (!empty($this->request->getParam('mailToBcc'))) {
                $bcc = $this->request->getParam('mailToBcc');
            } else {
                $bcc = '';
            }

            $mail = $this->templateMail->createMail("blaubandMail", $this->request->getParams());
            $mail->addTo($to);
            $mail->addBcc($bcc);

            foreach ($_FILES as $file) {
                $content = file_get_contents($file['tmp_name']);
                $zendAttachment = new Zend_Mime_Part($content);
                $zendAttachment->type = $file['type'];
                $zendAttachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                $zendAttachment->encoding = Zend_Mime::ENCODING_BASE64;
                $zendAttachment->filename = $file['name'];

                $mail->addAttachment($zendAttachment);
            }

            $mail->send();

            //Gerade erstellten eintrag ergänzen um weitere Daten
            if ($this->request->getParam('orderId')) {
                $this->db->executeUpdate("
                    UPDATE blauband_email_logged_mail
                    SET orderID = :orderId
                    WHERE orderID IS NULL
                    AND customerID IS NULL
                    AND create_date > DATE_SUB(NOW(),INTERVAL 3 SECOND)
                    AND to_mail = :to",
                    [
                        'orderId' => $this->request->getParam('orderId'),
                        'to' => $to
                    ]
                );
            }

            if ($this->request->getParam('customerId')) {
                $this->db->executeUpdate("
                    UPDATE blauband_email_logged_mail
                    SET customerID = :customerId
                    WHERE customerID IS NULL
                    AND create_date > DATE_SUB(NOW(),INTERVAL 3 SECOND)
                    AND to_mail = :to",
                    [
                        'customerId' => $this->request->getParam('customerId'),
                        'to' => $to
                    ]
                );
            }

            $data = ['success' => true];
        } catch (\Exception $e) {
            $data = ['success' => false, 'message' => $e->getMessage()];
        }

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Response()->setBody(json_encode($data));
        $this->Response()->setHeader('Content-type', 'application/json', true);
    }
}
