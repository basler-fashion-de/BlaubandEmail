<ul>
    {foreach $mails as $key => $mail}
        {assign 'createDate' $mail->getCreateDate()|date_format:"%e %B %Y - %H:%M:%S"}
        <li>
            <a href="#mail-{$key}">
                {$mail->getSubject()|iconv_mime_decode} - {$createDate}
            </a>
        </li>
    {/foreach}
</ul>


{foreach $mails as $key => $mail}
    {assign 'createDate' $mail->getCreateDate()|date_format:"%e %B %Y - %H:%M:%S"}
    <div class="mail" id="mail-{$key}">
        <div>
            <div class="mail-attributes">
                <label>{s namespace="blauband/mail" name="mailSubject"}{/s}:</label> {$mail->getSubject()|iconv_mime_decode}<br/>
                <label>{s namespace="blauband/mail" name="mailFrom"}{/s}:</label> {$mail->getFrom()}<br/>
                <label>{s namespace="blauband/mail" name="mailTo"}{/s}:</label> {$mail->getTo()}<br/>
                <label>{s namespace="blauband/mail" name="mailDate"}{/s}:</label> {$createDate}<br/>

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
        </div>
    </div>
{/foreach}