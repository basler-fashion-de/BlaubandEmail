{block name="html"}
    <!DOCTYPE html>
    <html style="height: 100%">
    {block name="head"}
        <head>
            {include file="backend/blauband_common/header.tpl"}
        </head>
    {/block}
    {block name="body"}
        <body style="height: 100%">
        {block name="main-content"}
            {if $eksPlugins}
                <h2>{s namespace="blauband/mail" name="morePlugins"}{/s}</h2>
                {foreach $eksPlugins as $plugin}
                    {include file="backend/blauband_store/plugin.tpl"}
                {/foreach}
            {/if}

            {if $blaubandPlugins}
                <h2>{s namespace="blauband/mail" name="moreBlaubandPlugins"}{/s}</h2>
                {foreach $blaubandPlugins as $plugin}
                    {include file="backend/blauband_store/plugin.tpl"}
                {/foreach}
            {/if}
        {/block}
        </body>
    {/block}
    </html>
{/block}