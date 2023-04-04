<app-card>
    <app-table source="settings/user-roles/data" data-height="full" data-action-select="defaultControl" table="user_group" class="__appinity__">
        <thead>
            <tr>
                <th data-content="nama">ln{peran}</th>
                <th data-content="keterangan">ln{keterangan}</th>
                <th width="120" data-content="is_active" data-type="boolean" class="text-center">ln{aktif}</th>
                <th width="90" data-content="button" class="text-center">ln{aksi}</th>
            </tr>
        </thead>
    </app-table>
</app-card>
<div data-action-id="defaultControl">
    <?php if($access['delete']) { ?>
    <button class="btn btn-danger" data-action="deleteSelected"><i class="fa-trash-alt"></i> ln{hapus}</button>
    <?php } ?>
</div>
<app-modal size="xl" title="${title}">
    <form id="form" autocomplete="off" app-link="default">
        <app-input type="hidden" name="id" />
        <app-input-default size="3:9" rules="user_group" />
        <div class="tab-form">
            <div class="nav nav-pills" role="tablist">
                <button class="nav-link" id="tab-info" data-bs-toggle="pill" data-bs-target="#pane-info" type="button" role="tab" aria-controls="pane-info" aria-selected="false">ln{deskripsi}</button>
                <?php foreach($menu[0] as $m) { ?>
                <button class="nav-link" id="tab-module<?=$m['id'];?>" data-bs-toggle="pill" data-bs-target="#pane-module<?=$m['id'];?>" type="button" role="tab" aria-controls="pane-module<?=$m['id'];?>" aria-selected="false"><?=$m['nama'];?></button>
                <?php } ?>
            </div>
            <div class="tab-content" id="v-pills-tabContent">
                <div class="tab-pane fade" id="pane-info" role="tabpanel" aria-labelledby="tab-info">
                    <app-input name="nama" label="ln{peran}" />
                    <app-input type="textarea" name="keterangan" label="ln{keterangan}" />
                    <app-select class="select2" name="id_menu_default" label="ln{menu_beranda}">
                        <option value="0"></option>
                    </app-select>
                    <app-input type="switch" name="is_active" label="ln{aktif}" value="1" checked="1" />
                </div>
                <?php foreach($menu[0] as $m) { ?>
                <div class="tab-pane fade" id="pane-module<?=$m['id'];?>" role="tabpanel" aria-labelledby="tab-module<?=$m['id'];?>">
                    <?php foreach($menu[$m['id']] as $m1) {
                        if(count($menu[$m1['id']]) > 0) { ?>
                        <div class="row mb-3">
                            <label class="form-label col-12"><?=$m1['nama'];?></label>
                        </div>
                        <?php foreach($menu[$m1['id']] as $m2) { ?>
                        <div class="row mb-1 form-group">
                            <label class="form-label col-md-3 col-12 ps-md-5"><?=$m2['nama'];?></label>
                            <div class="col-md-2 col-12 mb-2">
                                <input type="hidden" name="id_menu[<?=$m2['id'];?>]" value="<?=$m2['id'];?>" />
                                <div class="form-check">
                                    <input class="form-check-input cb-role-all" id="all-<?=$m2['id'];?>" type="checkbox" value="1">
                                    <label class="form-check-label fw-bold" for="all-<?=$m2['id'];?>">ln{semua}</label>
                                </div>
                            </div>
                            <div class="col-md-7 col-12">
                                <div class="row">
                                    <div class="col-md-3 col-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input cb-role-child" id="view-<?=$m2['id'];?>" name="view[<?=$m2['id'];?>]" type="checkbox" value="1">
                                            <label class="form-check-label" for="view-<?=$m2['id'];?>">ln{lihat}</label>
                                        </div>
                                    </div>
                                    <?php if($m2['_input']) { ?>
                                    <div class="col-md-3 col-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input cb-role-child" id="input-<?=$m2['id'];?>" name="input[<?=$m2['id'];?>]" type="checkbox" value="1">
                                            <label class="form-check-label" for="input-<?=$m2['id'];?>">ln{tambah}</label>
                                        </div>
                                    </div>
                                    <?php } if($m2['_edit']) { ?>
                                    <div class="col-md-3 col-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input cb-role-child" id="edit-<?=$m2['id'];?>" name="edit[<?=$m2['id'];?>]" type="checkbox" value="1">
                                            <label class="form-check-label" for="edit-<?=$m2['id'];?>">ln{edit}</label>
                                        </div>
                                    </div>
                                    <?php } if($m2['_delete']) { ?>
                                    <div class="col-md-3 col-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input cb-role-child" id="delete-<?=$m2['id'];?>" name="delete[<?=$m2['id'];?>]" type="checkbox" value="1">
                                            <label class="form-check-label" for="delete-<?=$m2['id'];?>">ln{hapus}</label>
                                        </div>
                                    </div>
                                    <?php } if($m2['_additional']) { foreach(explode(',',$m2['_additional']) as $key_add => $add) { if(trim($add)) { ?>
                                    <div class="col-md-3 col-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input cb-role-child" id="delete-<?=$key_add;?>-<?=$m2['id'];?>" name="additional[<?=$m2['id'];?>][<?=strtolower(str_replace(' ','_',trim($add)));?>]" type="checkbox" value="1">
                                            <label class="form-check-label" for="delete-<?=$key_add;?>-<?=$m2['id'];?>"><?=ucwords(str_replace('_',' ',$add));?></label>
                                        </div>
                                    </div>
                                    <?php }}} ?>
                                </div>
                            </div>
                        </div>
                        <?php }} else { ?>
                        <div class="row mb-1 form-group">
                            <label class="form-label col-md-3 col-12"><?=$m1['nama'];?></label>
                            <div class="col-md-2 col-12 mb-2">
                                <input type="hidden" name="id_menu[<?=$m1['id'];?>]" value="<?=$m1['id'];?>" />
                                <div class="form-check">
                                    <input class="form-check-input cb-role-all" id="all-<?=$m1['id'];?>" type="checkbox" value="1">
                                    <label class="form-check-label fw-bold" for="all-<?=$m1['id'];?>">ln{semua}</label>
                                </div>
                            </div>
                            <div class="col-md-7 col-12">
                                <div class="row">
                                    <div class="col-md-3 col-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input cb-role-child" id="view-<?=$m1['id'];?>" name="view[<?=$m1['id'];?>]" type="checkbox" value="1">
                                            <label class="form-check-label" for="view-<?=$m1['id'];?>">ln{lihat}</label>
                                        </div>
                                    </div>
                                    <?php if($m1['_input']) { ?>
                                    <div class="col-md-3 col-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input cb-role-child" id="input-<?=$m1['id'];?>" name="input[<?=$m1['id'];?>]" type="checkbox" value="1">
                                            <label class="form-check-label" for="input-<?=$m1['id'];?>">ln{tambah}</label>
                                        </div>
                                    </div>
                                    <?php } if($m1['_edit']) { ?>
                                    <div class="col-md-3 col-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input cb-role-child" id="edit-<?=$m1['id'];?>" name="edit[<?=$m1['id'];?>]" type="checkbox" value="1">
                                            <label class="form-check-label" for="edit-<?=$m1['id'];?>">ln{edit}</label>
                                        </div>
                                    </div>
                                    <?php } if($m1['_delete']) { ?>
                                    <div class="col-md-3 col-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input cb-role-child" id="delete-<?=$m1['id'];?>" name="delete[<?=$m1['id'];?>]" type="checkbox" value="1">
                                            <label class="form-check-label" for="delete-<?=$m1['id'];?>">ln{hapus}</label>
                                        </div>
                                    </div>
                                    <?php } if($m1['_additional']) { foreach(explode(',',$m1['_additional']) as $key_add => $add) { if(trim($add)) { ?>
                                    <div class="col-md-3 col-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input cb-role-child" id="delete-<?=$key_add;?>-<?=$m1['id'];?>" name="additional[<?=$m1['id'];?>][<?=strtolower(str_replace(' ','_',trim($add)));?>]" type="checkbox" value="1">
                                            <label class="form-check-label" for="delete-<?=$key_add;?>-<?=$m1['id'];?>"><?=ucwords(str_replace('_',' ',$add));?></label>
                                        </div>
                                    </div>
                                    <?php }}} ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </form>
    <footer-form />
</app-modal>
<script>
    function defaultBeforeLoad(appLink) {
        $('form[app-link="'+appLink+'"] .nav-link').first().click();
    }
    function defaultAfterLoad(d, appLink) {
        $.each(d.akses,function(k,v){
            if(v._view == '1')      $('[name="view['+v.id_menu+']"]').prop('checked',true);
            if(v._input == '1')     $('[name="input['+v.id_menu+']"]').prop('checked',true);
            if(v._edit == '1')      $('[name="edit['+v.id_menu+']"]').prop('checked',true);
            if(v._delete == '1')    $('[name="delete['+v.id_menu+']"]').prop('checked',true);
            try {
                var additional  = JSON.parse(v._additional);
                if(typeof additional == 'object') {
                    $.each(additional,function(x,y){
                        if(y == '1') $('[name="additional['+v.id_menu+']['+x+']"]').prop('checked',true);
                    });
                }
            } catch {}
            checkCb($('[name="view['+v.id_menu+']"]'));
        });
    }
    function checkCb(e) {
        var cbAll       = e.closest('.form-group').find('.cb-role-child').length;
        var cbChecked   = e.closest('.form-group').find('.cb-role-child:checked').length;
        var el          = e.closest('.form-group').find('.cb-role-all');
        if(cbChecked == 0) {
            el.prop('indeterminate',false);
            el.prop('checked',false);
        } else if(cbAll == cbChecked) {
            el.prop('indeterminate',false);
            el.prop('checked',true);
        } else {
            el.prop('indeterminate',true);
            el.prop('checked',false);
        }
    }
    $(document).on('click','.cb-role-all',function(){
        if($(this).is(':checked')) {
            $(this).closest('.form-group').find('.cb-role-child').prop('checked',true);
        } else {
            $(this).closest('.form-group').find('.cb-role-child').prop('checked',false);
        }
    });
    $(document).on('click','.cb-role-child',function(){
        var $t = $(this);
        $t.closest('.form-group').find('.cb-role-child').each(function(){
            if($(this).attr('name').indexOf('view') != -1) {
                $t  = $(this);
            }
        });
        if($(this).is(':checked') && !$t.is(':checked')) {
            $t.prop('checked',true);
        } else if($(this).attr('name').indexOf('view') != -1 && !$(this).is(':checked')) {
            $(this).closest('.form-group').find('.cb-role-child').prop('checked',false);
        }
        checkCb($(this));
    });
</script>