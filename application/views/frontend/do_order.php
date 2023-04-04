<div class="row">
    <div class="col-xl-12 mb-4">
        <div class="card">
            <div class="card-header fw-bold">
                <span style="font-size: 13px;">BERHASIL DIDAFTARKAN</span>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <table class="table table-stripped">
                        <tr>
                            <td>Nomor ID</td>
                            <td>:</td>
                            <th><?= $result['nomor_id']; ?></th>
                        </tr>
                        <tr>
                            <td>Nama</td>
                            <td>:</td>
                            <th><?= $result['nama']; ?></th>
                        </tr>
                        <tr>
                            <td>Jenis Kelamin</td>
                            <td>:</td>
                            <th><?= $result['jenis_kelamin']; ?></th>
                        </tr>
                        <tr>
                            <td>No. Handphone</td>
                            <td>:</td>
                            <th><?= $result['telp']; ?></th>
                        </tr>
                    </table>
                </div>
            </div>
            <button class="btn btn-app" onclick="history.back();">Kembali</button>
        </div>
    </div>
</div>