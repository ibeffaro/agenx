<div class="fw-bold f-150 mb-1"><?=lang('reset_kata_sandi'); ?></div>
<?php if($valid) { ?>
<p class="text-justify"><?=lang('reset_kata_sandi_desc'); ?></p>
<form autocomplete="off" action="<?=base_url('auth/do-reset');?>" method="post" data-submit="ajax" data-info="alert" data-callback="redirect:href">
    <input type="hidden" name="token" value="<?=$id;?>" />
    <app-input type="password" name="password" label="ln{kata_sandi_baru}" validation="required|setting{password_validation}" />
    <app-input type="password" name="konfirmasi_password" label="ln{konfirmasi_kata_sandi}" validation="required|equal:password" />
    <div class="row">
        <div class="col-6">
            <button type="submit" class="btn btn-app pl-4 pr-4"><?=lang('reset_kata_sandi');?></button>
        </div>
        <div class="col-6 text-end pt-1 fw-bold">
            <a href="<?=base_url('auth/login');?>"><?=lang('halaman_masuk');?></a>
        </div>
    </div>
</form>
<?php } else { ?>
<p class="text-justify"><?=lang('reset_kata_sandi_invalid_desc'); ?></p>
<div class="row">
    <div class="col-6">
        <a class="btn btn-app" href="<?=base_url('auth/forgot-password');?>"><?=lang('lupa_kata_sandi');?></a>
    </div>
    <div class="col-6 text-end pt-1 fw-bold">
        <a href="<?=base_url('auth/login');?>"><?=lang('halaman_masuk');?></a>
    </div>
</div>
<?php } ?>