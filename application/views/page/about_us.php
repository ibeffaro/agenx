<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form method="post" id="form" action="<?=base_url('page/about_us/save');?>" data-submit="ajax" data-callback="reload" autocomplete="off">
                    <app-input-default size="3:9" size-param="lg" />
                    <app-input type="hidden" name="id" value="<?= isset($record['id']) && $record['id'] ? $record['id'] : 0; ?>" />
                    <app-input name="judul" value="<?= isset($record['judul']) && $record['judul'] ? $record['judul'] : ''; ?>" label="ln{judul}" validation="required" />
                    <app-input type="textarea" name="deskripsi" value="<?= isset($record['deskripsi']) && $record['deskripsi'] ? $record['deskripsi'] : ''; ?>" label="ln{deskripsi}" validation="" />
                    <app-input name="judul2" value="<?= isset($record['judul2']) && $record['judul2'] ? $record['judul2'] : ''; ?>" label="ln{judul} (2)" validation="required" />
                    <app-input type="textarea" name="deskripsi2" value="<?= isset($record['deskripsi2']) && $record['deskripsi2'] ? $record['deskripsi2'] : ''; ?>" label="ln{deskripsi} (2)" validation="" />
                    <app-input name="judul3" value="<?= isset($record['judul3']) && $record['judul3'] ? $record['judul3'] : ''; ?>" label="ln{judul} (3)" validation="required" />
                    <app-input type="tags" name="daftar_deskripsi3[]" label="ln{daftar_deskripsi} (3)" value="<?= isset($record['daftar_deskripsi3']) && $record['daftar_deskripsi3'] ? $record['daftar_deskripsi3'] : ''; ?>" class="validation" />
                    <app-input name="judul4" value="<?= isset($record['judul4']) && $record['judul4'] ? $record['judul4'] : ''; ?>" label="ln{judul} (4)" validation="required" />
                    <app-input type="tags" name="daftar_deskripsi4[]" label="ln{daftar_deskripsi} (4)" value="<?= isset($record['daftar_deskripsi4']) && $record['daftar_deskripsi4'] ? $record['daftar_deskripsi4'] : ''; ?>" class="validation" />
                    <app-input name="judul5" value="<?= isset($record['judul5']) && $record['judul5'] ? $record['judul5'] : ''; ?>" label="ln{judul} (5)" validation="required" />
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