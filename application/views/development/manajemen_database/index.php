<action-header>
    <a href="base_url{development/manajemen_database/sql}" class="btn btn-app text-nowrap"><i class="fa-code"></i><span class="ms-2">ln{perintah_sql}</span></a>
</action-header>
<right-panel>
    <panel-header>
        <ul class="nav nav-tabs">
            <li class="nav-item w-50">
                <a class="nav-link text-center active" href="base_url{development/manajemen-database}">ln{tabel}</a>
            </li>
            <li class="nav-item w-50">
                <a class="nav-link text-center" href="base_url{development/manajemen-database/relasi}">ln{relasi}</a>
            </li>
        </ul>
    </panel-header>
    <?php if($access['input']) { ?>
    <a href="javascript:;" class="btn btn-app d-block mb-3" id="btn-add-table"><i class="fa-plus"></i> ln{tambah_tabel}</a>
    <?php } ?>
    <?php foreach($table as $t) { ?>
    <a href="<?=base_url('development/manajemen-database?table='.$t.'&show='.$show);?>" class="list-item<?php if($t == $cur_table) echo ' active';?>">
        <i class="fa-table"></i><span><?=$t;?></span>
    </a>
    <?php } ?>
</right-panel>


<?php if($cur_table == 'tableList') { ?>

<app-card>
    <app-table class="__appinity__" data-height="full">
        <thead>
            <tr>
                <th>ln{nama_tabel}</th>
                <th>ln{engine}</th>
                <th>ln{collation}</th>
                <th>ln{jumlah_data}</th>
                <th>ln{auto_increment}</th>
                <th>ln{komentar}</th>
            </tr>
        </thead>
        <tbody>
            each(table_detail => t)
            <tr>
                <td><a href="base_url{development/manajemen-database?table=}{t.TABLE_NAME}">{t.TABLE_NAME}</a></td>
                <td>{t.ENGINE}</td>
                <td>{t.TABLE_COLLATION}</td>
                <td>{t.TABLE_ROWS}</td>
                <td>{t.AUTO_INCREMENT}</td>
                <td>{t.TABLE_COMMENT}</td>
            </tr>
            endEach
        </tbody>
    </app-table>
</app-card>

<?php } else { ?>

<app-card>
    <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
        <li class="nav-item">
            <a href="<?=base_url('development/manajemen-database?table='.$cur_table);?>" class="nav-link<?php if($show == 'data') echo ' active';?>">ln{data}</a>
        </li>
        <li class="nav-item">
            <a href="<?=base_url('development/manajemen-database?table='.$cur_table).'&show=structure';?>" class="nav-link<?php if($show == 'structure') echo ' active';?>">ln{struktur}</a>
        </li>
        <li class="nav-item">
            <a href="<?=base_url('development/manajemen-database?table='.$cur_table).'&show=rules';?>" class="nav-link<?php if($show == 'rules') echo ' active';?>">ln{rules}</a>
        </li>
        <li class="nav-item">
            <a href="<?=base_url('development/manajemen-database?table='.$cur_table).'&show=form';?>" class="nav-link<?php if($show == 'form') echo ' active';?>">ln{form_builder}</a>
        </li>
    </ul>
    <div class="tab-content p-0" id="myTabContent">
        <div class="tab-pane fade show active">
            <?php if($show == 'data') { ?>
            
            <?php if($access['input'] && !in_array($cur_table,$primary_table)) { ?>
            <div id="data-control" class="d-flex mb-2">
                <div class="ms-auto">
                    <button class="btn btn-success btn-input" type="button" app-link="default"><i class="fa-plus me-2"></i>ln{tambah_data}</button>
                </div>
            </div>
            <?php } ?>

            <app-table table="${cur_table}" class="__appinity__" data-height="full" data-height-include="#myTab|#data-control" data-action-select="defaultControl">
                <thead>
                    <tr>
                        each(fields => f)
                        <th data-content="{f.name}">{f.name}</th>
                        endEach
                        <?php if(!in_array($cur_table,$primary_table)) { ?>
                        <th data-content="button" width="80">ln{aksi}</th>
                        <?php } ?>
                    </tr>
                </thead>
            </app-table>

            <?php } elseif($show == 'structure') { ?>

            <div id="structure-control" class="d-flex mb-2">
                <?php if($access['edit'] || $access['delete']) { ?>
                <div id="cb-structure-action" class="d-none">
                    <?php if($access['edit']) { ?>
                    <button class="btn btn-warning" type="button" id="edit-structure"><i class="fa-edit me-2"></i>ln{edit}</button>
                    <?php } if($access['delete']) { ?>
                    <button class="btn btn-danger" type="button" id="delete-structure"><i class="fa-trash me-2"></i>ln{hapus}</button>
                    <?php } ?>
                </div>
                <?php } if($access['input']) { ?>
                <div class="ms-auto">
                    <button class="btn btn-success" type="button" id="add-field"><i class="fa-plus me-2"></i>ln{tambah_field}</button>
                </div>
                <?php } ?>
            </div>
            
            <app-table class="__appinity__" data-height="full" data-height-include="#myTab|#structure-control">
                <thead>
                    <tr>
                        <?php if(!in_array($cur_table,$primary_table) && ($access['edit'] || $access['delete'])) { ?>
                        <th width="30">&nbsp;</th>
                        <?php } ?>
                        <th>ln{nama_field}</th>
                        <th>ln{tipe}</th>
                        <th>ln{panjang}</th>
                        <th class="text-center">Unsigned</th>
                        <th class="text-center">Null</th>
                        <th>Key</th>
                        <th>Default</th>
                        <th class="text-center">Auto Increment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($structure as $s) { ?>
                    <tr>
                        <?php if(!in_array($cur_table,$primary_table) && ($access['edit'] || $access['delete'])) { ?>
                        <td class="text-center">
                            <div class="form-check ps-0 d-inline-block">
                                <input type="checkbox" class="ms-0 form-check-input cb-structure" 
                                    value="<?=$s['field'];?>" 
                                    data-table="<?=$cur_table;?>" 
                                    data-type="<?=$s['type'];?>" 
                                    data-length="<?=$s['length'];?>" 
                                    data-unsigned="<?=$s['unsigned'] ? 1 : 0;?>"
                                    data-null="<?=$s['null'] ? 1 : 0;?>"
                                    data-key="<?=$s['key'];?>"
                                    data-default="<?=$s['default'];?>"
                                    data-auto_increment="<?=$s['auto_increment'] ? 1 : 0;?>" />
                            </div>
                        </td>
                        <?php } ?>
                        <td><?=$s['field'];?></td>
                        <td><?=$s['type'];?></td>
                        <td><?=$s['length'];?></td>
                        <td class="text-center"><?=$s['unsigned'] ? '<i class="fa-check"></i>' : '';?></td>
                        <td class="text-center"><?=$s['null'] ? '<i class="fa-check"></i>' : '';?></td>
                        <td><?=$s['key'];?></td>
                        <td><?=$s['default'];?></td>
                        <td class="text-center"><?=$s['auto_increment'] ? '<i class="fa-check"></i>' : '';?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </app-table>

            <?php } elseif($show == "rules") { ?>

            <form autocomplete="off" method="post" action="base_url{development/manajemen-database/save-rules}" data-submit="ajax">
                <app-input-default size="3:9" size-param="sm" />
                <app-input type="hidden" name="table" value="${cur_table}" />
                <div class="row">
                    <?php foreach($structure as $s) { if($s['key'] != 'PRIMARY KEY' && !in_array($s['field'],['created_at','created_by','updated_at','updated_by'])) { ?>
                    <div class="col-md-6 mb-4">
                        <div class="card card-collapse open">
                            <div class="card-header fw-bold mb-1"><?=$s['field'];?></div>
                            <div class="card-body">
                                <app-input type="hidden" name="field[]" value="<?=$s['field'];?>" />
                                <app-input type="tags" name="validation[]" label="ln{validasi}" value="<?=$s['validation'];?>" class="validation" />
                                <app-input name="dir[]" label="ln{direktori_unggah}" prefix="<?=upload_path();?>" value="<?=$s['dir'];?>" />
                            </div>
                        </div>
                    </div>
                    <?php }} ?>
                </div>
                <button class="btn btn-app d-block w-100" type="submit">ln{simpan}</button>
            </form>

            <?php } elseif($show == 'form') { ?>

            <div class="row">
                <div class="col-md-8 mb-4 mb-md-0">
                    <div class="card">
                        <div class="card-header">ln{form}</div>
                        <div class="card-body">
                            <form autocomplete="off" method="post" action="base_url{development/manajemen-database/save-form}" data-submit="ajax" data-beforeSave="reIndexList">
                                <input type="hidden" name="cur_table" value="${cur_table}" />
                                <ol class="sortable" id="form-container" data-table="${cur_table}"></ol>
                                <button class="btn btn-app d-block w-100" type="submit">ln{simpan}</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card sticky-top">
                        <div class="card-header">ln{field}</div>
                        <div class="card-body">
                            <?php foreach($structure as $s) {  if($s['key'] != 'PRIMARY KEY' && !in_array($s['field'],['created_at','created_by','updated_at','updated_by'])) { ?>
                            <div class="form-check">
                                <input class="form-check-input chk-form-builder" type="checkbox" value="<?=$s['field'];?>" data-type="<?=$s['type'];?>" data-length="<?=$s['length'];?>" id="chk-<?=$s['field'];?>">
                                <label class="form-check-label" for="chk-<?=$s['field'];?>">
                                    <?=$s['field'];?>
                                </label>
                            </div>
                            <?php }} ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php } ?>
        </div>
    </div>
</app-card>
<?php if(!in_array($cur_table,$primary_table)) { ?>
<div data-action-id="defaultControl">
    <?php if($access['delete']) { ?>
    <button class="btn btn-danger" data-action="deleteSelected"><i class="fa-trash-alt"></i> ln{hapus}</button>
    <?php } ?>
</div>

<?php }} ?>

<app-modal size="xl" id="modal-add-table" title="ln{tambah_tabel}">
    <form id="form-add-table" autocomplete="off" method="post" action="base_url{development/manajemen-database/add-table}" data-callback="redirect:href">
        <app-input-default size="3:9" />
        <app-input name="table" id="add-table" validation="required|alphanumeric" label="ln{nama_tabel}" />
        <div class="table-responsive mb-3">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th><button type="button" class="btn btn-success" id="add-row"><i class="fa-plus"></i></button></th>
                        <th style="min-width:150px">ln{nama_field}</th>
                        <th style="min-width:180px">ln{tipe}</th>
                        <th style="min-width:120px">ln{panjang}</th>
                        <th style="min-width:150px">Default</th>
                        <th class="text-center" style="min-width:100px">Unsigned</th>
                        <th class="text-center" style="min-width:100px">Null</th>
                        <th style="min-width:180px">Key</th>
                        <th class="text-center" style="min-width:100px">Auto Increment</th>
                    </tr>
                </thead>
                <tbody id="fields"></tbody>
            </table>
        </div>
        <app-input type="checkbox" name="timestamp" id="add-timestamp" label="ln{info_data}" sub-label="(create_at, create_by, update_at, update_by)" value="1" checked="1" />
    </form>
    <footer-form />
</app-modal>

<?php if($show == 'data') { ?>
<app-modal size="lg" title="${cur_table}">
    <form id="form-data" autocomplete="off" app-link="default" table="${cur_table}">
        <app-input-default size="3:9" />
        <?php foreach($fields as $f) {
            if($f->primary_key) {
                echo '<app-input type="hidden" name="last_'.$f->name.'" id="id-last_'.$f->name.'" />';
            }
            if(substr($f->type,-3) == 'int' || in_array($f->type,['decimal','float','double'])) {
                echo '<app-input type="number" name="'.$f->name.'" id="id-'.$f->name.'" label="'.$f->name.'" />';
            } elseif(substr($f->type,-4) == 'text') {
                echo '<app-input type="textarea" name="'.$f->name.'" id="id-'.$f->name.'" label="'.$f->name.'" />';
            } elseif($f->type == 'date') {
                echo '<app-input type="date" name="'.$f->name.'" id="id-'.$f->name.'" label="'.$f->name.'" />';
            } elseif($f->type == 'datetime') {
                echo '<app-input type="datetime" name="'.$f->name.'" id="id-'.$f->name.'" label="'.$f->name.'" />';
            } else {
                echo '<app-input name="'.$f->name.'" id="id-'.$f->name.'" label="'.$f->name.'" />';
            }
        } ?>
        <app-input-default size="3:9" />
    </form>
    <footer-form />
</app-modal>
<?php } elseif($show == 'structure') { ?>
<app-modal size="xl" id="modal-add-field" title="ln{tambah_field}">
    <form id="form-add-field" autocomplete="off" method="post" action="base_url{development/manajemen-database/add-field}" data-callback="reload">
        <app-input-default size="3:9" />
        <app-input type="hidden" name="table" id="add-field-table" value="${cur_table}" />
        <app-select class="select2" name="after" label="ln{setelah}">
            <?php foreach($structure as $k => $s) { ?>
            <option value="<?=$s['field'];?>"<?php if($k+1 == count($structure)) echo ' selected';?>><?=$s['field'];?></option>
            <?php } ?>
        </app-select>
        <div class="table-responsive mb-3">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th><button type="button" class="btn btn-success" id="add-row-field"><i class="fa-plus"></i></button></th>
                        <th style="min-width:150px">ln{nama_field}</th>
                        <th style="min-width:180px">ln{tipe}</th>
                        <th style="min-width:120px">ln{panjang}</th>
                        <th style="min-width:150px">Default</th>
                        <th class="text-center" style="min-width:100px">Unsigned</th>
                        <th class="text-center" style="min-width:100px">Null</th>
                        <th style="min-width:180px">Key</th>
                        <th class="text-center" style="min-width:100px">Auto Increment</th>
                    </tr>
                </thead>
                <tbody id="fields-add"></tbody>
            </table>
        </div>
    </form>
    <footer-form />
</app-modal>

<app-modal size="xl" id="modal-edit-field" title="ln{edit}">
    <form id="form-edit-field" autocomplete="off" method="post" action="base_url{development/manajemen-database/edit-field}" data-callback="reload">
        <app-input-default size="3:9" />
        <app-input type="hidden" name="table" id="add-field-table" value="${cur_table}" />
        <div class="table-responsive mb-3">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="min-width:150px">ln{nama_field}</th>
                        <th style="min-width:180px">ln{tipe}</th>
                        <th style="min-width:120px">ln{panjang}</th>
                        <th style="min-width:150px">Default</th>
                        <th class="text-center" style="min-width:100px">Unsigned</th>
                        <th class="text-center" style="min-width:100px">Null</th>
                        <th style="min-width:180px">Key</th>
                        <th class="text-center" style="min-width:100px">Auto Increment</th>
                    </tr>
                </thead>
                <tbody id="fields-edit"></tbody>
            </table>
        </div>
    </form>
    <footer-form />
</app-modal>
<?php } elseif($show == 'form') { ?>
<select id="list-table-form" class="d-none">
    <optgroup label="ln{tabel}">
    <?php foreach($table as $t) { if($t != $cur_table) { ?>
    <option value="<?=$t;?>"><?=$t;?></option>
    <?php }} ?>
    </optgroup>
    <option value="manual">ln{input_manual}</option>
</select>
<script src="<?=asset_url('js/jquery.sortable.min.js');?>"></script>
<?php } ?>
<table id="template-add-table" class="d-none">
    <tbody>
        <tr>
            <td><button type="button" class="btn btn-danger btn-remove"><i class="fa-times"></i></button></td>
            <td><input type="text" name="field[]" class="form-control field" data-validation="required|alphanumeric" /><input type="hidden" name="last_field[]" class="last_field" /></td>
            <td>
                <select name="type[]" class="form-select type" data-validation="required">
                    <optgroup label="Numeric">
                        <option value="tinyint">TINYINT</option>
                        <option value="smallint">SMALLINT</option>
                        <option value="mediumint">MEDIUMINT</option>
                        <option value="int" selected>INT</option>
                        <option value="bigint">BIGINT</option>
                        <option value="" disabled>-</option>
                        <option value="decimal">DECIMAL</option>
                        <option value="float">FLOAT</option>
                        <option value="double">DOUBLE</option>
                        <option value="" disabled>-</option>
                        <option value="boolean">BOOLEAN</option>
                    </optgroup>
                    <optgroup label="Date and Time">
                        <option value="datetime">DATETIME</option>
                        <option value="date">DATE</option>
                        <option value="time">TIME</option>
                        <option value="timestamp">TIMESTAMP</option>
                    </optgroup>
                    <optgroup label="String">
                        <option value="char">CHAR</option>
                        <option value="varchar">VARCHAR</option>
                        <option value="" disabled>-</option>
                        <option value="tinytext">TINYTEXT</option>
                        <option value="text">TEXT</option>
                        <option value="mediumtext">MEDIUMTEXT</option>
                        <option value="longtext">LONGTEXT</option>
                    </optgroup>
                </select>
            </td>
            <td><input type="text" name="length[]" class="form-control length" data-validation="numeric" /></td>
            <td><input type="text" name="default[]" class="form-control default" /></td>
            <td class="text-center">
                <div class="form-check ps-0 d-inline-block">
                    <input type="checkbox" class="ms-0 form-check-input unsigned" name="unsigned[]" value="1" />
                </div>
            </td>
            <td class="text-center">
                <div class="form-check ps-0 d-inline-block">
                    <input type="checkbox" class="ms-0 form-check-input null" name="null[]" value="1" />
                </div>
            </td>
            <td>
                <select name="key[]" class="form-select key" data-search="false">
                    <option value=""></option>
                    <option value="pk">PRIMARY KEY</option>
                    <option value="fk">FOREIGN KEY</option>
                </select>
            </td>
            <td class="text-center">
                <div class="form-check ps-0 d-inline-block">
                    <input type="checkbox" class="ms-0 form-check-input auto_increment" name="auto_increment[]" value="1" />
                </div>
            </td>
        </tr>
    </tbody>
</table>
<script>
var procBuilder = false;
$('#btn-add-table').click(function(){
    $('#fields').html('');
    $('#add-row').click();
    $('#fields .field').val('id');
    $('#fields .type').val('bigint').trigger('change');
    $('#fields .key').val('pk').trigger('change');
    $('#fields .unsigned').prop('checked',true);
    $('#fields .auto_increment').prop('checked',true);
    modal('modal-add-table').show();
});
$(document).on('click','#add-row',function(){
    var index   = rand();
    var content = $('#template-add-table tbody').html().replace(/form-select/g, 'form-select select2').replace(/\[\]/g, '['+index+']');
    $('#fields').append(content);
    select2Init();
});
$(document).on('click','.btn-remove',function(){
    if($('#modal-add-table').hasClass('show') && $('#fields').children('tr').length > 1) {
        $(this).closest('tr').remove();
    } else if($('#modal-add-field').hasClass('show') && $('#fields-add').children('tr').length > 1) {
        $(this).closest('tr').remove();
    }
});
$(document).on('change','#fields .key',function(){
    if($(this).val() == 'pk') {
        var c = 0;
        $('#fields .key').each(function(){
            if($(this).val() == 'pk') c++;
        });
        if(c > 1) $(this).val('').trigger('change');
    }
});
$(document).on('click','#fields .auto_increment',function(){
    if($(this).is(':checked') && $('#fields .auto_increment:checked').length > 1) {
        $(this).prop('checked',false);        
    }
});
$(document).on('click','.cb-structure',function(){
    if($('.cb-structure:checked').length > 0) {
        $('#cb-structure-action').removeClass('d-none');
    } else {
        $('#cb-structure-action').addClass('d-none');
    }
});

$('#add-field').click(function(){
    $('#fields-add').html('');
    $('#add-row-field').click();
    modal('modal-add-field').show();
});
$(document).on('click','#add-row-field',function(){
    var index   = rand();
    var content = $('#template-add-table tbody').html().replace(/form-select/g, 'form-select select2').replace(/\[\]/g, '['+index+']');
    $('#fields-add').append(content);
    $('#fields-add select [value="pk"]').remove();
    select2Init();
});
$(document).on('change','#fields-add .key',function(){
    if($(this).val() == 'pk') {
        var c = 0;
        $('#fields-add .key').each(function(){
            if($(this).val() == 'pk') c++;
        });
        if(c > 1) $(this).val('').trigger('change');
    }
});
$(document).on('click','#fields-add .auto_increment',function(){
    if($(this).is(':checked') && $('#fields-add .auto_increment:checked').length > 1) {
        $(this).prop('checked',false);        
    }
});
$(document).on('click','#edit-structure',function(){
    $('#fields-edit').html('');
    if($('.cb-structure:checked').length > 0) {
        $('.cb-structure:checked').each(function(){
            var index   = rand();
            var content = $($('#template-add-table tbody').html().replace(/form-select/g, 'form-select select2').replace(/\[\]/g, '['+index+']'));
            content.children().first().remove();
            $('#fields-edit').append(content[0].outerHTML);
            $('#fields-edit select [value="pk"]').remove();
            var t       = $(this);
            var lastTR  = $('#fields-edit').children().last();
            lastTR.find('.field').val(t.attr('value'));
            lastTR.find('.last_field').val(t.attr('value'));
            lastTR.find('.type').val(t.attr('data-type'));
            lastTR.find('.length').val(t.attr('data-length'));
            lastTR.find('.default').val(t.attr('data-default'));

            if(t.attr('data-unsigned') == 1) lastTR.find('.unsigned').prop('checked',true);
            if(t.attr('data-null') == 1) lastTR.find('.null').prop('checked',true);
            if(t.attr('data-auto_increment') == 1) lastTR.find('.auto_increment').prop('checked',true);

            if(t.attr('data-key') == 'FOREIGN KEY') lastTR.find('.key').val('fk');
            else if(t.attr('data-key') == 'PRIMARY KEY') lastTR.find('.key').removeClass('select2').addClass('d-none');
        });
        select2Init();
        modal('modal-edit-field').show();
    }
});
$(document).on('click','#fields-edit .auto_increment',function(){
    if($(this).is(':checked') && $('#fields-edit .auto_increment:checked').length > 1) {
        $(this).prop('checked',false);        
    }
});
$(document).on('click','#delete-structure',function(){
    if($('.cb-structure:checked').length > 0) {
        cConfirm.open($('.cb-structure:checked').length+' '+lang.data_terpilih+"\n\n" + lang.apakah_anda_yakin_ingin_menghapus_data_ini+'?','deleteField');
    }
});
function deleteField() {
    var fields              = [];
    var table               = '';
    $('.cb-structure:checked').each(function(){
        fields.push($(this).attr('value'));
        table   = $(this).attr('data-table');
    });
    var data            = {
        fields          : fields,
        table           : table
    };
    $.ajax({
        url : baseURL('development/manajemen-database/delete-field'),
        data : data,
        type : 'post',
        dataType : 'json',
        success : function(r) {
            if(r.status == 'success') {
                cAlert.open(r.message,r.status,'reload');
            } else {
                cAlert.open(r.message,r.status);
            }
        }
    })
}
$(document).ready(function(){
    if($('.validation').length > 0) {
        $('.validation').each(function(){
            var newVal = $(this).val().replace('[[','{').replace(']]','}');
            console.log(newVal);
            $(this).val(newVal);
            tagsInit();
        });
    }
    if($('ol#form-container.sortable').length > 0) {
        $('ol#form-container.sortable').append('<li class="text-center"><i class="d-inline-block fa-spinner-third fa-spin me-2 text-app f-2x"></i></li>');
        $('.chk-form-builder').attr('disabled',true);
        var url = baseURL('development/manajemen-database/get-form?r=' + $('#form-container').attr('data-table'));
        $.getJSON(url,function(r){
            $('ol#form-container.sortable').html('');
            $('.chk-form-builder').removeAttr('disabled');

            if(typeof r.field == 'object') {
                $.each(r.field, function(x,y){
                    $('.chk-form-builder[value="'+x+'"]').click();
                    setTimeout(function(){
                        procBuilder = true;
                        var z = $('li[data-field="'+x+'"]');
                        z.find('.x-label').val(y.label);
                        z.find('.x-type').val(y.type).trigger('change');
                        if(!y.showOnTable) {
                            z.find('.x-show_table').prop('checked',false);
                        }
                        if(typeof y.ref !== 'undefined') {
                            z.find('.x-table').val(y.ref).trigger('change');
                            if(y.ref == 'manual') {
                                z.find('.x-opt_tags').val(y.refData);
                                tagsInit();
                            } else {
                                if(typeof r.ref[y.ref] == 'object') {
                                    var opt     = '<option value=""></option>';
                                    $.each(r.ref[y.ref],function(a,b){
                                        opt += '<option value="'+b+'">'+b+'</option>';
                                    });
                                    z.find('.x-opt_value').html(opt).val(y.refValue).trigger('change');
                                    z.find('.x-opt_label').html(opt).val(y.refLabel).trigger('change');
                                }
                            }
                        } else if(typeof y.imgWidth !== 'undefined' && y.imgHeight !== 'undefined') {
                            z.find('.x-img_width').val(y.imgWidth);
                            z.find('.x-img_height').val(y.imgHeight);
                            if(typeof y.imgCrop !== 'undefined' && y.imgCrop) {
                                z.find('.x-img_crop').prop('checked',true);
                            }
                        }
                        procBuilder = false;
                    },300);
                });
            }
        });
    }
});
$(document).on('change','.chk-form-builder',function(){
    var idx = rand();
    if($(this).is(':checked')) {
        var type    = $(this).attr('data-type');
        var build   =   '<li data-field="'+$(this).val()+'" class="mb-4">' +
                            '<div class="card card-collapse open">' +
                                '<div class="card-header">'  +
                                    '<div class="card-title fw-semi-bold mb-1">'+$(this).val()+'</div>' +
                                '</div>' +
                                '<div class="card-body">' +
                                    '<input type="hidden" name="field[]" value="'+$(this).val()+'" />' +
                                    '<div class="row mb-3">' +
                                        '<label class="col-md-3 required form-label">'+lang.label+'</label>' +
                                        '<div class="col-md-9">' +
                                            '<input type="text" class="form-control x-label" name="label[]" data-validation="required" value="'+$(this).val()+'" />' +
                                        '</div>' +
                                    '</div>' +
                                    '<div class="row mb-3">' +
                                        '<label class="col-md-3 form-label">'+lang.tampil_di_tabel+'</label>' +
                                        '<div class="col-md-9">' +
                                            '<div class="form-check form-switch">' +
                                                '<input type="checkbox" class="form-check-input x-show_table" name="show_table[]" value="1" checked />' +
                                            '</div>' +
                                        '</div>' +
                                    '</div>' +
                                    '<div class="row mb-3">' +
                                        '<label class="col-md-3 form-label">'+lang.tipe_inputan+'</label>' +
                                        '<div class="col-md-9">' +
                                            '<select class="form-select select2 select-type x-type" name="tipe[]" data-search="false">';
                                            if(type == 'date') {
                                                build += '<option value="date">DATE</option>';
                                                build += '<option value="current_date">CURRENT DATE</option>';
                                            } else if(type == 'datetime') {
                                                build += '<option value="datetime">DATE TIME</option>';
                                                build += '<option value="date">DATE</option>';
                                                build += '<option value="current_date">CURRENT DATE</option>';
                                            } else if(type.indexOf('int') !== -1 || type == 'decimal' || type == 'double' || type == 'float') {
                                                if(type == 'tinyint' && $(this).attr('data-length') == '1') {
                                                    build += '<option value="switch">SWITCH</option>';
                                                } else {
                                                    build += '<option value="number">NUMBER</option>';
                                                    build += '<option value="currency">CURRENCY</option>';
                                                    if(type.indexOf('int') !== -1) {
                                                        build += '<option value="range">RANGE</option>';
                                                        build += '<option value="select">SELECT</option>';
                                                    }
                                                }
                                            } else {
                                                build += '<option value="text">TEXT</option>';
                                                build += '<option value="textarea">TEXTAREA</option>';
                                                build += '<option value="password">PASSWORD</option>';
                                                build += '<option value="select">SELECT</option>';
                                                build += '<option value="icon">ICON</option>';
                                                build += '<option value="tags">TAGS</option>';
                                                build += '<option value="color">COLOR</option>';
                                                build += '<option value="fileupload">FILEUPLOAD</option>';
                                                build += '<option value="imageupload">IMAGEUPLOAD</option>';
                                            }
                                            build += '</select>' +
                                        '</div>' +
                                    '</div>' +
                                    '<div class="row mb-3 ref d-none">' +
                                        '<label class="col-md-3 form-label required">'+lang.referensi+'</label>' +
                                        '<div class="col-md-9">' +
                                            '<select class="form-select select2 select-ref x-table" name="table[]" data-validation=""><option value=""></option>';
                                            build += $('#list-table-form').html();
                                            build += '</select>' +
                                        '</div>' +
                                    '</div>' +
                                    '<div class="row mb-3 ref ref-table d-none">' +
                                        '<div class="col-6 col-md-4 offset-md-3">' +
                                            '<select class="form-select select2 select-opt x-opt_value" name="opt_value[]" data-validation="" placeholder="value"><option value=""></option></select>'+
                                        '</div>' +
                                        '<div class="col-6 col-md-5">' +
                                            '<select class="form-select select2 select-opt x-opt_label" name="opt_label[]" data-validation="" placeholder="label"><option value=""></option></select>'+
                                        '</div>' +
                                    '</div>' +
                                    '<div class="row mb-3 ref ref-tags d-none">' +
                                        '<div class="col-md-9 offset-md-3">' +
                                            '<input class="form-control input-tags x-opt_tags" name="opt_tags[]" data-validation="" />'+
                                        '</div>' +
                                    '</div>' +
                                    '<div class="row mb-3 img-ref d-none">' +
                                        '<div class="col-6 col-md-4 offset-md-3">' +
                                            '<div class="input-group">' +
                                                '<input type="text" class="form-control x-img_width" name="img_width[]" data-validation="numeric" placeholder="'+lang.lebar+'" />'+
                                                '<div class="input-group-text">px</div>' +
                                            '</div>' +
                                        '</div>' +
                                        '<div class="col-6 col-md-5">' +
                                            '<div class="input-group">' +
                                                '<input type="text" class="form-control x-img_height" name="img_height[]" data-validation="numeric" placeholder="'+lang.tinggi+'" />'+
                                                '<div class="input-group-text">px</div>' +
                                            '</div>' +
                                        '</div>' +
                                    '</div>' +
                                    '<div class="row mb-3 img-ref d-none">' +
                                        '<div class="col-md-9 offset-md-3">' +
                                            '<div class="form-check">' +
                                                '<input type="checkbox" class="form-check-input x-img_crop" value="1" name="img_crop[]" id="chk'+idx+'" />' +
                                                '<label class="form-check-label" for="chk'+idx+'">' + lang.sesuaikan_gambar + '</label>' +
                                            '</div>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</li>';
        $('#form-container').append(build);
        select2Init();
        tagsInit();
    } else {
        $('li[data-field="'+$(this).val()+'"]').remove();
    }
    sortable();
});
$(document).on('change','.select-type',function(){
    var cb = $(this).closest('.card-body');
    if($(this).val() == 'select') {
        cb.find('.ref').removeClass('d-none');
        cb.find('.select-ref').attr('data-validation','required').trigger('change');
        var fieldname = $(this).closest('li').attr('data-field');
        var fieldtype = $('.chk-form-builder[value="'+fieldname+'"]').attr('data-type');
        if(fieldtype.indexOf('int') !== -1 || fieldtype == 'decimal' || fieldtype == 'double' || fieldtype == 'float') {
            cb.find('option[value="manual"]').remove();
            cb.find('.ref-table').find('select').trigger('change');
        }
    } else {
        cb.find('.ref').addClass('d-none');
        cb.find('.select-ref').attr('data-validation','');
        cb.find('.select-opt').attr('data-validation','');
        cb.find('.x-opt_tags').attr('data-validation','');
    }
    if($(this).val() == 'imageupload') {
        cb.find('.img-ref').removeClass('d-none');
    } else {
        cb.find('.img-ref').addClass('d-none');
    }
});
$(document).on('change','.select-ref',function(){
    var cb = $(this).closest('.card-body');
    if(cb.find('.select-ref').val() == 'manual') {
        cb.find('.ref-table').addClass('d-none');
        cb.find('.ref-tags').removeClass('d-none');
        cb.find('.x-opt_tags').attr('data-validation','required');
        cb.find('.select-opt').attr('data-validation','');
    } else {
        cb.find('.ref-table').removeClass('d-none');
        cb.find('.ref-tags').addClass('d-none');
        cb.find('.select-opt').attr('data-validation','required');
        cb.find('.x-opt_tags').attr('data-validation','');
    }
});
$(document).on('change','.select-ref',function(){
    if(!procBuilder) {
        var target = $(this).closest('.card-body').find('.select-opt');
        $.getJSON(baseURL('development/manajemen-database/get-field?t=' + $(this).val()),function(r){
            var opt     = '<option value=""></option>';
            $.each(r,function(e,k){
                opt += '<option value="'+k+'">'+k+'</option>';
            });
            target.html(opt);
            target.trigger('change');
        });
    }
});
function sortable() {
    if($().sortable) {
        if($('ol.sortable').hasClass('ui-sortable')) {
            $('ol.sortable').sortable('destroy');
        }
        $('ol.sortable').sortable({
            forcePlaceholderSize: true,
            handle: '.card-header',
            helper:	'clone',
            items: 'li',
            opacity: .6,
            placeholder: 'placeholder mb-4 h-card',
            tolerance: 'pointer',
            toleranceElement: '> div'
        });
    }
}
function reIndexList() {
    var idxList = 0;
    $('ol.sortable li').each(function(){
        $(this).find('[name]').each(function(){
            var nm = $(this).attr('name');
            var xNM = nm.split('[');
            if(xNM.length == 2) {
                var newNM = xNM[0] + "["+idxList+"]";
                $(this).attr('name',newNM);
            }
        });
        idxList++;
    });
    return true;
}
</script>