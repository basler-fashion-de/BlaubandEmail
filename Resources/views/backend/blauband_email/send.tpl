<!DOCTYPE html>
<html>
<head>
    {include file="backend/blauband_common/header.tpl"}
    <script type="text/javascript" src="{link file="backend/_public/src/js/snippet.js"}"></script>
</head>
<body>
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
            <label>
                {s namespace="blauband/mail" name="mailTo"}{/s}
                {if $isOwnFrame}
                    <a href="#" class="open-customer-link" data-customer-id="{$customerId}">{$toFullName} ({$toNumber})</a>
                {/if}
            </label>
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
            <div id="mailContentWrapper">
                <label>{s namespace="blauband/mail" name="mailMessage"}{/s}</label>
                <textarea id="mailContent" name="mailContent">
                    {$header|regex_replace:"/[\r\t\n]/":"&#10;"}
                    &#10;
                    {$bodyContent}
                    &#10;
                    {$footer|regex_replace:"/[\r\t\n]/":"&#10;"}
                </textarea>

                <h4>{s namespace="blauband/mail" name="mailAttachments"}{/s}</h4>
                <button id="addAttachment">{s namespace="blauband/mail" name="addAttachment"}{/s}</button>
            </div>

        </div>
        <div class="two-cols">
            <div id="mailCustomSnippets">
                <label>{s namespace="blauband/mail" name="mailSnippets"}{/s}</label>
                <select id="customSnippets" name="customSnippets">
                    {foreach $customSnippets as $id => $data}
                        <option value="{$id}">{$data.name}</option>
                    {/foreach}
                </select>
                <div class="customSnippetsDataWrapper">
                    {foreach $customSnippets as $id => $data}
                        <div class="customSnippetsData" id="customSnippetsData{$id}">
                            {foreach $data.data as $snippetName => $snippet}
                                <div class="snippetRow">
                                    <div class="snippetName">{$snippetName}</div>
                                    <div class="snippet">{$snippet}</div>
                                </div>
                            {/foreach}
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>


