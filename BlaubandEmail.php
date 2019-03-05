<?php

namespace BlaubandEmail;

use BlaubandEmail\Installers\Attributes;
use BlaubandEmail\Installers\Config;
use BlaubandEmail\Installers\Mails;
use BlaubandEmail\Services\ChangeLogService;
use BlaubandEmail\Services\ConfigService;
use Shopware\Components\Plugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use BlaubandEmail\Installers\Models;

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

    public function install(InstallContext $context)
    {
        $this->setup(null, $context->getCurrentVersion());
        parent::install($context);
    }

    public function update(UpdateContext $context)
    {
        $this->setup($context->getCurrentVersion(), $context->getUpdateVersion());
        parent::update($context);

        /** @var ChangeLogService $changeLogService */
        $changeLogService = $this->container->get('blauband_email.services.change_log_service');
        $changeLogService->popUpChangeLogs($context, $context->getCurrentVersion());

    }

    public function uninstall(UninstallContext $context)
    {
        if (!$context->keepUserData()) {
            (new Models($this->container->get('models')))->uninstall();

            (new Attributes(
                $this->container->get('shopware_attribute.crud_service'),
                $this->container->get('models')
            ))->uninstall();
        }

        parent::uninstall($context);
    }


    /**
     * @param string|null $oldVersion
     * @param string|null $newVersion
     *
     * @return bool
     */
    public function setup($oldVersion = null, $newVersion = null)
    {
        $versions = [
            '0.0.1' => function () {
                (new Models($this->container->get('models')))->install();
                return true;
            },

            '0.0.2' => function () {
                (new Models($this->container->get('models')))->update();
                return true;
            },

            '0.0.3' => function () {
                (new Mails(
                    $this->container->get('models'),
                    new ConfigService($this->getPath() . '/Resources/mails.xml'),
                    $this->getPath()
                ))->install();
                return true;
            },

            '0.0.4' => function () {
                (new Models($this->container->get('models')))->update();
                return true;
            },

            '1.1.0' => function () {
                (new Mails(
                    $this->container->get('models'),
                    new ConfigService($this->getPath() . '/Resources/mails.xml'),
                    $this->getPath()
                ))->update();
                return true;
            },

            '1.2.0' => function ($plugin, $oldVersion, $version, $newVersion) {
                (new Config(
                    $this->container->get('models'),
                    (version_compare($oldVersion, $version, '<') && $oldVersion !== null)
                ))->update();

                (new Attributes(
                    $this->container->get('shopware_attribute.crud_service'),
                    $this->container->get('models')
                ))->install();

                return true;
            },
        ];

        foreach ($versions as $version => $callback) {
            if ($oldVersion === null || (version_compare($oldVersion, $version, '<') && version_compare($version, $newVersion, '<='))) {
                if (!$callback($this, $oldVersion, $version, $newVersion)) {
                    return false;
                }
            }
        }

        return true;
    }
}
