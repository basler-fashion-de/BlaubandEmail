{block name="html"}
    <!DOCTYPE html>
    <html style="height: 100%">
    {block name="head"}
        <head>
            {include file="backend/blauband_common/header.tpl"}

            <script type="application/javascript">
              var offset = '{$offset}'
              var limit = '{$limit}'
              var showNewsletter = '{$newsletter}'
            </script>
        </head>
    {/block}
    {block name="body"}
        <body style="height: 100%">
        {block name="main-content"}
            <div id="blauband-mail">
                {include file="backend/blauband_email/ad.tpl"}

                <h2>
                    {if $orderId}
                        {s namespace="blauband/mail" name="listOfMailsByOrder"}{/s}
                    {else}
                        {s namespace="blauband/mail" name="listOfMails"}{/s}
                    {/if}
                    {include file="backend/blauband_email/components/dokumentation-button.tpl"}
                </h2>

                <hr/>

                <button id="send-mail-button" class="blue"
                        data-url="{url action="send" customerId=$customerId orderId=$orderId}">
                    {s namespace="blauband/mail" name="writeMail"}{/s}
                </button>

                {if empty($mails)}
                    <div>
                        {s namespace="blauband/mail" name="noMails"}{/s}
                    </div>
                {else}
                    {include file="backend/blauband_email/paging.tpl"}

                    <div class="mail-list" id="mails">
                        {include file="backend/blauband_email/mails.tpl"}
                    </div>
                {/if}
            </div>
        {/block}
        </body>
    {/block}
    </html>
{/block}