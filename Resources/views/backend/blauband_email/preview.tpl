{block name="html"}
    <!DOCTYPE html>
    <html style="height: 100%">
    {block name="head"}
        <head>
            {include file="backend/blauband_common/header.tpl"}
        </head>
    {/block}
    {block name="body"}
        <body>
        {block name="main-content"}
            <div id="blauband-mail">
                <div class="blauband--action-bar">
                    <button id="preview-send-button" class="blue">
                        {s namespace="blauband/mail" name="sendMail"}{/s}
                    </button>
                </div>
                <div class="blauband-preview">

                    {$preview}

                </div>
            </div>
        {/block}
        </body>
    {/block}
    </html>
{/block}