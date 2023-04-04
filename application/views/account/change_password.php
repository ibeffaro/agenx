<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="fw-bold f-110 mb-1">ln{perbarui_kata_sandi}</div>
        <div>ln{perbarui_kata_sandi_desc}</div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="post" action="<?=base_url('account/save-password');?>" data-submit="ajax" data-callback="reload" autocomplete="off">
                    <?php if(user('expired_password')) { ?>
                    <div class="alert alert-danger"><?=lang('kata_sandi_expired_desc','','<strong>'.date_lang(user('change_password_at')).'</strong>');?></div>
                    <?php } ?>
                    <app-input-default size="3:9" size-param="lg" />
                    <app-input type="password" name="last_password" validation="required" label="ln{kata_sandi_lama}" />
                    <app-input type="password" name="password" validation="required|setting{password_validation}" label="ln{kata_sandi_baru}" />
                    <app-input type="password" name="konfirmasi_password" validation="required|equal:password" label="ln{konfirmasi_kata_sandi}" />
                    <div class="row">
                        <div class="offset-lg-3 col-lg-9">
                            <button type="submit" class="btn btn-app"><?=lang('simpan_perubahan');?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>