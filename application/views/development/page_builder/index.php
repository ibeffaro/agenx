<action-header>
    <a href="javascript:;" class="btn btn-app" id="btn-add"><i class="fa-plus"></i><span class="ms-2">ln{tambah}</span></a>
</action-header>
<div class="row">
    <?php foreach($list_page as $r) { ?>
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-4 col-6 mb-4 list-page" data-key="<?=encode_string($r);?>">
        <a href="<?=base_url('development/page-builder/edit/'.encode_string($r));?>" class="quick-link">
            <i class="icon fa-window-alt"></i>
            <span class="title"><?=str_replace('.page.txt','',$r);?></span>
        </a>
    </div>
    <?php } ?>
</div>
<app-modal title="${title}" subtitle="ln{tambah}" id="modal-form">
    <form id="form-x" method="post" action="base_url{development/page-builder/add}" data-submit="ajax" data-callback="redirect:href" autocomplete="off">
        <app-input type="text" name="nama" label="ln{nama_halaman}" validation="required|alphanumeric|min-length:5" size="3:9" size-param="md" />
    </form>
    <footer-form id="form-x" />
</app-modal>
<script>
$('#btn-add').click(function(){
    modal('modal-form').show();
});
$.contextMenu({
    selector: '.list-page',
    callback: function(key, options) {
        console.log($(this).attr('data-key'));
        var ajaxURL = baseURL('development/page-builder/delete');
        cConfirm.open(lang.apakah_anda_yakin_ingin_menghapus_data_ini+'?','__deleteData',{
            url : ajaxURL,
            valID : $(this).attr('data-key'),
            valKey: 'key',
            appLink : 'default'
        });
    },
    items: {
        delete : {
            name : lang.hapus,
            icon : 'fa-trash-alt'
        }
    }
});
</script>