<?php if($access['edit']) { ?>
<action-header-additional>
    <action label="<?=lang('urutkan_menu');?>" icon="fa-list" class="btn-sort" />
</action-header-additional>
<?php } ?>
<app-card>
    <app-table data-type="html" source="development/menu/data" data-height="full" class="__appinity__">
        <thead>
            <tr>
                <th>ln{menu}</th>
                <th>ln{target_url}</th>
                <th width="60" class="text-center">#</th>
                <th width="70" class="text-center">ln{aktif}</th>
                <th width="90" class="text-center">ln{aksi}</th>
            </tr>
        </thead>
    </app-table>
</app-card>
<app-modal title="${title}" id="modal-default" scrollable="false">
    <form app-link="default" id="form" autocomplete="off">
        <app-input type="hidden" name="id" />
        <app-input-default rules="menu" size="3:9" />
        <app-select class="select2" name="parent_id" label="ln{submenu_dari}"></app-select>
        <app-input name="nama" label="ln{menu}" />
        <app-input name="deskripsi" label="ln{deskripsi}" />
        <app-input name="target" label="ln{target_url}" />
        <app-input-group label="ln{akses}">
            <app-input type="checkbox" name="_input" value="1" label="ln{tambah}" size="3" />
            <app-input type="checkbox" name="_edit" value="1" label="ln{edit}" size="3" />
            <app-input type="checkbox" name="_delete" value="1" label="ln{hapus}" size="3" />
            <app-input type="tags" name="_additional" label="ln{tambahan}" size="12" />
        </app-input-group>
        <app-select class="select2" label="ln{referensi}" name="ref">
            <option value=""></option>
            each(ref => r)
            <option value="{r.value}">{r.label}</option>
            endEach
        </app-select>
        <app-input type="icon" name="icon" label="ln{ikon}" size="3:6" />
        <app-input type="text" name="urutan" label="ln{urutan}" size="3:6" />
        <app-input type="switch" name="is_active" value="1" checked="1" label="ln{aktif}" />
    </form>
    <footer-form />
</app-modal>
<app-modal title="${title}" subtitle="ln{urutkan_menu}" id="modal-sort">
    <footer>
        <button type="button" class="btn btn-theme" data-bs-dismiss="modal">ln{batal}</button>
        <button type="button" class="btn btn-app" id="save-sort">ln{simpan}</button>
    </footer>
</app-modal>
<script src="<?=asset_url('js/jquery.sortable.min.js');?>"></script>
<script>
function defaultBeforeLoad(e, id) {
    var data    = getTableData(e).html;
    var opt     = '<option value=""></option>';
    $(data).each(function(){
        if(typeof $(this).attr('data-menu-id') != 'undefined') {
            var dataID      = $(this).attr('data-menu-id');
            var dataParent  = $(this).attr('data-menu-parent');
            var dataMenu    = $(this).attr('data-menu-name');
            if(dataParent != '0') dataMenu = ' &nbsp; &nbsp; '+ dataMenu;
            if(id != dataID && id != dataParent) {
                opt += '<option value="'+dataID+'">'+dataMenu+'</option>';
            }
        }
    });
    $('#parent_id').html(opt).trigger('change');
}
$('.btn-sort').click(function(){
    $.get(baseURL('development/menu/get-sort'),function(r){
        $('#modal-sort .modal-body')[0].innerHTML = r;
        modal('modal-sort').show();
        $('ol.sortable').nestedSortable({
            forcePlaceholderSize: true,
            handle: 'div',
            helper:	'clone',
            items: 'li',
            opacity: .6,
            placeholder: 'placeholder mb-2',
            revert: 250,
            tabSize: 25,
            tolerance: 'pointer',
            toleranceElement: '> div',
            maxLevels: 4,
            isTree: true,
            expandOnHover: 700,
            isAllowed: function(item, parent, dragItem) {
                var x = true;
                if(dragItem.hasClass('position')) {
                    x = false;
                } else if(dragItem.hasClass('module')) {
                    if(typeof parent == 'undefined' || !parent.hasClass('position')) x = false;
                } else {
                    if(typeof parent == 'undefined') x = false;
                    if(x && parent.closest('.module').attr('data-module') != dragItem.attr('data-module')) x = false;
                }
                return x;
            }
        });
    });
});
$(document).on('click','#save-sort',function(e){
    e.preventDefault();
    var serialized = $('ol.sortable').nestedSortable('serialize');
    $.ajax({
        url : baseURL('development/menu/save-sort'),
        type : 'post',
        data : serialized,
        dataType : 'json',
        success : function(response) {
            cAlert.open(response.message,response.status,'refreshData:default');
        }
    });
});
</script>