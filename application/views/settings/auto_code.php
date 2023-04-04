<app-card>
    <app-table source="settings/auto-code/data" data-height="full" data-action-select="defaultControl" table="kode" class="__appinity__">
        <thead>
            <tr>
                <th max-width="250" data-content="tabel">ln{tabel}</th>
                <th max-width="200" data-content="kolom">ln{kolom}</th>
                <th data-content="awalan">ln{awalan}</th>
                <th width="120" data-content="panjang">ln{panjang}</th>
                <th data-content="akhiran">ln{akhiran}</th>
                <th width="120" data-content="is_active" data-type="boolean" class="text-center">ln{aktif}</th>
                <th data-content="button" width="90" class="text-center">ln{aksi}</th>
            </tr>
        </thead>
    </app-table>
</app-card>
<div data-action-id="defaultControl">
    <?php if($access['delete']) { ?>
    <button class="btn btn-danger" data-action="deleteSelected"><i class="fa-trash-alt"></i> ln{hapus}</button>
    <?php } ?>
</div>
<app-modal title="${title}" id="modal-form">
    <form id="form" app-link="default" autocomplete="off">
        <header>
            <a href="javascript:;" class="f-150 me-4" data-bs-target="#modal-help" data-bs-toggle="modal" data-bs-dismiss="modal" aria-label="ln{bantuan}" data-appinity-tooltip="left"><i class="fa-question-circle"></i></a>
        </header>
        <app-input type="hidden" name="id" />
        <app-input-default size="3:9" rules="kode" />
        <app-select class="select2" name="tabel" label="ln{tabel}">
            <option value=""></option>
            each(table => t)
            <option value="{t}">{t}</option>
            endEach
        </app-select>
        <app-select class="select2" name="kolom" label="ln{kolom}"></app-select>
        <app-input type="text" name="awalan" label="ln{awalan}" />
        <app-input type="number" name="panjang" label="ln{panjang}" />
        <app-input type="text" name="akhiran" label="ln{akhiran}" />
        <app-input type="switch" name="is_active" label="ln{aktif}" value="1" checked="1" />
    </form>
    <footer-form />
</app-modal>
<div class="modal fade" id="modal-help" aria-hidden="true" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ln{bantuan}</h5>
                <button type="button" class="btn-close btn-back" data-bs-target="#modal-form" data-bs-toggle="modal" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="fw-bold text-uppercase mb-1">ln{definisi_variabel}</div>
                <p>ln{def_variabel_desc}</p>
                <table class="table">
                    <tr>
                        <td width="125"><span class="fw-bold">{d}</span> ln{atau} <span class="fw-bold">{D}</span></td>
                        <td>ln{hari_ini}<small class="d-block">ln{contoh} : 01 / 02 / 03 ... 31</small></td>
                    </tr>
                    <tr>
                        <td><span class="fw-bold">{m}</span></td>
                        <td>ln{bulan_ini} (ln{dalam_angka})<small class="d-block">ln{contoh} : 01 / 02 / 03 ... 12</small></td>
                    </tr>
                    <tr>
                        <td><span class="fw-bold">{r}</span></td>
                        <td>ln{bulan_ini} (ln{dalam_huruf_romawi})<small class="d-block">ln{contoh} : I / III / III ... XII</small></td>
                    </tr>
                    <tr>
                        <td><span class="fw-bold">{M}</span></td>
                        <td>ln{bulan_ini} (ln{singkatan_bahasa_inggris})<small class="d-block">ln{contoh} : JAN / FEB / MAR ... DEC</small></td>
                    </tr>
                    <tr>
                        <td><span class="fw-bold">{MONTH}</span></td>
                        <td>ln{bulan_ini} (ln{bahasa_inggris})<small class="d-block">ln{contoh} : JANUARY / FEBRUARI / MARCH ... DECEMBER</small></td>
                    </tr>
                    <tr>
                        <td><span class="fw-bold">{BLN}</span></td>
                        <td>ln{bulan_ini} (ln{singkatan_bahasa_indonesia})<small class="d-block">ln{contoh} : JAN / FEB / MAR ... DES</small></td>
                    </tr>
                    <tr>
                        <td><span class="fw-bold">{BULAN}</span></td>
                        <td>ln{bulan_ini} (ln{bahasa_indonesia})<small class="d-block">ln{contoh} : JANUARI / FEBRUARI / MARET ... DESEMBER</small></td>
                    </tr>
                    <tr>
                        <td><span class="fw-bold">{y}</span></td>
                        <td>ln{tahun_ini} (ln{dua_digit})<small class="d-block">ln{contoh} : 20 / 21 / 22 ... 99</small></td>
                    </tr>
                    <tr>
                        <td><span class="fw-bold">{Y}</span></td>
                        <td>ln{tahun_ini} (ln{empat_digit})<small class="d-block">ln{contoh} : 2020 / 2021 / 2022 ... 2099</small></td>
                    </tr>
                    <tr>
                        <td><span class="fw-bold">{<em>nama_kolom</em>}</span></td>
                        <td>ln{isi_dari_kolom_tabel_yang_berkaitan}<small class="d-block">ln{contoh} : {nama} = Bayu Ramadhan</small></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    function defaultBeforeLoad(appLink) {
        $('#kolom').html('').trigger('change');
    }
    $(document).on('change','#tabel',function(){
        if(!xhrCheck('getData')) {
            if($(this).val() != '') {
                $('#kolom').html('<option value="">'+lang.mohon_tunggu+'...</option>').trigger('change');
                $.get(baseURL('settings/auto-code/get-field?t=' + encodeURI($(this).val())),function(r){
                    $('#kolom').html(r).trigger('change');
                });
            } else $('#kolom').html('').trigger('change');
        }
    });
</script>