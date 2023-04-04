<app-card>
    <app-table source="settings/information/data" data-height="full" data-action-select="defaultControl" table="informasi" class="__appinity__">
        <thead>
            <tr>
                <th max-width="300" data-content="informasi">ln{informasi}</th>
                <th max-width="200" data-content="tanggal_mulai" data-type="daterange">ln{tanggal_mulai}</th>
                <th max-width="200" data-content="tanggal_selesai" data-type="daterange">ln{tanggal_selesai}</th>
                <th data-content="id_group" data-type="tags" data-filter="group">ln{penerima}</th>
                <th data-content="button" width="90" class="text-center">ln{aksi}</th>
            </tr>
        </thead>
    </app-table>
</app-card>
<div data-action-id="defaultControl">
    <?php if($access['delete']) { ?>
    <button class="btn btn-danger" data-action="deleteSelected"><i class="fa-trash-alt"></i> ln{hapus}</button>
    <?php } ?>
</div>
<select data-filter-id="group">
    each(user_group => t)
    <option value="{t.id}">{t.nama}</option>
    endEach
</select>
<app-modal title="${title}" id="modal-form">
    <form id="form" app-link="default" autocomplete="off">
        <app-input type="hidden" name="id" />
        <app-input-default size="3:9" rules="informasi" />
        <app-input type="textarea" name="informasi" label="ln{informasi}" />
        <app-input type="daterange" name="tanggal" label="ln{tanggal_publikasi}" validation="required" />
        <app-select class="select2" name="id_group[]" label="ln{penerima}" multiple>
            each(user_group => t)
            <option value="{t.id}">{t.nama}</option>
            endEach
        </app-select>
    </form>
    <footer-form />
</app-modal>