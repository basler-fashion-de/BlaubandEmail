{block name="mail-list--header"}
    <ul class="mail-select">
        {foreach $mails as $key => $mail}
            {assign 'createDate' $mail->getCreateDate()|date_format:"%e %B %Y - %H:%M:%S"}
            {if  $mail->getCreateDate()|date_format:"%e %B %Y" == $smarty.now|date_format:"%e %B %Y"}
                {assign 'createDateShort' $mail->getCreateDate()|date_format:"%H:%M:%S"}
            {else}
                {assign 'createDateShort' $mail->getCreateDate()|date_format:"%e %B %Y"}
            {/if}

            {block name="mail-list--header--list-item"}
                <li>
                    {block name="mail-list--header--link"}
                        <a href="#mail-{$key}">
                            {block name="mail-list--header--content"}
                                <div class="title-from">{$mail->getFrom()|iconv_mime_decode|escape}</div>
                                <div class="title-date">{$createDateShort}</div>
                                <div class="title-subject">{$mail->getSubject()|iconv_mime_decode}</div>
                            {/block}
                        </a>
                    {/block}
                </li>
            {/block}
        {/foreach}
    </ul>
{/block}

{block name="mail-list--body"}
    {foreach $mails as $key => $mail}
        {assign 'createDate' $mail->getCreateDate()|date_format:"%e %B %Y - %H:%M:%S"}
        <div
                class="mail"
                id="mail-{$key}">
            <div>
                <div class="mail-attributes">
                    {block name="mail-list--body--attribute"}
                        {block name="mail-list--body--attribute--date"}
                            <div class="title-date">{$createDate}</div>
                        {/block}
                        {block name="mail-list--body--attribute--subject"}
                            <label>{s namespace="blauband/mail" name="mailSubject"}{/s}
                                :</label>
                            {$mail->getSubject()|iconv_mime_decode}
                        {/block}
                        <br/>
                        {block name="mail-list--body--attribute--from"}
                            <label>{s namespace="blauband/mail" name="mailFrom"}{/s}:</label>
                            {$mail->getFrom()|iconv_mime_decode|escape}
                        {/block}
                        <br/>
                        {block name="mail-list--body--attribute--to"}
                            <label>{s namespace="blauband/mail" name="mailTo"}{/s}:</label>
                            {$mail->getTo()|iconv_mime_decode|escape}
                        {/block}
                        <br/>
                        {if !empty($mail->getOrder())}
                            {block name="mail-list--body--attribute--order-link"}
                                <label>{s namespace="blauband/mail" name="orderNumber"}{/s}:</label>
                                <a href="#" class="open-order-link"
                                   data-order-id="{$mail->getOrder()->getId()}">{$mail->getOrder()->getNumber()}</a>
                            {/block}
                            <br/>
                        {/if}

                        {if !empty($mail->getCustomer())}
                            {block name="mail-list--body--attribute--customer-link"}
                                <label>{s namespace="blauband/mail" name="customer"}{/s}:</label>
                                    <a href="#" class="open-customer-link"
                                       data-customer-id="{$mail->getCustomer()->getId()}">
                                        {$mail->getCustomer()->getFirstname()} {$mail->getCustomer()->getLastname()}
                                        &lt;{$mail->getCustomer()->getEmail()}&gt;
                                    </a>
                            {/block}
                            <br/>
                        {/if}

                    {/block}
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
{/block}
