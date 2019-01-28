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
            <div id="blauband-mail">
                <form action="https://t5fcf4f0e.emailsys1a.net/172/1497/6a749b2890/subscribe/form.html" method="post">

                    <input style="display: none" type="text" name="rm_email" value="" tabindex="-1"/>

                    <div class="newsletter-input">
                        <label for="email">{s namespace="blauband/mail" name="mail"}{/s}: </label>
                        <input type="text" name="email" id="email" value=""/>
                    </div>

                    <div class="button-right-wrapper">
                        <button id="close-newsletter-popup-button" type="button">
                            {s namespace="blauband/mail" name="close"}{/s}
                        </button>
                        <button id="register-newsletter-popup-button" class="blue" type="submit">
                            {s namespace="blauband/mail" name="register"}{/s}
                        </button>
                    </div>
                </form>
            </div>
        {/block}
        </body>
    {/block}
    </html>
{/block}