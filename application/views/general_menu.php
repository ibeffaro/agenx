<div class="row">
    <?php foreach($list_menu as $lm) { ?>
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-4 col-6 mb-4">
        <a href="<?=base_url($lm['target']);?>" class="quick-link">
            <i class="icon <?=$lm['icon'];?>"></i>
            <span class="title"><?=lang('_'.strtolower(str_replace([' ','/'],'_',$lm['target'])),$lm['nama']);?></span>
        </a>
    </div>
    <?php } ?>
</div>