{extends file='page.tpl'}
{block name='page_title'}
    {l s='3D Secure Deshabilitado' mod='openpayprestashop'}
{/block}

{block name='page_content_container'}
    <section id="content" class="page-content page-cms">

        {block name='cms_content'}
            <div class="alert alert-danger">
                <i class="fa fa-warning"></i> El módulo de pagos no cuenta con el 3D Secure habilitado, contacte al administrador del sitio.
            </div>
            
        {/block}

    </section>
{/block}