<action-header>
    <a href="" class="btn btn-app text-nowrap"><i class="fa-code"></i><span class="ms-2">ln{perintah_sql}</span></a>
</action-header>
<right-panel>
    <panel-header>
        <ul class="nav nav-tabs">
            <li class="nav-item w-50">
                <a class="nav-link text-center" href="base_url{development/manajemen-database}">ln{tabel}</a>
            </li>
            <li class="nav-item w-50">
                <a class="nav-link text-center active" href="base_url{development/manajemen-database/relasi}">ln{relasi}</a>
            </li>
        </ul>
    </panel-header>
    <?php foreach($table as $k => $v) { ?>
    <a href="javascript:;" class="list-item list-table" data-table="<?=$k;?>" data-pk="<?=$v['pk'];?>" data-fk="<?=$v['fk'];?>" data-def="<?=$v['def'];?>">
        <i class="fa-table"></i><span><?=$k;?></span>
    </a>
    <?php } ?>
</right-panel>

<div class="flowchart-action-container">
    <button class="btn" type="button" data-label-tabel="ln{hapus_tabel}" data-label-link="ln{hapus_link}">ln{hapus}</button>
</div>
<div class="d-flex flex-column h-100">
    <div class="p-2 setup-panel setup-top">
        <a href="base_url{development/manajemen-database/relasi}" class="btn me-3"><i class="fa-chevron-left"></i></a>
        <button type="button" class="btn btn-app me-1" id="save-relasi"><i class="fa-save me-2"></i>ln{simpan_relasi}</button>
        <?php if(isset($default) && $default && $access['delete']) { ?>
        <button type="button" class="btn btn-danger" id="delete-relasi"><i class="fa-trash-alt me-2"></i>ln{hapus_relasi}</button>
        <?php } ?>
    </div>
    <div class="h-100" data-key="${key}" id="flowchart-container">
        <div id="flowchartworkspace" class="h-100"<?php if(setting('custom_color_primary')) echo ' data-link-color="'.setting('color_primary').'"';?>></div>
    </div>
</div>
<script src="<?=base_url('assets/js/jquery.ui.min.js');?>"></script>
<script src="<?=base_url('assets/js/jquery.flowchart.js');?>"></script>
<script src="<?=base_url('assets/js/app/relasi.js');?>"></script>
<?php if(isset($default) && $default)  { ?>
<script data-inline>
    var defaultJSON = '<?=json_encode(json_decode($default));?>';
</script>
<?php } ?>