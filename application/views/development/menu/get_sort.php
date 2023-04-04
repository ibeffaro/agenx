<ol class="sortable">
    <?php foreach(['top','bottom'] as $b) { ?>
    <li id="menuItem_<?=$b?>" class="position">
        <div class="sort-item">
            <span class="item-title"><?=$b == 'top' ? lang('modul_atas') : lang('modul_bawah');?></span>
        </div>
        <?php if(count($menu[$b][0]) > 0) { ?>
        <ol>
            <?php foreach($menu[$b][0] as $m0) { ?>
            <li id="menuItem_<?=$m0['id'];?>" class="module" data-module="<?=$m0['target'];?>">
                <div class="sort-item">
                    <span class="item-title"><?=$m0['nama'];?></span>
                </div>
                <?php if(isset($menu[$b][$m0['id']]) && count($menu[$b][$m0['id']]) > 0) { ?>
                <ol>
                    <?php foreach($menu[$b][$m0['id']] as $m1) { ?>
                    <li id="menuItem_<?=$m1['id'];?>" data-module="<?=$m0['target'];?>">
                        <div class="sort-item">
                            <span class="item-title"><?=$m1['nama'];?></span>
                        </div>
                        <?php if(isset($menu[$b][$m1['id']]) && count($menu[$b][$m1['id']]) > 0) { ?>
                        <ol>
                            <?php foreach($menu[$b][$m1['id']] as $m2) { ?>
                            <li id="menuItem_<?=$m2['id'];?>" data-module="<?=$m0['target'];?>">
                                <div class="sort-item">
                                    <span class="item-title"><?=$m2['nama'];?></span>
                                </div>
                            </li>
                            <?php } ?>
                        </ol>
                        <?php } ?>
                    </li>
                    <?php } ?>
                </ol>
                <?php } ?>
            </li>
            <?php } ?>
        </ol>
        <?php } ?>
    </li>
    <?php } ?>
</ol>