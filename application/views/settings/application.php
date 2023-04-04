<app-card>
    <form method="post" action="base_url{settings/application/save}" data-submit="ajax" data-callback="reload" autocomplete="off" spellcheck>
        <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="home" aria-selected="true">ln{umum}</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab" aria-controls="email" aria-selected="false">ln{surel}</button>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                <div class="row">
                    <div class="col-sm-6 mb-2">
                        <div class="fw-bold mb-3 text-uppercase">ln{aplikasi}</div>

                        <app-input-default size="3:9" size-param="lg" />

                        <app-input type="text" name="title" label="ln{judul}" validation="required" value="setting{title}" />
                        <app-input type="textarea" name="description" label="ln{deskripsi}" size="3:9" size-param="lg" value="setting{description}" />
                        <app-input type="imageupload" name="logo" value="asset_url{uploads/settings/}setting{logo}" label="ln{logo}" data-width="768" data-height="256" />
                        <app-input type="imageupload" name="favicon" value="asset_url{uploads/settings/}setting{favicon}" label="ln{favicon}" data-width="256" data-height="256" />
                        <app-input type="switch" name="background_login_active" value="1" checked="setting{background_login_active}" label="ln{background_login}" />
                        <app-input type="imageupload" name="background_login" value="asset_url{uploads/settings/}setting{background_login}" label="&nbsp;" />

                        <app-input-group label="ln{kustom_warna_utama}">
                            <app-input type="switch" name="custom_color_primary" size="2" size-param="none" value="1" checked="setting{custom_color_primary}" />
                            <app-input type="color" name="color_primary" size="10" size-param="none" value="setting{color_primary}" />
                        </app-input-group>

                        <app-input type="switch" label="ln{warna_utama_pada_header}" name="header_color_primary" value="1" checked="setting{header_color_primary}" />
                        <app-select class="select2" data-search="false" name="font_type" value="setting{font_type}" label="ln{jenis_font}">
                            each(fonts => f)
                                <option value="{f.key}">{f.label}</option>
                            endEach
                        </app-select>
                        <app-input type="range" name="font_size" value="setting{font_size}" label="ln{ukuran_font}" min="12" max="15" data-prefix="px" />
                        <app-input type="range" name="border_radius" value="setting{border_radius}" label="ln{border_radius}" min="0" max="16" data-prefix="px" />
                        <app-input type="tags" name="fileupload_mimes" value="setting{fileupload_mimes}" label="ln{unggahan_yang_diizinkan}" min="12" max="15" data-prefix="px" />
                        <app-select class="select2" data-search="false" name="pos_account_notif" value="setting{pos_account_notif}" label="ln{posisi_menu_akun_dan_notifikasi}">
                            <option value="sidebar">ln{menu_kiri}</option>
                            <option value="header">ln{header}</option>
                        </app-select>
                        <app-select class="select2" data-search="false" name="pos_action_button" value="setting{pos_action_button}" label="ln{posisi_tombol_aksi}">
                            <option value="default">ln{setelan_standar}</option>
                            <option value="left">ln{kiri}</option>
                        </app-select>
                        <app-select class="select2" data-search="false" name="pos_add_button" value="setting{pos_add_button}" label="ln{posisi_tombol_tambah}">
                            <option value="header">ln{header}</option>
                            <option value="table">ln{tabel}</option>
                        </app-select>
                        <app-select class="select2" data-search="false" name="table_style[]" label="ln{style_tabel}" multiple>
                            <option value="table-bordered"<?=in_array('table-bordered',explode(',',setting('table_style'))) ? ' selected' : '';?>>Bordered</option>
                            <option value="table-striped"<?=in_array('table-striped',explode(',',setting('table_style'))) ? ' selected' : '';?>>Striped</option>
                            <option value="table-hover"<?=in_array('table-hover',explode(',',setting('table_style'))) ? ' selected' : '';?>>Hover</option>
                        </app-select>
                        <app-select class="select2" data-search="false" name="style_sidebar" value="setting{style_sidebar}" label="Style Sidebar">
                            <option value="app-style1">Style 1</option>
                            <option value="app-style2">Style 2</option>
                            <option value="app-style3">Style 3</option>
                        </app-select>
                        <app-input type="switch" name="write_log_activity" value="1" label="ln{catat_log_aktifitas}" checked="setting{write_log_activity}" />
                    </div>
                    <div class="col-sm-6">
                        <div class="fw-bold mb-3 text-uppercase">ln{perusahaan}</div>

                        <app-input name="company_name" value="setting{company_name}" label="ln{nama_perusahaan}" />
                        <app-input type="textarea" name="company_address" value="setting{company_address}" label="ln{alamat_perusahaan}" />
                        <app-input name="company_email" value="setting{company_email}" label="ln{alamat_surel}" validation="email" />
                        <app-input name="company_phone" value="setting{company_phone}" label="ln{telepon}" validation="phone" />
                        <app-input name="company_fax" value="setting{company_fax}" label="ln{faksimili}" validation="phone" />
                        <app-input type="imageupload" name="company_logo" value="asset_url{uploads/settings/}setting{company_logo}" label="ln{logo_perusahaan}" data-width="768" data-height="256" />
                        
                        <div class="fw-bold mb-3 pt-3 text-uppercase">ln{akun}</div>
                        <app-input type="switch" name="strong_password" value="1" label="ln{kata_sandi_kuat}" checked="setting{strong_password}" />
                        <app-input name="password_min_length" value="setting{password_min_length}" label="ln{minimal_panjang_kata_sandi}" validation="required|min:6|numeric" suffix="ln{karakter}" />
                        <app-input name="expired_password" value="setting{expired_password}" label="ln{masa_aktif_kata_sandi}" validation="numeric" suffix="ln{hari}" />
                        <app-input name="history_password_limit" value="setting{history_password_limit}" label="ln{batas_riwayat_kata_sandi}" validation="numeric" suffix="ln{kali}" />
                        <app-input name="wrong_password_limit" value="setting{wrong_password_limit}" label="ln{batas_kesalahan_kata_sandi_saat_masuk}" validation="numeric" suffix="ln{kali}" />
                        <app-input type="switch" name="single_login" value="1" label="ln{masuk_tunggal}" sub-label="ln{masuk_tunggal_desc}" checked="setting{single_login}" />

                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="email" role="tabpanel" aria-labelledby="email-tab">
                <div class="fw-bold mb-3 text-uppercase">SMTP</div>
                <div class="row">
                    <div class="col-sm-6 mb-2">
                        <app-input type="switch" name="smtp_active" value="1" label="ln{aktif}" checked="setting{smtp_active}" />
                        <app-input name="smtp_server" value="setting{smtp_server}" label="ln{server}" validation="required" />
                        <app-input name="smtp_port" value="setting{smtp_port}" label="ln{port}" validation="numeric" />
                        <app-input name="smtp_email" value="setting{smtp_email}" label="ln{alamat_surel}" validation="required|email" />
                        <app-input type="password" name="smtp_password" value="setting{smtp_password}" label="ln{kata_sandi}" />
                        <app-input name="smtp_email_alias" value="setting{smtp_email_alias}" label="ln{alamat_surel_alias}" validation="email" />
                        <app-input name="smtp_sender_alias" value="setting{smtp_sender_alias}" label="ln{nama_pengirim_alias}" />
                    </div>
                    <div class="col-sm-6 mb-2 text-center">
                        <div class="image-upload border-0">
                            <div class="image-preview">
                                <img src="<?=asset_url('images/email.svg');?>" alt="" class="d-block mb-4 me-auto ms-auto" width="250" />
                            </div>
                            <div class="upload-container">
                                <div class="upload-recomendation text-start"><?=lang('kirim_surel_desc');?></div>
                                <div class="upload-button-container">
                                    <button type="button" class="btn btn-app" id="btn-kirim-email"><i class="fa-envelope me-2"></i><?=lang('kirim_surel');?></button>
                                </div>
                            </div>
                        </div>								
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="row">
                        <div class="offset-lg-3">
                            <button type="submit" class="btn-app btn"><?=lang('simpan_perubahan');?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</app-card>
<app-modal id="modal-kirim-email" title="ln{pengaturan_smtp}" subtitle="ln{kirim_surel}">
    <form id="form-email" action="base_url{settings/application/send-mail}" method="post" data-callback="closeModal" autocomplete="off">
        <app-input-default label-type="floating" />
        <app-input label="ln{alamat_surel}" id="kirim_alamat" name="email" validation="required|email" />
        <app-input label="ln{subjek}" id="kirim_subjek" name="subject" validation="required" />
        <app-input type="textarea" label="ln{pesan}" id="kirim_pesan" name="message" validation="required" />
    </form>
    <footer>
        <button type="button" class="btn btn-theme" data-bs-dismiss="modal">ln{batal}</button>
        <button type="submit" class="btn btn-app" form="form-email">ln{kirim_surel}</button>
    </footer>
</app-modal>
<script>
$(document).ready(function(){
    if(!$('#smtp_active').is(':checked')) {
        $('#smtp_active').closest('.row').parent().find('input[type="text"], input[type="password"]').attr('disabled',true);
        $('#btn-kirim-email').attr('disabled',true);
    }
    backgroundLogin();
});
$('#background_login_active').click(function(){
    backgroundLogin();
});
$('#smtp_active').click(function(){
    if(!$('#smtp_active').is(':checked')) {
        $('#smtp_active').closest('.row').parent().find('input[type="text"], input[type="password"]').attr('disabled',true);
        $('#btn-kirim-email').attr('disabled',true);
    } else {
        $('#smtp_active').closest('.row').parent().find('input[type="text"], input[type="password"]').removeAttr('disabled');
        $('#btn-kirim-email').removeAttr('disabled');
    }
});
$('#btn-kirim-email').click(function(){
    $('#form-email')[0].reset();
    modal('modal-kirim-email').show();
});
function backgroundLogin() {
    if($('#background_login_active').is(':checked')) {
        $('#background_login').closest('.row').removeClass('d-none');
    } else {
        $('#background_login').closest('.row').addClass('d-none');
    }
}
</script>