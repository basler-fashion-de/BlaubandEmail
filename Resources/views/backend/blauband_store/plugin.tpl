<div class="plugin-list">
    <div class="plugin-container">
        <img src="{$plugin.iconPath}"/>
        {$plugin.label}<br/>
        <button type="button" onclick="blaubandOpenPlugin('{$plugin.technicalName}');">
            {if $plugin.active}
                {s namespace="blauband/mail" name="open"}{/s}
            {else}
            {s namespace="blauband/mail" name="tryNow"}{/s}
            {/if}
        </button>
    </div>
</div>