<?php

use BlaubandEmail\Models\LoggedMail;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Model\ModelManager;

class Shopware_Controllers_Backend_BlaubandEmail extends \Enlight_Controller_Action implements CSRFWhitelistAware
{
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
        $this->view->addTemplateDir(__DIR__ . "/../../Resources/views");
    }

    public function indexAction()
    {
        /** @var ModelManager $modelManager */
        $modelManager = $this->container->get('models');

        $isOwnFrame = $this->request->getParam('frame') === '1';

        $customerId = $this->request->getParam('customerId');
        $orderId = $this->request->getParam('orderId');

        $limit = 20;
        $offset = empty(
        $this->request->getParam('offset')) ?
            0 :
            $this->request->getParam('offset');

        $repository = $modelManager->getRepository(LoggedMail::class);
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
        /* @var $db \Doctrine\DBAL\Connection */
        $db = $this->container->get('dbal_connection');

        /** @var Shopware_Components_Config $config */
        $config = $this->container->get('config');

        /** @var array $pluginConfig */
        $pluginConfig = $this->container->get('shopware.plugin.cached_config_reader')->getByPluginName('BlaubandEmail');

        /** @var Shopware_Components_StringCompiler $stringCompiler */
        $stringCompiler = new Shopware_Components_StringCompiler($this->view->Engine());

        $customerId = $this->request->getParam('customerId');
        $this->view->assign('customerId', $customerId);

        $orderId = $this->request->getParam('orderId');
        $this->view->assign('orderId', $orderId);

        $customer = $db->fetchAll('SELECT * FROM s_user WHERE id = :id', ['id' => $customerId]);
        $customer = $customer[0];

        $owner = $config->get('masterdata::mail');

        $currentUser = $this->container->get('auth')->getAdapter(0)->getResultRowObject('email');
        $currentUser = $currentUser->email;

        $users = $db->fetchAll(
            'SELECT email FROM s_core_auth WHERE email != ":currentUser" && email != ":owner" ORDER BY email',
            ['currentUser' => $currentUser, 'owner' => $owner]
        );

        $list = [$owner, $currentUser];
        foreach ($users as $user) {
            $list[] = $user['email'];
        }

        $this->view->assign('fromMailAddresses', $list);
        $this->view->assign('toMailAddress', $customer['email']);

        $templateContext = [
            'customer' => $customer,
            'shopName' => $config->get('shopName'),
        ];

        if (!empty($orderId)) {
            $order = $db->fetchAll(
                "SELECT * FROM s_order WHERE id = :id",
                ['id' => $orderId]
            );

            $templateContext['order'] = $order[0];
        }

        $contentTemplate = $pluginConfig['CONTENT_TEMPLATE'];
        $content = $stringCompiler->compileString($contentTemplate, $templateContext);
        $this->view->assign('bodyContent', $content);

        $subjectTemplate = $pluginConfig['SUBJECT_TEMPLATE'];
        $content = $stringCompiler->compileString($subjectTemplate, $templateContext);
        $this->view->assign('subjectContent', $content);

        $footer = $config->get('emailfooterplain');
        $content = $stringCompiler->compileString($footer, $templateContext);
        $this->view->assign('footer', $content);

        $header = $config->get('emailheaderplain');
        $content = $stringCompiler->compileString($header, $templateContext);
        $this->view->assign('header', $content);
    }

    public function executeSendAction()
    {
        try {
            /** @var Shopware_Components_TemplateMail $templateMail */
            $templateMail = $this->container->get('TemplateMail');

            /* @var $db \Doctrine\DBAL\Connection */
            $db = $this->container->get('dbal_connection');

            $to = $this->request->getParam('mailTo');

            if (!empty($this->request->getParam('mailToBcc'))) {
                $bcc = $this->request->getParam('mailToBcc');
            } else {
                $bcc = '';
            }

            $mail = $templateMail->createMail("blaubandMail", $this->request->getParams());
            $mail->addTo($to);
            $mail->addBcc($bcc);
            $mail->send();

            //Gerade erstellten eintrag ergänzen um weitere Daten
            if ($this->request->getParam('orderId')) {
                $db->executeUpdate("
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
                $db->executeUpdate("
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
