<?php

namespace BlaubandEmail\Installers;

use Doctrine\ORM\EntityRepository;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Config\Element;

class Config
{
    /** @var ModelManager */
    private $modelManager;

    private $updated;

    public function __construct($modelManager, $update)
    {
        $this->modelManager = $modelManager;
        $this->updated = $update;
    }

    /**
     * @return void
     */
    public function install()
    {
    }

    /**
     * @return void
     */
    public function uninstall()
    {
    }

    /**
     * @return void
     */
    public function update()
    {
        if ($this->updated) {
            /** @var EntityRepository $elementRepository */
            $elementRepository = $this->modelManager->getRepository(Element::class);

            /** @var Element $valueElement */
            $valueElement = $elementRepository->findOneBy(['name' => 'SAVE_NEWSLETTER_MAILS']);
            $valueElement->setValue(true);
            $this->modelManager->flush();
        }
    }
}
