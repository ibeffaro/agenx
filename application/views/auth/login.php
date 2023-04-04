<div class="fw-bold f-150 mb-1"><?= lang('masuk'); ?></div>
<p class="text-justify"><?= lang('masuk_desc'); ?></p>
<form autocomplete="off" action="<?= base_url('auth/do-login'); ?>" method="post" data-submit="ajax" data-info="alert" data-callback="redirect:href">
    <app-input name="username" validation="required" label="ln{nama_pengguna}" />
    <app-input type="password-toggle" name="password" validation="required" label="ln{kata_sandi}" />
    <button type="submit" class="btn btn-app pl-4 pr-4"><?= lang('masuk'); ?></button>
</form>