<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="base-url" content="<?=base_url();?>">
<title>Setup belum lengkap</title>
<link rel="stylesheet" href="<?=base_url();?>assets/css/bootstrap.min.css" />
<link rel="stylesheet" href="<?=base_url();?>assets/css/roboto.css" />
<link rel="stylesheet" href="<?=base_url();?>assets/css/fontawesome.regular.css" />
<link rel="stylesheet" href="<?=base_url();?>assets/css/styles.css" />
</head>
<body class="shadow-theme">
<div class="container auth-container">
	<div class="row justify-content-center">
		<div class="col col-md-12 col-lg-10 col-xl-8">
			<div id="auth-panel">
				<div class="left-side">
					<img src="<?=asset_url("images/setup.svg"); ?>" class="img-view" />
				</div>
				<div class="right-side">
					<div class="auth-content">
						<div class="auth-container error-content">
							<div class="error-code">
								<div class="error-desc p-0">Setup belum lengkap</div>
							</div>
							<div class="error-message mb-3">Beberapa tabel tidak ditemukan di database:</div>
                            <div class="mt-1">
                                <?php foreach($table as $t) { ?>
                                <div class="error"><i class="text-danger fa-times me-3"></i> <?=$prefix.$t;?></div>
                                <?php } ?>
                            </div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<sript src="<?=base_url();?>assets/js/jquery.js"></script>
<sript src="<?=base_url();?>assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>