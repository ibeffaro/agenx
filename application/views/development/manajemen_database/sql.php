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
    <a href="<?=base_url('development/manajemen-database?table='.$t);?>" class="list-item">
        <i class="fa-table"></i><span><?=$t;?></span>
    </a>
    <?php } ?>
</right-panel>

<app-card class="mb-4">
    <form autocomplete="off" id="form-query">
        <div class="alert alert-warning alert-dismissible fade show mb-2" role="alert">
            ln{hanya_berlaku_untuk_perintah_select_insert_update_delete_truncate_saja}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div class="hightlight-editor mb-3">
            <textarea name="query" id="query" spellcheck="false"></textarea>
            <pre><code class="language-sql" id="query-preview"></code></pre>
        </div>
        <button type="submit" class="btn btn-app">ln{proses_perintah}</button>
    </form>
</app-card>

<div id="result"></div>

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

<script src="<?=base_url('assets/js/highlight.min.js');?>"></script>
<script>
var xhrQuery = null;
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
$('#query').on('keyup keydown change',function(){
    $('#query-preview').html($(this).val());
    hljs.highlightAll();
});
$('#query').focus(function(){
    $(this).parent().addClass('focus');
});
$('#query').blur(function(){
    $(this).parent().removeClass('focus');
});

$('#form-query').submit(function(e){
    e.preventDefault();
    if(xhrQuery !== null) {
        xhrQuery.abort();
    }
    $('#form-query button').attr('disabled',true);
    xhrQuery = $.ajax({
        url : baseURL('development/manajemen-database/query'),
        data : $(this).serialize(),
        type : 'post',
        dataType : 'json',
        success : function(r) {
            xhrQuery = null;
            $('#form-query button').removeAttr('disabled');
            if(r.status == 'failed') {
                $('#result').html('<div class="alert alert-danger">'+r.message+'</div>');
            } else {
                if(typeof r.data == 'undefined') {
                    $('#result').html('<div class="alert alert-success">'+r.message+'</div>');
                } else {
                    var konten =    '<div class="card">' +
                                        '<div class="card-body">' +
                                            '<div class="table-responsive">' +
                                                '<table class="table table-bordered table-striped table-hover">' +
                                                    '<thead>' +
                                                        '<tr>';
                                                            $.each(r.data[0],function(k,v){
                                                                konten += '<th>' + k + '</th>';
                                                            });
                                                        konten += '</tr>' +
                                                    '</thead>' +
                                                    '<tbody>';
                                                    $.each(r.data, function(k,v){
                                                        konten += '<tr>';
                                                            $.each(r.data[k],function(x,y){
                                                                konten += '<td>'+y+'</td>';
                                                            });
                                                        konten += '</tr>'
                                                    });
                                                    konten += '</tbody>'
                                                '</table>' +
                                            '</div>' +
                                        '</div>' +
                                    '</div>';
                    $('#result')[0].innerHTML = konten;
                }
            }
        }, error : function() {
            xhrQuery = null;
            $('#result').html('<div class="alert alert-danger">'+lang.terjadi_kesalahan+'</div>');
            $('#form-query button').removeAttr('disabled');
        }
    });
});
</script>