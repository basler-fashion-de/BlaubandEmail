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
                <div class="blauband-preview">

                    {$preview}

                </div>
            </div>
        {/block}
        </body>
    {/block}
    </html>
{/block}