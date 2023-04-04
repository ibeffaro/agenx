<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="fw-bold f-110 mb-1">ln{informasi_profil}</div>
        <div>ln{informasi_profil_desc}</div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="post" id="form" action="<?=base_url('account/save-profile');?>" data-submit="ajax" data-callback="reload" autocomplete="off">
                    <app-input-default size="3:9" size-param="lg" />
                    <app-input name="nama" value="user{nama}" label="ln{nama}" validation="required" />
                    <app-input name="email" value="user{email}" label="ln{surel}" validation="required|email" />
                    <app-input name="telepon" value="user{telepon}" label="ln{telepon}" validation="phone" />
                    <app-input name="username" value="user{username}" label="ln{nama_pengguna}" disabled />
                    <app-input type="imageupload" name="foto" label="ln{foto}" value="user{foto}" data-width="256" data-crop="true" />
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