{block name="html"}
    <!DOCTYPE html>
    <html style="height: 100%">
    {block name="head"}
        <head>
            {include file="backend/blauband_common/header.tpl"}

            <script>
              var preselected = {if $isHtml}1{else}0{/if}
              {literal}
              $(function () {
                $('#mailContentWrapper').tabs({
                  active: preselected,
                  activate: function( event, ui ) {
                    $('#selectedTab').val( ui.newPanel.data('type'));
                  }
                })
              })
              {/literal}
            </script>


        </head>
    {/block}
    {block name="body"}
        <body>
        {block name="main-content"}
            <div id="blauband-mail">
                <div class="alerts ui-widget">
                    <div class="ui-state-error ui-corner-all">
                        <span class="ui-icon ui-icon-alert"></span>
                        <div class="content"></div>
                    </div>

                    <div class="ui-state-highlight ui-corner-all">
                        <span class="ui-icon ui-icon-info"></span>
                        <div class="content"></div>
                    </div>
                </div>

                <input type="hidden" id="customerId" name="customerId" value="{$customerId}">
                <input type="hidden" id="orderId" name="orderId" value="{$orderId}">
                <input type="hidden" id="shopName" name="shopName" value="{$shopName}">


                <h2>{s namespace="blauband/mail" name="sendMailHeader"}{/s}</h2>
                <hr/>

                <div class="button-right-wrapper">
                    <button id="back-button" data-url="{url action="index" customerId=$customerId orderId=$orderId}">
                        {s namespace="blauband/mail" name="back"}{/s}
                    </button>

                    <button id="execute-send-button" class="blue" data-url="{url action="executeSend"}">
                        {s namespace="blauband/mail" name="sendMail"}{/s}
                    </button>
                </div>

                <div>
                    <div class="two-cols">
                        <label>{s namespace="blauband/mail" name="mailTo"}{/s}</label>
                        <input type="hidden" id="mailTo" name="mailTo" value="{$toMailAddress}">
                        <input type="text" value="{$toMailAddress}" disabled>
                    </div>
                    <div class="two-cols">
                        <label>{s namespace="blauband/mail" name="mailFrom"}{/s}</label>
                        <select id="mailFrom" name="mailFrom">
                            {foreach $fromMailAddresses as $address}
                                <option value="{$address}">{$address}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>

                <div>
                    <div class="two-cols">
                        <label>{s namespace="blauband/mail" name="mailToBcc"}{/s}</label>
                        <select id="mailToBcc" name="mailToBcc">
                            <option value="">{s namespace="blauband/mail" name="noBcc"}{/s}</option>
                            {foreach $fromMailAddresses as $address}
                                <option value="{$address}">{$address}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="two-cols"></div>
                </div>

                <div>
                    <label>{s namespace="blauband/mail" name="mailSubject"}{/s}</label>
                    <input type="text" id="mailSubject" name="mailSubject"
                           value="{$subjectContent}">
                </div>

                <div>
                    <div class="two-cols">
                        {block name="mailContentWrapper"}
                            <div id="mailContentWrapper" class="tabs">
                                <input type="hidden" id="selectedTab" name="selectedTab" value="{if $isHtml}html{else}plain{/if}">
                                <ul>
                                    <li><a href="#plainMainContentWrapper">{s namespace="blauband/mail" name="plainEmail"}{/s}</a></li>
                                    <li><a href="#htmlMainContentWrapper">{s namespace="blauband/mail" name="htmlEmail"}{/s}</a></li>
                                </ul>
                                <div data-type="plain" id="plainMainContentWrapper">
                                    <label>{s namespace="blauband/mail" name="mailMessage"}{/s}</label>
                                    <textarea id="plainMailContent" name="plainMailContent">
                                        {$plainHeader|regex_replace:"/[\r\t\n]/":"&#10;"}
                                        &#10;
                                        {$bodyContent}
                                        &#10;
                                        {$plainFooter|regex_replace:"/[\r\t\n]/":"&#10;"}
                                    </textarea>
                                </div>

                                <div data-type="html" id="htmlMainContentWrapper">
                                    <label>{s namespace="blauband/mail" name="mailMessage"}{/s}</label>
                                    <div>
                                        {$htmlHeader}
                                    </div>
                                    <textarea id="htmlMailContent" name="htmlMailContent">
                                         {$bodyContent}
                                    </textarea>
                                    <div>
                                        {$htmlFooter}
                                    </div>
                                </div>

                                <h4>{s namespace="blauband/mail" name="mailAttachments"}{/s}</h4>
                                <button id="addAttachment">{s namespace="blauband/mail" name="addAttachment"}{/s}</button>
                            </div>
                        {/block}
                    </div>

                    <div class="two-cols">
                        {block name="mailContentWrapperAdditional"}{/block}
                    </div>
                </div>
            </div>
        {/block}
        </body>
    {/block}
    </html>
{/block}