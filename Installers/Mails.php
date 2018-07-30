<?php

namespace BlaubandEmail\Installers;

use Shopware\Models\Mail\Mail;
use Shopware\Components\Model\ModelManager;
use BlaubandEmail\Services\ConfigService;

class Mails
{
    /** @var ModelManager */
    private $modelManager;

    /** @var array */
    private $mails;

    /** @var String */
    private $pluginRoot;

    public function __construct(ModelManager $modelManager, ConfigService $filesConfigService, $pluginRoot)
    {
        $this->modelManager = $modelManager;
        $this->mails = $filesConfigService->get('mails', true);
        $this->pluginRoot = $pluginRoot;
    }

    /**
     * @return void
     */
    public function install()
    {
        $repository = $this->modelManager->getRepository(Mail::class);

        foreach ($this->mails as $mail) {
            $mailModel = $repository->findOneBy(['name' => $mail['name']]);
            if (!$mailModel) {
                $mailModel = new Mail();
                $this->setMailModelData($mailModel, $mail);
                $this->modelManager->persist($mailModel);
            }
        }

        $this->modelManager->flush();
    }

    /**
     * @return void
     */
    public function uninstall()
    {
        $repository = $this->modelManager->getRepository(Mail::class);

        foreach ($this->mails as $mail) {
            $mailModel = $repository->findOneBy(['name' => $mail['name']]);
            $this->modelManager->remove($mailModel);
        }

        $this->modelManager->flush();
    }

    /**
     * @return void
     */
    public function update()
    {
        $repository = $this->modelManager->getRepository(Mail::class);

        foreach ($this->mails as $mail) {
            $mailModel = $repository->findOneBy(['name' => $mail['name']]);
            if ($mailModel) {
                $this->setMailModelData($mailModel, $mail);
            }
        }

        $this->modelManager->flush();
    }

    private function setMailModelData(&$mailModel, $data){
        $mailModel->setName($data['name']);
        $mailModel->setFromMail($data['fromMail']);
        $mailModel->setFromName($data['fromName']);
        $mailModel->setSubject($data['subject']);

        if(is_file($this->pluginRoot.$data['plainContent'])){
            $mailModel->setContent(file_get_contents($this->pluginRoot.$data['plainContent']));
        }else{
            $mailModel->setContent($data['plainContent']);
        }

        if(is_file($this->pluginRoot.$data['htmlContent'])){
            $mailModel->setContentHtml(file_get_contents($this->pluginRoot.$data['htmlContent']));
        }else{
            $mailModel->setContentHtml($data['htmlContent']);
        }

        $mailModel->setIsHtml(($data['isHtml'] == 'true'));
        $mailModel->setMailtype(Mail::MAILTYPE_USER);

        return $mailModel;
    }
}
