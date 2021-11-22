{extends file='page.tpl'}
{block name='page_title'}
    {l s='La operación no pudo completarse' mod='openpaypse'}
{/block}

{block name='page_content_container'}
    <section id="content" class="page-content page-cms">

        {block name='cms_content'}
            <div class="alert alert-danger">
                <i class="fa fa-warning"></i> Pago vía PSE fallido, favor de contactar a tu Banco emisor.
            </div>
            
        {/block}

    </section>
{/block}