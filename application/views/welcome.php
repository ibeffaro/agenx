<div class="row mb-4">
	<div class="col-lg-8">
		<div class="quick-link bg-app mb-0 h-100">
			<div class="welcome-panel">
				<div class="welcome-img">
					<img src="user{foto2}" alt="user{nama}" />
				</div>
				<div class="welcome-desc">
					<div class="f-120 fw-bold text-uppercase mt-2">user{nama}</div>
					<div class="f-100 mb-2">user{email}</div>
					<div class="btn-group mt-2">
						<a href="base_url{account/profile}" class="btn btn-light"><i class="fa-user me-2"></i>ln{profil}</a>
						<a href="base_url{account/change-password}" class="btn btn-light"><i class="fa-key me-2"></i>ln{kata_sandi}</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-4 d-none d-lg-block">
		<div class="card h-100">
			<div class="card-body text-center">
				<i class="fa-globe icon-circle"></i>
				<div class="text-center f-100 mt-2">ln{alamat_ip}</div>
				<div class="text-center f-130 fw-bold"><?=$ip;?></div>
			</div>
		</div>
	</div>
</div>
<div class="card mb-4 d-none d-lg-block">
	<div class="card-body">
		<div class="f-140 fw-bold mb-4">ln{informasi_akun}</div>
		<div class="row">
			<div class="col-lg-4">
				<div class="mb-3 pb-2 pt-2">
					<div class="text-color-400">ln{agen_pengguna}</div>
					<div class="f-130 fw-bold"><?=$agent;?></div>
				</div>
				<div class="mb-3 pb-2 pt-2">
					<div class="text-color-400">ln{masuk_terakhir}</div>
					<div class="f-130 fw-bold"><?=date_lang(user('last_login'), true, true);?></div>
				</div>
				<div class="mb-3 pb-2 pt-2">
				<div class="text-color-400">ln{aktifitas_terakhir}</div>
					<div class="f-130 fw-bold"><?=date_lang(user('last_activity'), true, true);?></div>
				</div>
				<div class="mb-3 pb-2 pt-2">
					<div class="text-color-400">ln{perubahan_kata_sandi_terakhir}</div>
					<div class="f-130 fw-bold"><?=date_lang(user('change_password_at'), true, true);?></div>
				</div>
			</div>
			<div class="col-lg-8">
				<div class="p-4">
					<img src="asset_url{images/info.svg}" alt="" width="300" class="d-block ms-auto me-auto" />
				</div>
			</div>
		</div>
	</div>
</div>
<?php foreach($__menu[0] as $m) { if(isset($__menu[$m['id']]) && count($__menu[$m['id']])) { ?>
<div class="f-110 fw-bold text-uppercase mb-3"><?=$m['nama'];?></div>
<div class="row mb-4">
	<?php foreach($__menu[$m['id']] as $lm) { 
		if(isset($__menu[$lm['id']]) && count($__menu[$lm['id']])) { 
			foreach($__menu[$lm['id']] as $lm2) { ?>
	<div class="col-xl-2 col-lg-3 col-md-4 col-sm-4 col-6 mb-4">
		<a href="<?=base_url($lm2['target']);?>" class="quick-link">
			<i class="icon <?=$lm2['icon'];?>"></i>
			<span class="title"><?=lang('_'.strtolower(str_replace([' ','/'],'_',$lm2['target'])),$lm2['nama']);?></span>
		</a>
	</div>
	<?php }} else { ?>
	<div class="col-xl-2 col-lg-3 col-md-4 col-sm-4 col-6 mb-4">
		<a href="<?=base_url($lm['target']);?>" class="quick-link">
			<i class="icon <?=$lm['icon'];?>"></i>
			<span class="title"><?=lang('_'.strtolower(str_replace([' ','/'],'_',$lm['target'])),$lm['nama']);?></span>
		</a>
	</div>
	<?php }} ?>
</div>
<?php }} ?>
<right-panel title="Informasi">
	<?php if(count($informasi) == 0) { ?>
	<div class="card">
		<div class="card-header">ln{tidak_ada_informasi}</div>
		<div class="card-body">
			<img src="<?=asset_url('images/search.svg');?>" alt="" width="80%" class="me-auto ms-auto d-block">
		</div>
	</div>
	<?php } else { foreach($informasi as $i) { ?>
	<div class="card mb-4">
		<div class="card-body">
		<figure class="m-0">
			<blockquote class="blockquote f-100 mb-3">
				<p><?=linkify($i->informasi);?></p>
			</blockquote>
			<figcaption class="blockquote-footer m-0">
				<?=$i->updated_by;?>, <cite title="<?=date_lang($i->updated_at);?>"><?=date_lang($i->updated_at);?></cite>
			</figcaption>
		</figure>
		</div>
	</div>
	<?php }} ?>
</right-panel>