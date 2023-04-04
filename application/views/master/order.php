<app-card>
    <app-table source="master/order/data" data-height="full" data-action-select="defaultControl" table="<?= 'order'; ?>" class="__appinity__">
        <thead>
            <tr>
                <th data-content="nomor_id">Nomor ID</th>
                <th data-content="nama" data-sub-content="jenis_kelamin">Nama</th>
                <th data-content="telp">No Handphone</th>
                <th width="120" data-content="is_active" data-type="boolean" class="text-center">ln{aktif}</th>
                <th data-content="button" width="90" class="text-center">ln{aksi}</th>
            </tr>
        </thead>
    </app-table>
</app-card>
<div data-action-id="defaultControl">
    <?php if ($access['delete']) { ?>
        <button class="btn btn-danger" data-action="deleteSelected"><i class="fa-trash-alt"></i> ln{hapus}</button>
    <?php } ?>
</div>

<app-modal title="${title}" size="md">
    <form id="form" app-link="default" autocomplete="off">
        <app-input-default size="3:9" />
        <app-input type="hidden" name="id" />
        <app-input name="nama" label="ln{nama}" validation="required" />
        <app-select class="select2" validation="required" label="Jenis Kelamin" name="jenis_kelamin">
            <option value="Pria">Pria</option>
            <option value="Wanita">Wanita</option>
        </app-select>
        <app-input name="telp" label="No. Handphone" validation="required|numeric" />
    </form>
    <footer-form />
</app-modal>