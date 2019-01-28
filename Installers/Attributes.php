<?php

namespace BlaubandEmail\Installers;

use BlaubandEmail\Models\LoggedMail;
use Doctrine\ORM\Tools\SchemaTool;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;

class Attributes
{
    /**
     * @var CrudService
     * */
    private $crudService;

    /**
     * @var ModelManager
     */
    private $modelManager;


    /**
     * Attributes constructor.
     * @param CrudService $crudService
     * @param ModelManager $modelManager
     */
    public function __construct(CrudService $crudService, ModelManager $modelManager)
    {
        $this->crudService = $crudService;
        $this->modelManager = $modelManager;
    }

    /**
     * @return void
     */
    public function install()
    {
        $this->update();
    }

    /**
     * @return void
     */
    public function uninstall()
    {
        $this->crudService->delete(
            's_core_auth_attributes',
            'blauband_email_newsletter',
            true
        );
    }

    /**
     * @return void
     */
    public function update()
    {
        $this->crudService->update(
            's_core_auth_attributes',
            'blauband_email_newsletter',
            'boolean',

            [
                'label' => 'Field label',
                'translatable' => false,
                'displayInBackend' => false,
                'position' => 100,
                'custom' => false
            ]
        );

        $metaDataCache = $this->modelManager->getConfiguration()->getMetadataCacheImpl();
        $metaDataCache->deleteAll();
        $this->modelManager->generateAttributeModels(['s_core_auth_attributes']);
    }
}
