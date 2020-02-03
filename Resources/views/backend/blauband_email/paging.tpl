{if $total > $limit}
    <div class="list-navigation">
        <button id="prev-mails-button"{if $offset == 0} disabled{/if}>
            {s namespace="blauband/mail" name="previous"}{/s}
        </button>

        <span>
            {s namespace="blauband/mail" name="page"}{/s} {$offset/$limit+1} {s namespace="blauband/mail" name="of"}{/s} {($total/$limit+1)|string_format:"%d"}
        </span>

        <button id="next-mails-button"{if $total <= $offset+$limit} disabled{/if}>
            {s namespace="blauband/mail" name="next"}{/s}
        </button>
    </div>
{/if}