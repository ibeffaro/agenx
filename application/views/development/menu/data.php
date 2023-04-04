<?php foreach($menu[0] as $m0) { ?>
    <tr data-menu-id="<?=$m0['id'];?>" data-menu-name="<?=$m0['nama'];?>" data-menu-parent="<?=$m0['parent_id'];?>">
        <td>
            <?=$m0['nama'];?>
            <?php if($m0['deskripsi']) { ?>
            <div class="appinityTable-sub-content"><?=$m0['deskripsi']; ?></div>
            <?php } ?>
        </td>
        <td><em>base_url{}</em><strong><?=$m0['target'];?></strong></td>
        <td class="text-center"><?=$m0['urutan'];?></td>
        <td class="text-center"><?=$m0['is_active'] ? '<span class="badge badge-icon rounded-pill bg-success" data-appinity-tooltip="top" aria-label="'.lang('ya').'"><i class="fa-check"></i></span>' : '<span class="badge badge-icon rounded-pill bg-danger" data-appinity-tooltip="top" aria-label="'.lang('tidak').'"><i class="fa-times"></i></span>';?></td>
        <td class="text-center">
            <div class="appinityTable-action">
                <?php if($access['edit']) { ?>
                    <button class="btn btn-warning btn-input" data-key="id"  data-val="<?=$m0['id'];?>" data-ref="" data-appinity-tooltip="top" data-context-key="edit" aria-label="ln{edit}"><i class="appinityTable-action-icon fa-edit"></i></button>
                <?php } if($access['delete']) { ?>
                    <button class="btn btn-danger btn-delete" data-key="id"  data-val="<?=$m0['id'];?>" data-ref="" data-appinity-tooltip="top" data-context-key="delete" aria-label="ln{hapus}"><i class="appinityTable-action-icon fa-trash-alt"></i></button>
                <?php } ?> 
            </div>
        </td>
    </tr>
    <?php foreach($menu[$m0['id']] as $m1) { ?>
        <tr data-menu-id="<?=$m1['id'];?>" data-menu-name="<?=$m1['nama'];?>" data-menu-parent="<?=$m1['parent_id'];?>">
            <td>
                <div class="ps-4">
                    <?=$m1['nama'];?>
                    <?php if($m1['deskripsi']) { ?>
                    <div class="appinityTable-sub-content"><?=$m1['deskripsi']; ?></div>
                    <?php } ?>
                </div>
            </td>
            <td><em>base_url{}</em><strong><?=$m1['target'];?></strong></td>
            <td class="text-center"><?=$m1['urutan'];?></td>
            <td class="text-center"><?=$m1['is_active'] ? '<span class="badge badge-icon rounded-pill bg-success" data-appinity-tooltip="top" aria-label="'.lang('ya').'"><i class="fa-check"></i></span>' : '<span class="badge badge-icon rounded-pill bg-danger" data-appinity-tooltip="top" aria-label="'.lang('tidak').'"><i class="fa-times"></i></span>';?></td>
            <td class="text-center">
                <div class="appinityTable-action">
                    <?php if($access['edit']) { ?>
                        <button class="btn btn-warning btn-input" data-key="id"  data-val="<?=$m1['id'];?>" data-ref="" data-appinity-tooltip="top" data-context-key="edit" aria-label="ln{edit}"><i class="appinityTable-action-icon fa-edit"></i></button>
                    <?php } if($access['delete']) { ?>
                        <button class="btn btn-danger btn-delete" data-key="id"  data-val="<?=$m1['id'];?>" data-ref="" data-appinity-tooltip="top" data-context-key="delete" aria-label="ln{hapus}"><i class="appinityTable-action-icon fa-trash-alt"></i></button>
                    <?php } ?> 
                </div>
            </td>
        </tr>
        <?php foreach($menu[$m1['id']] as $m2) { ?>
            <tr>
                <td>
                    <div class="ps-5">
                        <?=$m2['nama'];?>
                        <?php if($m2['deskripsi']) { ?>
                        <div class="appinityTable-sub-content"><?=$m2['deskripsi']; ?></div>
                        <?php } ?>
                    </div>
                </td>
                <td><em>base_url{}</em><strong><?=$m2['target'];?></strong></td>
                <td class="text-center"><?=$m2['urutan'];?></td>
                <td class="text-center"><?=$m2['is_active'] ? '<span class="badge badge-icon rounded-pill bg-success" data-appinity-tooltip="top" aria-label="'.lang('ya').'"><i class="fa-check"></i></span>' : '<span class="badge badge-icon rounded-pill bg-danger" data-appinity-tooltip="top" aria-label="'.lang('tidak').'"><i class="fa-times"></i></span>';?></td>
                <td class="text-center">
                    <div class="appinityTable-action">
                        <?php if($access['edit']) { ?>
                            <button class="btn btn-warning btn-input" data-key="id"  data-val="<?=$m2['id'];?>" data-ref="" data-appinity-tooltip="top" data-context-key="edit" aria-label="ln{edit}"><i class="appinityTable-action-icon fa-edit"></i></button>
                        <?php } if($access['delete']) { ?>
                            <button class="btn btn-danger btn-delete" data-key="id"  data-val="<?=$m2['id'];?>" data-ref="" data-appinity-tooltip="top" data-context-key="delete" aria-label="ln{hapus}"><i class="appinityTable-action-icon fa-trash-alt"></i></button>
                        <?php } ?> 
                    </div>
                </td>
            </tr>
        <?php } ?>
    <?php } ?>
<?php } ?>