<app-card>
    <app-table table="${form_table}" class="__appinity__" data-height="full" data-action-select="defaultControl">
        <thead>
            <tr>
                <?php foreach($form_fields as $k => $t) { if($t['showOnTable']) { ?>
                <th data-content="<?=$k;?>"<?php
                $decimal = '';
                if($t['type'] == 'currency' && $t['decimal']) $decimal = ' data-decimal="'.$t['decimal'].'"';
                if($t['type'] == 'currency') echo ' data-type="currency" class="text-end"'.$decimal;
                else if($t['type'] == 'imageupload') echo ' data-type="image" width="150"';
                else if($t['type'] == 'switch') echo ' data-type="boolean" width="120" class="text-center"';
                else if($t['type'] == 'tags') echo ' data-type="tags"';
                else if(in_array($t['type'], ['date','datetime','current_date'])) echo ' data-type="daterange"';
                else if($t['type'] == 'select') echo ' data-filter="filter-'.$k.'"';
                ?>><?=lang($t['label'],$t['label']);?></th>
                <?php }} ?>
                <th data-content="button" class="text-center" width="90">ln{aksi}</th>
            </tr>
        </thead>
    </app-table>
</app-card>
<?php 
foreach($form_fields as $k => $t) { 
    if($t['type'] == 'select' && $t['showOnTable']) {
        if(isset($form_ref[$t['ref']])) {
            echo '<select data-filter-id="filter-'.$k.'">';
            echo '<option value=""></option>';
            foreach($form_ref[$t['ref']] as $v) {
                $val    = isset($v[$t['refValue']]) ? $v[$t['refValue']] : 'undefined';
                $lbl    = isset($v[$t['refLabel']]) ? $v[$t['refLabel']] : 'undefined';
                echo '<option value="'.$val.'">'.$lbl.'</option>';
            }
            echo '</select>';
        } elseif(isset($t['refData'])) {
            echo '<select data-filter-id="filter-'.$k.'">';
            echo '<option value=""></option>';
            $refData = explode(',',$t['refData']);
            foreach($refData as $rData) {
                echo '<option value="'.$rData.'">'.$rData.'</option>';
            }
            echo '</select>';
        }
    }
}
?>
<div data-action-id="defaultControl">
    <?php if($access['delete']) { ?>
    <button class="btn btn-danger" data-action="deleteSelected"><i class="fa-trash-alt"></i> ln{hapus}</button>
    <?php } ?>
</div>
<app-modal title="${title}">
    <form id="form" app-link="default" autocomplete="off" table="${form_table}">
        <app-input-default size="3:9" rules="${form_table}" />
        <?php if($form_pk) { ?>
        <app-input type="hidden" name="<?=$form_pk;?>" />
        <?php } ?>
        <?php foreach($form_fields as $k => $f) { 
            $decimal = '';
            if($f['type'] == 'currency' && $f['decimal']) $decimal = ' data-decimal="'.$f['decimal'].'"';
            if($f['type'] == 'select') { ?>
            <app-select class="select2" name="<?=$k;?>" label="<?=lang($f['label'],$f['label']);?>">
                <?php
                if(isset($form_ref[$f['ref']])) {
                    echo '<option value=""></option>';
                    foreach($form_ref[$f['ref']] as $v) {
                        $val    = isset($v[$f['refValue']]) ? $v[$f['refValue']] : 'undefined';
                        $lbl    = isset($v[$f['refLabel']]) ? $v[$f['refLabel']] : 'undefined';
                        echo '<option value="'.$val.'">'.$lbl.'</option>';
                    }
                } elseif(isset($f['refData'])) {
                    $refData = explode(',',$f['refData']);
                    foreach($refData as $rData) {
                        echo '<option value="'.$rData.'">'.$rData.'</option>';
                    }
                }
                ?>
            </app-select>
            <?php } elseif($f['type'] == 'current_date') { ?>
            <app-input type="hidden" name="<?=$k;?>" value="__CURRENT_DATE__" />
            <?php } elseif($f['type'] == 'switch') { ?>
            <app-input type="<?=$f['type'];?>" name="<?=$k;?>" label="<?=lang($f['label'],$f['label']);?>" value="1" checked="1" />
            <?php 
                } elseif($f['type'] == 'imageupload') {
                    if(isset($f['imgWidth']) && isset($f['imgHeight'])) { $crop = isset($f['imgCrop']) && $f['imgCrop'] ? ' data-crop' : ''; ?>
                    <app-input type="<?=$f['type'];?>" name="<?=$k;?>" label="<?=lang($f['label'],$f['label']);?>" data-width="<?=$f['imgWidth'];?>" data-height="<?=$f['imgHeight'];?>"<?=$crop;?> />
                <?php } else { ?>
                    <app-input type="<?=$f['type'];?>" name="<?=$k;?>" label="<?=lang($f['label'],$f['label']);?>" />
                <?php }
            } else { ?>
            <app-input type="<?=$f['type'];?>" name="<?=$k;?>" label="<?=lang($f['label'],$f['label']);?>"<?=$decimal;?> />
            <?php } ?>
        <?php } ?>
    </form>
    <footer-form />
</app-modal>