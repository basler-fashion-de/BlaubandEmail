{foreach $mails as $mail}
    {assign 'createDate' $mail->getCreateDate()|date_format:"%e %B %Y - %H:%M:%S"}

    <div class="mail">
        <h3 class="title">{$mail->getSubject()} - {$createDate} - {$mail->getTo()}{if !empty($mail->getOrder())} - {$mail->getOrder()->getNumber()}{/if}</h3>
        <div>
            <div class="mail-attributes">
                <label>{s namespace="blauband/mail" name="mailSubject"}{/s}:</label> {$mail->getSubject()}<br/>
                <label>{s namespace="blauband/mail" name="mailFrom"}{/s}:</label> {$mail->getFrom()}<br/>
                <label>{s namespace="blauband/mail" name="mailTo"}{/s}:</label> {$mail->getTo()}<br/>
                <label>{s namespace="blauband/mail" name="mailDate"}{/s}:</label> {$createDate}<br/>

                {if !empty($mail->getOrder())}
                    <label>{s namespace="blauband/mail" name="orderNumber"}{/s}:</label>
                    {*<a href="#" class="open-order-link" data-order-id="{$mail->getOrder()->getId()}">{$mail->getOrder()->getNumber()}</a>*}
                    {$mail->getOrder()->getNumber()}
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