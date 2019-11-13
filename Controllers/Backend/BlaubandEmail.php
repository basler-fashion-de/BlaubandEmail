<?php

use BlaubandEmail\Models\LoggedMail;
use BlaubandEmail\Services\ConfigService;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Mail\Mail;
use Shopware\Models\Order\Order;
use Shopware\Models\Shop\Shop;
use BlaubandEmail\Services\MailService;
use Shopware\Models\User\User;

class Shopware_Controllers_Backend_BlaubandEmail extends \Enlight_Controller_Action implements CSRFWhitelistAware
{
    /** @var ModelManager $modelManager */
    private $modelManager;

    /* @var $db \Doctrine\DBAL\Connection */
    private $db;

    private $auth;

    /** @var MailService */
    private $mailService;

    /** @var \Shopware\Components\Plugin\CachedConfigReader */
    private $configReader;

    /** @var \BlaubandEmail\Services\AdService */
    private $adService;

    /** @var Shopware_Components_TemplateMail $templateMail */
    private $templateMail;

    public function getWhitelistedCSRFActions()
    {
        return [
            'index',
            'send',
            'executeSend',
            'newsletter',
            'writeLatestLock',
            'dokumentation',
            'preview'
        ];
    }

    public function preDispatch()
    {
        $this->modelManager = $this->container->get('models');
        $this->db = $this->container->get('dbal_connection');
        $this->auth = $this->container->get('auth');
        $this->mailService = $this->container->get('blauband_email.services.email_service');
        $this->configReader = $this->container->get('shopware.plugin.cached_config_reader');
        $this->adService = $this->container->get('blauband_email.services.ad_service');
        $this->templateMail = $this->container->get('TemplateMail');

        $this->view->assign('adContent', $this->adService->getLatestAdContent());

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

        $authId = $this->auth->getIdentity()->id;
        /** @var User $authModel */
        $authModel = $this->modelManager->find(User::class, $authId);
        $showNewsletter = (
            $authModel->getAttribute() === null ||
            !$authModel->getAttribute()->getBlaubandEmailNewsletter()
        );

        if (empty($customerId)) {
            $customerId = '-';
        }

        $this->view->assign('customerId', $customerId);
        $this->view->assign('isOwnFrame', $isOwnFrame);
        $this->view->assign('orderId', $orderId);
        $this->view->assign('mails', $allMails);
        $this->view->assign('offset', $offset);
        $this->view->assign('limit', $limit);
        $this->view->assign('total', $total);
        $this->view->assign('newsletter', $showNewsletter);
    }

    public function sendAction()
    {
        /** @var array $pluginConfig */
        $pluginConfig = $this->configReader->getByPluginName('BlaubandEmail');

        /** @var Shopware_Components_StringCompiler $stringCompiler */
        $stringCompiler = new Shopware_Components_StringCompiler($this->view->Engine());

        list($orderArray, $customerArray, $customerShop, $customerConfig) = $this->prepareRequestData();
        $templateContext = $this->getTemplateContext($orderArray, $customerArray, $customerShop, $customerConfig);

        $owner = $customerConfig->get('masterdata::mail');

        $currentUser = $this->auth->getAdapter(0)->getResultRowObject('email');
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

        $this->view->assign('fromMailAddresses', $list);
        $this->view->assign('toMailAddress', $customerArray['email']);

        $isHtml = $this->db->fetchColumn(
            'SELECT ishtml FROM s_core_config_mails WHERE name = "sORDER"'
        ) !== '0' ? true : false;

        $this->view->assign('isHtml', $isHtml);

        $contentTemplate = $pluginConfig['CONTENT_TEMPLATE'];
        $content = $stringCompiler->compileString($contentTemplate, $templateContext);
        $this->view->assign('bodyContent', $content);

        $subjectTemplate = $pluginConfig['SUBJECT_TEMPLATE'];
        $content = $stringCompiler->compileString($subjectTemplate, $templateContext);
        $this->view->assign('subjectContent', $content);

        $this->view->assign('shopName', $customerConfig->get('shopName'));
        $this->view->assign('customerId', $customerArray['id']);
        $this->view->assign('orderId', $orderArray['id']);
    }

    public function executeSendAction()
    {
        try {
            $request = $this->request;
            $params = $this->request->getParams();

            list($orderArray, $customerArray, $customerShop, $customerConfig) = $this->prepareRequestData();
            $templateContext = $this->getTemplateContext($orderArray, $customerArray, $customerShop, $customerConfig);

            $to = $request->getParam('mailTo');

            if (!empty($request->getParam('mailToBcc'))) {
                $bcc = $request->getParam('mailToBcc');
            } else {
                $bcc = '';
            }

            $isHtml = $request->getParam('selectedTab') === 'html';

            $this->mailService->sendMail(
                $to,
                $bcc,
                array_merge($params, $templateContext),
                $isHtml,
                $_FILES
            );

            //Gerade erstellten eintrag ergänzen um weitere Daten
            if ($request->getParam('orderId')) {
                $this->db->executeUpdate("
                    UPDATE blauband_email_logged_mail
                    SET orderID = :orderId
                    WHERE orderID IS NULL
                    AND customerID IS NULL
                    AND create_date > DATE_SUB(NOW(),INTERVAL 3 SECOND)
                    AND to_mail = :to",
                    [
                        'orderId' => $request->getParam('orderId'),
                        'to' => $to
                    ]
                );
            }

            if ($request->getParam('customerId')) {
                $this->db->executeUpdate("
                    UPDATE blauband_email_logged_mail
                    SET customerID = :customerId
                    WHERE customerID IS NULL
                    AND create_date > DATE_SUB(NOW(),INTERVAL 3 SECOND)
                    AND to_mail = :to",
                    [
                        'customerId' => $request->getParam('customerId'),
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

    public function newsletterAction()
    {
        $newsletterShowed = $this->request->getParam('newsletterShowed') === '1';

        if ($newsletterShowed) {
            $authId = $this->auth->getIdentity()->id;
            /** @var User $authModel */
            $authModel = $this->modelManager->find(User::class, $authId);
            if (empty($authModel->getAttribute())) {
                $authModel->setAttribute(new \Shopware\Models\Attribute\User());
            }

            $authModel->getAttribute()->setBlaubandEmailNewsletter(1);
            $this->modelManager->flush($authModel->getAttribute());
        }
    }

    public function writeLatestLockAction()
    {
        $this->adService->writeLatestLock();

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Response()->setBody(json_encode(['success' => true]));
        $this->Response()->setHeader('Content-type', 'application/json', true);
    }

    public function previewAction()
    {
        $params = $this->request->getParams();
        $isHtml = $params['selectedTab'] === 'html';

        if (isset($params['template'])) {
            /* @var $mailModel \Shopware\Models\Mail\Mail */
            $mailModel = $this->modelManager->find(Mail::class, $params['template']);
        } else {
            $filesConfigService = new ConfigService(__DIR__ . '/../../Resources/mails.xml');
            $mails = $filesConfigService->get('mails', true);
            $repository = $this->modelManager->getRepository(Mail::class);

            foreach ($mails as $mail) {
                $mailModel = $repository->findOneBy(['name' => $mail['name']]);
            }

            if (empty($mailModel)) {
                $this->View()->assign('preview', 'Kein EmailTemplate gefunden. Installieren Sie das Plugin erneut');
                return;
            }
        }

        $mailModel->setIsHtml($isHtml);

        list($orderArray, $customerArray, $customerShop, $customerConfig) = $this->prepareRequestData();
        $templateContext = $this->getTemplateContext($orderArray, $customerArray, $customerShop, $customerConfig);

        $mail = $this->templateMail->createMail($mailModel, array_merge($params, $templateContext));

        if ($isHtml) {
            $this->View()->assign('preview', $mail->getBodyHtml()->getRawContent());
        } else {
            $this->View()->assign('preview', nl2br($mail->getBodyText()->getRawContent()));
        }
    }

    private function prepareRequestData()
    {
        $orderId = $this->request->getParam('orderId');
        $customerId = $this->request->getParam('customerId');

        if (empty($customerId) || !is_numeric($customerId)) {
            /** @var Order $o */
            $o = $this->modelManager->find(Order::class, $orderId);
            $customerId = $o->getCustomer()->getId();
        }

        $customer = $this->db->fetchAll('SELECT * FROM s_user WHERE id = :id', ['id' => $customerId]);
        $customerArray = $customer[0];

        $orderArray = [];
        if (!empty($orderId)) {
            $order = $this->db->fetchAll(
                "SELECT * FROM s_order WHERE id = :id",
                ['id' => $orderId]
            );

            $orderDetails = $this->db->fetchAll(
                "SELECT * FROM s_order_details WHERE orderID = :id",
                ['id' => $orderId]
            );

            $orderShipping = $this->db->fetchAll(
                "SELECT * FROM s_order_shippingaddress WHERE orderID = :id",
                ['id' => $orderId]
            );

            $orderBilling = $this->db->fetchAll(
                "SELECT * FROM s_order_billingaddress WHERE orderID = :id",
                ['id' => $orderId]
            );

            $payment = $this->db->fetchAll(
                "SELECT * FROM s_core_paymentmeans WHERE id = :paymentId",
                ['paymentId' => $order[0]['paymentID']]
            );

            $orderArray = $order[0];
            $orderArray['details'] = $orderDetails;
            $orderArray['shipping'] = $orderShipping[0];
            $orderArray['billing'] = $orderBilling[0];
            $orderArray['payment'] = $payment[0];
        }


        /** @var Shop $customerShop */
        $customerShop = $this->modelManager->getRepository(Shop::class)->find($customerArray['subshopID']);
        $templateDirs = $this->view->Engine()->getTemplateDir();
        $customerShop->registerResources();
        $this->view->Engine()->setTemplateDir($templateDirs); //registerResources überschreibt alles

        /** @var Shopware_Components_Config $customerConfig */
        $customerConfig = $this->container->get('config');
        $customerConfig->setShop($customerShop);

        return [$orderArray, $customerArray, $customerShop, $customerConfig];
    }

    private function getTemplateContext($order, $customer, Shop $customerShop, $config)
    {
        $templateContext = [
            'sConfig' => Shopware()->Config(),
            'customer' => $customer,
            'order' => $order,
            'currency' => $customerShop->getCurrency()->getSymbol(),
            'shopName' => $config->get('shopName'),
            'sShopURL' => ($customerShop->getSecure() ? 'https://' : 'http://') . $customerShop->getHost() . $customerShop->getBaseUrl(),
        ];

        return $templateContext;
    }
}
