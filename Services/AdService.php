<?php

namespace BlaubandEmail\Services;

class AdService
{
    private $url = 'https://blauband-verlag.com/eks/';
    private $contentPath = 'eks-info.xml';
    private $content;
    private $lockFile = '/../ad.lock';
    private $dateFormat = 'Y-m-d H:i:s';

    public function __construct()
    {
        $contentUrl = $this->url . $this->contentPath;
        $xmlContent = file_get_contents($contentUrl);
        $this->content = json_decode(json_encode((array)simplexml_load_string($xmlContent)), 1);
    }

    public function getLatestAdContent()
    {
        $latestDate = $this->readLatestLock();
        $now = date($this->dateFormat);
        $returnContents = [];
        $ads = $this->content['ad'];

        if(!isset($ads[0])){
            $ads = [$ads];
        }

        foreach ($ads as $ad) {
            if (
                $ad['availableFrom'] > $latestDate &&
                $ad['availableFrom'] < $now &&
                $ad['availableTo'] > $now &&
                !isset($ad['test'])
            ) {
                $returnContents[$ad['availableFrom']] = $ad['content'];
            }
        }

        if (!empty($returnContents)) {
            krsort($returnContents);
            return array_shift($returnContents);
        }

        return;
    }

    public function writeLatestLock()
    {
        $result = file_put_contents(__DIR__ . $this->lockFile, date($this->dateFormat));
    }

    public function readLatestLock()
    {
        return file_get_contents(__DIR__ . $this->lockFile);
    }
}