<div class="row">
    <div class="col-xl-12 mb-4">
        <div class="card">
            <div class="card-header fw-bold">
                <span style="font-size: 13px;">Form Data</span>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <form autocomplete="off" action="<?= base_url('frontend/do-order'); ?>" method="post" data-submit="ajax" data-info="alert" data-callback="redirect:href">
                        <app-input type="hidden" name="id" />
                        <app-input name="nama" validation="required" label="Nama" />
                        <app-select class="select2" validation="required" label="Jenis Kelamin" name="jenis_kelamin">
                            <option value="Pria">Pria</option>
                            <option value="Wanita">Wanita</option>
                        </app-select>
                        <app-input name="telp" validation="required" label="No. Handphone" />
                        <button type="submit" class="btn btn-app">Order Sekarang</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>