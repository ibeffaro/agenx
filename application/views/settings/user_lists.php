<app-card>
    <app-table source="settings/user-lists/data" data-height="full" data-action-select="defaultControl" table="user" class="__appinity__">
        <thead>
            <tr>
                <th width="300" data-content="nama" data-sub-content="email" data-thumbnail="foto">ln{nama}</th>
                <th data-content="username">ln{nama_pengguna}</th>
                <th data-content="id_group" data-filter="user-role">ln{peran}</th>
                <th width="120" data-content="is_active" data-type="boolean" class="text-center">ln{aktif}</th>
                <th data-content="button" width="90" class="text-center">ln{aksi}</th>
            </tr>
        </thead>
    </app-table>
</app-card>
<select data-filter-id="user-role">
    <option value=""></option>
    each(user_group => u)
    <option value="{u.id}">{u.nama}</option>
    endEach
</select>
<div data-action-id="defaultControl">
    <?php if($access['delete']) { ?>
    <button class="btn btn-danger" data-action="deleteSelected"><i class="fa-trash-alt"></i> ln{hapus}</button>
    <?php } ?>
</div>
<app-modal title="${title}" size="lg">
    <form id="form" app-link="default" autocomplete="off">
        <app-input type="hidden" name="id" />
        <app-input-default size="3:9" rules="user" />
        <div class="row">
            <div class="col-sm-3 d-sm-block d-none">
                <app-input type="imageupload" name="foto" data-width="200" data-height="200" data-crop="true" data-info="false" data-path="assets/uploads/user" />
            </div>
            <div class="col-sm-9">
                <app-input type="text" name="nama" label="ln{nama}" />
                <app-input type="text" name="email" label="ln{surel}" />
                <app-input type="text" name="telepon" label="ln{telepon}" />
                <app-select name="id_group" class="select2" label="ln{peran}">
                    <option value=""></option>
                    each(user_group => u)
                    <option value="{u.id}">{u.nama}</option>
                    endEach
                </app-select>
                <app-input type="text" name="username" label="ln{nama_pengguna}" />
                <app-input type="password-toggle" name="password" label="ln{kata_sandi}" />
                <app-input type="switch" name="is_active" label="ln{aktif}" value="1" checked="1" />
            </div>
        </div>
    </form>
    <footer-form />
</app-modal>
<script>
    function defaultBeforeLoad(appLink) {
        $('#password').parent().siblings('.form-label').addClass('required');
        var validation  = $('#password').attr('data-validation');
        if(validation.indexOf('required') == -1) {
            if(validation != '') validation += '|';
            validation      += 'required';
        }
        $('#password').attr('data-validation',validation);
    }
    function defaultAfterLoad(data,appLink) {
        $('#password').parent().siblings('.form-label').removeClass('required');
        var validation  = $('#password').attr('data-validation');
        validation      = validation.replace('|required','').replace('required','');
        $('#password').attr('data-validation',validation).val('');
    }
    $(document).on('click','.btn-unlock',function(e){
        e.preventDefault();
        var id = $(this).attr('data-val');
        cConfirm.open('Apakah anda yakin membuka kunci untuk user ini?','unlockUser',{
            id : id
        });
    });
    function unlockUser(e) {
        $.getJSON(baseURL('settings/user-lists/unlock?i='+e.id),function(r){
            if(r.status == 'success') {
                cAlert.open(r.message, r.status, 'refreshData:default');
            } else {
                cAlert.open(r.message, r.status);
            }
        });
    }
</script>