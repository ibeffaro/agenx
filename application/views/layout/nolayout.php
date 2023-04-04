<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?=setting('description');?>">
<meta name="base-url" content="<?=base_url();?>">
<meta name="string-uri" content="<?=$__cur_uri;?>">
<meta name="m-upl-size" content="<?=file_upload_max_size();?>">
<meta name="m-upl-acc" content="<?=base64_encode(setting('fileupload_mimes') ? str_replace(',','|',setting('fileupload_mimes')): '*');?>">
<meta name="app-number" content="<?=round((strtotime(date('Y-m-d')) / 1000) * 2);?>">
<meta name="app-token" content="<?=isset($__app_token) ? $__app_token : '';?>">
<meta name="client-token" content="<?=isset($__client_token) ? $__client_token : '';?>">
<title><?=$title;?></title>
<link rel="shortcut icon" href="<?=asset_url('uploads/settings/'.setting('favicon'));?>" />
<?php
Asset::css('bootstrap.min.css', true);
Asset::css('roboto.css', true);
Asset::css('fontawesome.regular.css', true);
Asset::css('styles.css', true);
echo Asset::render();
if(setting('font_type') && setting('font_type') != 'roboto') {
    echo '<link rel="stylesheet" href="'.asset_url('fonts/'.setting('font_type').'/font.css').'" />' . "\n";
}
echo $__css;
echo $__custom_css;
?>
</head>
<body class="<?php echo get_cookie('app-shadow-theme') != 'flat' ? 'shadow-theme' : '';?> p-2" data-theme="<?=$__theme;?>">
    <?=$__content; ?>
<?php
Asset::js('jquery.js', true);
Asset::js('jquery.fileupload.js', true);
Asset::js('jquery.inputmask.js', true);
Asset::js('jquery.contextmenu.js', true);
Asset::js('bootstrap.bundle.min.js', true);
Asset::js('hashids.min.js', true);
Asset::js('moment.min.js', true);
Asset::js('select2.full.js', true);
Asset::js('lang_'.setting('language_code').'.js', true);
Asset::js('lightbox.min.js', true);
Asset::js('sweetalert.min.js', true);
Asset::js('bootstrap.tagsinput.js', true);
Asset::js('daterangepicker.js', true);
Asset::js('printThis.js', true);
Asset::js('app.fn.js', true);
Asset::js('appinityTable.js', true);
Asset::js('app.js', true);
Asset::js('main.js', true);
echo Asset::render();
echo $__js;
?>
</body>
</html>