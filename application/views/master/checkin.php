<app-card>
    <app-table source="master/checkin/data" data-height="full" data-action-select="defaultControl" table="<?= 'order'; ?>" class="__appinity__">
        <thead>
            <tr>
                <th data-content="nomor_id">Nomor ID</th>
                <th data-content="nama" data-sub-content="jenis_kelamin">Nama</th>
                <th data-content="telp">No Handphone</th>
                <th width="120" data-content="is_active" data-type="boolean" data-filter="false" data-sort="false" class="text-center">Status</th>
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
        <app-input name="nomor_id" label="Nomor ID" validation="required" />
    </form>
    <footer-form />
</app-modal>