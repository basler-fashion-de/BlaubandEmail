<?php

namespace BlaubandEmail\Services;

interface MailServiceInterface
{
    public function saveMail(\Enlight_Components_Mail $mail);

    public function sendMail($to, $bcc, $context, $isHtml, $files = [], $template = 'EKS-Template');
}
