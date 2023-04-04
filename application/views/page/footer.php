<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form method="post" id="form" action="<?=base_url('page/footer/save');?>" data-submit="ajax" data-callback="reload" autocomplete="off">
                    <app-input-default size="3:9" size-param="lg" />
                    <app-input type="hidden" name="id" value="<?= isset($record['id']) && $record['id'] ? $record['id'] : 0; ?>" />
                    <app-input name="judul" value="<?= isset($record['judul']) && $record['judul'] ? $record['judul'] : ''; ?>" label="ln{judul}" validation="required" />
                    <app-input type="textarea" name="alamat" value="<?= isset($record['alamat']) && $record['alamat'] ? $record['alamat'] : ''; ?>" label="ln{alamat}" validation="" />
                    <app-input name="link_gmaps" value="<?= isset($record['link_gmaps']) && $record['link_gmaps'] ? $record['link_gmaps'] : ''; ?>" label="ln{link}" validation="required" />
                    <app-input type="textarea" name="deskripsi" value="<?= isset($record['deskripsi']) && $record['deskripsi'] ? $record['deskripsi'] : ''; ?>" label="ln{deskripsi}" validation="" />
                    <app-input name="copyright" value="<?= isset($record['copyright']) && $record['copyright'] ? $record['copyright'] : ''; ?>" label="ln{copyright}" validation="required" />
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