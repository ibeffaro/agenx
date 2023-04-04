<div class="fw-bold f-150 mb-1"><?=lang('lupa_kata_sandi'); ?></div>
<p class="text-justify"><?=lang('lupa_kata_sandi_desc'); ?></p>
<form autocomplete="off" action="<?=base_url('auth/do-forgot');?>" method="post" data-submit="ajax" data-info="alert" data-callback="redirect:href">
    <app-input name="email" label="ln{alamat_surel}" validation="required|email" />
    <div class="row">
        <div class="col-6">
            <button type="submit" class="btn btn-app pl-4 pr-4"><?=lang('kirim_link');?></button>
        </div>
        <div class="col-6 text-end pt-1 fw-bold">
            <a href="<?=base_url('auth/login');?>"><?=lang('halaman_masuk');?></a>
        </div>
    </div>
</form>