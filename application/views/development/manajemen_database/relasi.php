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
    <?php if($access['input']) { ?>
    <a href="base_url{development/manajemen-database/form-relasi}" class="btn btn-app d-block mb-3"><i class="fa-plus"></i> ln{tambah_relasi}</a>
    <?php } foreach($relasi as $r) { ?>
    <a href="<?=$r['link'];?>" class="list-item">
        <i class="fa-project-diagram"></i><span><?=$r['title'];?></span>
    </a>
    <?php } ?>
</right-panel>

<div class="row">
    <?php foreach($relasi as $r) { ?>
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-4 col-6 mb-4">
        <a href="<?=$r['link'];?>" class="quick-link">
            <i class="icon fa-project-diagram"></i>
            <span class="title"><?=$r['title'];?></span>
        </a>
    </div>
    <?php } ?>
</div>

<script src="<?=base_url('assets/js/jquery.ui.min.js');?>"></script>
<script src="<?=base_url('assets/js/jquery.flowchart.js');?>"></script>
<script src="<?=base_url('assets/js/app/relasi.js');?>"></script>