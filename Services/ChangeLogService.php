<?php

namespace BlaubandEmail\Services;

use Shopware\Components\Plugin\Context\InstallContext;

class ChangeLogService
{
    /** @var ConfigService */
    private $pluginXml;

    public function __construct(ConfigService $pluginXml)
    {
        $this->pluginXml = $pluginXml;
    }

    public function popUpChangeLogs(InstallContext $context, $oldVersion)
    {
        $message = [];
        $changeLogs = $this->pluginXml->get('changelog');
        foreach ($changeLogs as $changeLog) {
            $version = $changeLog['@attributes']['version'];
            if (
                $oldVersion &&
                $version !== '1.0.0' &&
                version_compare($oldVersion, $version, '<=')
            ) {
                $message[] = $version . ':<br/>' . $changeLog['changes'][0];
            }
        }

        if (!empty($message)) {
            $context->scheduleMessage(implode('<br/>---------------<br/>', $message));
        }
    }
}