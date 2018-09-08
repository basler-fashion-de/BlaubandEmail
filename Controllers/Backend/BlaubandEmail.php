<?php

use BlaubandEmail\Models\LoggedMail;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Mail\Mail;

class Shopware_Controllers_Backend_BlaubandEmail extends \Enlight_Controller_Action implements CSRFWhitelistAware
{
    /** @var ModelManager $modelManager */
    private $modelManager;

    /* @var $db \Doctrine\DBAL\Connection */
    private $db;

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
        $this->modelManager = $this->container->get('models');
        $this->db = $this->container->get('dbal_connection');
        $this->templateMail = $this->container->get('TemplateMail');

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
        /** @var array $pluginConfig */
        $pluginConfig = $this->container->get('shopware.plugin.cached_config_reader')->getByPluginName('BlaubandEmail');

        /** @var Shopware_Components_StringCompiler $stringCompiler */
        $stringCompiler = new Shopware_Components_StringCompiler($this->view->Engine());

        list($orderId, $customerId, $customerArray, $customerShop, $customerConfig) = $this->prepareRequestData();
        $templateContext = $this->getTemplateContext($customerArray, $customerShop, $customerConfig, $orderId);

        $owner = $customerConfig->get('masterdata::mail');

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

        $plainFooter = $customerConfig->get('emailfooterplain');
        $content = $stringCompiler->compileString($plainFooter, $templateContext);
        $this->view->assign('plainFooter', $content);

        $plainHeader = $customerConfig->get('emailheaderplain');
        $content = $stringCompiler->compileString($plainHeader, $templateContext);
        $this->view->assign('plainHeader', $content);

        $htmlFooter = $customerConfig->get('emailfooterhtml');
        $content = $stringCompiler->compileString($htmlFooter, $templateContext);
        $this->view->assign('htmlFooter', $content);

        $htmlHeader = $customerConfig->get('emailheaderhtml');
        $content = $stringCompiler->compileString($htmlHeader, $templateContext);
        $this->view->assign('htmlHeader', $content);

        $this->view->assign('shopName', $customerConfig->get('shopName'));
        $this->view->assign('customerId', $customerId);
        $this->view->assign('orderId', $orderId);
    }

    public function executeSendAction()
    {
        try {
            $request = $this->request;

            list($orderId, $customerId, $customerArray, $customerShop, $customerConfig) = $this->prepareRequestData();
            $templateContext = $this->getTemplateContext($customerArray, $customerShop, $customerConfig, $orderId);

            $to = $request->getParam('mailTo');

            if (!empty($request->getParam('mailToBcc'))) {
                $bcc = $request->getParam('mailToBcc');
            } else {
                $bcc = '';
            }

            $isHtml = $request->getParam('selectedTab') === 'html';

            //Bei HTML Emails setzen wir zu Beginn und am Ende einen Zeilen Abstand
            $request->setParam('htmlMailContent',
                '<br/>'. $request->getParam('htmlMailContent').'<br/>'
            );

            /* @var $mailModel \Shopware\Models\Mail\Mail */
            $mailModel = $this->modelManager->getRepository(Mail::class)->findOneBy(
                ['name' => 'blaubandMail']
            );
            $mailModel->setIsHtml($isHtml);

            $mail = $this->templateMail->createMail($mailModel, array_merge($request->getParams(), $templateContext));
            $mail->addTo($to, $to);

            if(!empty($bcc)){
                $mail->addBcc($bcc);
            }

            $mail->send();

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

    private function prepareRequestData(){
        $orderId = $this->request->getParam('orderId');

        $customerId = $this->request->getParam('customerId');
        if(empty($customerId)){
            /** @var Order $o */
            $o = $this->modelManager->find(Order::class, $orderId);
            $customerId = $o->getCustomer()->getId();
        }

        $customer = $this->db->fetchAll('SELECT * FROM s_user WHERE id = :id', ['id' => $customerId]);
        $customerArray = $customer[0];

        /** @var Shop $customerShop */
        $customerShop = $this->modelManager->getRepository(Shop::class)->find($customerArray['subshopID']);

        /** @var Shopware_Components_Config $customerConfig */
        $customerConfig = $this->container->get('config');
        $customerConfig->setShop($customerShop);

        return [$orderId, $customerId, $customerArray, $customerShop, $customerConfig];
    }

    private function getTemplateContext($customer, Shop $customerShop, $config, $orderId){
        $templateContext = [
            'customer' => $customer,
            'shopName' => $config->get('shopName'),
            'sShopURL' => ($customerShop->getSecure() ? 'https://' : 'http://') . $customerShop->getHost() . $customerShop->getBaseUrl(),
        ];

        if (!empty($orderId)) {
            $order = $this->db->fetchAll(
                "SELECT * FROM s_order WHERE id = :id",
                ['id' => $orderId]
            );

            $templateContext['order'] = $order[0];
        }

        return $templateContext;
    }
}
