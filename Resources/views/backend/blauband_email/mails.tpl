<ul class="mail-select">
    {foreach $mails as $key => $mail}
        {assign 'createDate' $mail->getCreateDate()|date_format:"%e %B %Y - %H:%M:%S"}
        {if  $mail->getCreateDate()|date_format:"%e %B %Y" == $smarty.now|date_format:"%e %B %Y"}
            {assign 'createDateShort' $mail->getCreateDate()|date_format:"%H:%M:%S"}
        {else}
            {assign 'createDateShort' $mail->getCreateDate()|date_format:"%e %B %Y"}

        {/if}
        <li>
            <a href="#mail-{$key}">
                <div class="title-from">{$mail->getFrom()}</div>
                <div class="title-date">{$createDateShort}</div>
                <div class="title-subject">{$mail->getSubject()|iconv_mime_decode}</div>
            </a>
        </li>
    {/foreach}
</ul>

{foreach $mails as $key => $mail}
    {assign 'createDate' $mail->getCreateDate()|date_format:"%e %B %Y - %H:%M:%S"}
    <div class="mail" id="mail-{$key}">
        <div>
            <div class="mail-attributes">
                <div class="title-date">{$createDate}</div>
                <label>{s namespace="blauband/mail" name="mailSubject"}{/s}
                    :</label> {$mail->getSubject()|iconv_mime_decode}<br/>
                <label>{s namespace="blauband/mail" name="mailFrom"}{/s}:</label> {$mail->getFrom()}<br/>
                <label>{s namespace="blauband/mail" name="mailTo"}{/s}:</label> {$mail->getTo()}<br/>


                {if !empty($mail->getOrder())}
                    <label>{s namespace="blauband/mail" name="orderNumber"}{/s}:</label>
                    {if $isOwnFrame}
                        <a href="#" class="open-order-link"
                           data-order-id="{$mail->getOrder()->getId()}">{$mail->getOrder()->getNumber()}</a>
                    {else}
                        {$mail->getOrder()->getNumber()}
                    {/if}
                    <br/>
                {/if}

            </div>
            <div class="mail-body bordered">
                {if $mail->isHtml()}
                    {$mail->getBody()}
                {else}
                    {$mail->getBody()|nl2br}
                {/if}
            </div>

            {$attachmentArray = json_decode($mail->getAttachments(), true)}
            <div class="mail-attachments" {if count($attachmentArray) == 0}style="display: none"{/if}>
                <label>{s namespace="blauband/mail" name="mailAttachments"}{/s}:</label><br/>

                {foreach $attachmentArray as $attachment}
                    <div class="mail-attachment">{$attachment.filename}</div>
                {/foreach}
            </div>
        </div>
    </div>
{/foreach}
