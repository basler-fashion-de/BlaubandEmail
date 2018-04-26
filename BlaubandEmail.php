<?php

namespace BlaubandEmail;

use Shopware\Components\Plugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Shopware-Plugin BlaubandEmail.
 */
class BlaubandEmail extends Plugin
{

    /**
    * @param ContainerBuilder $container
    */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('blauband_email.plugin_dir', $this->getPath());
        parent::build($container);
    }
}
