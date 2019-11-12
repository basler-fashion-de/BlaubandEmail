{block name="html"}
    <!DOCTYPE html>
    <html style="height: 100%">
    {block name="head"}
        <head>
            {include file="backend/blauband_common/header.tpl"}

            <script>


              function blaubandOpenPlugin (name) {
                var wait = 1;
                if(!window.parent.Shopware.app.Application.controllers.keys.includes('Shopware.apps.PluginManager')){
                  window.parent.Shopware.app.Application.addSubApplication({
                    name: 'Shopware.apps.PluginManager',
                    localizedName: "Plugin Manager"
                  });

                  wait = 500;
                }

                window.setTimeout(function () {
                  window.parent.Shopware.app.Application.fireEvent('display-plugin-by-name', name);
                }, wait);
              }
            </script>
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
            <div style="clear: both; margin-bottom: 50px"></div>
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