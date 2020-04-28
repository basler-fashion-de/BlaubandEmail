<?php

namespace BlaubandEmail\Installers;

use BlaubandEmail\Models\LoggedMail;
use Doctrine\ORM\Tools\SchemaTool;
use Shopware\Components\Model\ModelManager;

class Models
{
    /**
     * @var ModelManager
     * */
    private $modelManager;

    /**
     * @var array
     */
    private $classes;

    /**
     * @var SchemaTool
     */
    private $tool;

    /**
     * Models constructor.
     *
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
        $this->tool = new SchemaTool($this->modelManager);
        $this->classes = [$this->modelManager->getClassMetadata(LoggedMail::class)];
    }

    /**
     * @return void
     */
    public function install()
    {
        try{
            $this->tool->createSchema($this->classes);
        }catch (\Exception $e){}
    }

    /**
     * @return void
     */
    public function uninstall()
    {
        $this->tool->dropSchema($this->classes);
    }

    /**
     * @return void
     */
    public function update()
    {
        $this->modelManager->regenerateProxies();
        $this->tool->updateSchema($this->classes, true);
    }
}
