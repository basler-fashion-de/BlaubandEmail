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
                {if $orderId}
                    <h2>{s namespace="blauband/mail" name="listOfMailsByOrder"}{/s}</h2>
                {else}
                    <h2>{s namespace="blauband/mail" name="listOfMails"}{/s}</h2>
                {/if}
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
                    {if $total < $limit}
                        <div class="list-navigation">
                            <button id="prev-mails-button"{if $offset == 0} disabled{/if}>
                                {s namespace="blauband/mail" name="previous"}{/s}
                            </button>

                            <button id="next-mails-button"{if $total <= $offset+$limit} disabled{/if}>
                                {s namespace="blauband/mail" name="next"}{/s}
                            </button>
                        </div>
                    {/if}
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