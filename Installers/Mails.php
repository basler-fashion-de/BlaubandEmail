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
                $mailModel->setName($mail['name']);
                $mailModel->setFromMail($mail['fromMail']);
                $mailModel->setFromName($mail['fromName']);
                $mailModel->setSubject($mail['subject']);

                if(is_file($this->pluginRoot.$mail['plainContent'])){
                    $mailModel->setContent(file_get_contents($this->pluginRoot.$mail['plainContent']));
                }else{
                    $mailModel->setContent($mail['plainContent']);
                }

                if(is_file($this->pluginRoot.$mail['htmlContent'])){
                    $mailModel->setContentHtml(file_get_contents($this->pluginRoot.$mail['htmlContent']));
                }else{
                    $mailModel->setContentHtml($mail['htmlContent']);
                }

                $mailModel->setIsHtml(($mail['isHtml'] == 'true'));
                $mailModel->setMailtype(Mail::MAILTYPE_USER);

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

    }
}
