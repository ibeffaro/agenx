<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= setting('description'); ?>">
    <meta name="base-url" content="<?= base_url(); ?>">
    <meta name="string-uri" content="<?= $__cur_uri; ?>">
    <meta name="m-upl-size" content="<?= file_upload_max_size(); ?>">
    <meta name="m-upl-acc" content="<?= base64_encode(setting('fileupload_mimes') ? str_replace(',', '|', setting('fileupload_mimes')) : '*'); ?>">
    <meta name="app-number" content="<?= round((strtotime(date('Y-m-d')) / 1000) * 2); ?>">
    <meta name="app-token" content="<?= isset($__app_token) ? $__app_token : ''; ?>">
    <meta name="client-token" content="<?= isset($__client_token) ? $__client_token : ''; ?>">
    <title><?= $title; ?></title>
    <link rel="shortcut icon" href="<?= asset_url('uploads/settings/' . setting('favicon')); ?>" />
    <?php
    Asset::css('bootstrap.min.css', true);
    Asset::css('roboto.css', true);
    Asset::css('fontawesome.solid.css', true);
    Asset::css('styles.css', true);
    // Asset::css('main.css', true);
    echo Asset::render();
    if (setting('font_type') && setting('font_type') != 'roboto') {
        echo '<link rel="stylesheet" href="' . asset_url('fonts/' . setting('font_type') . '/font.css') . '" />' . "\n";
    }
    echo $__css;
    echo $__custom_css;
    ?>
    <style>
        :root {
            --app-font-size: 1rem;
        }
    </style>
</head>

<body class="<?php echo get_cookie('app-shadow-theme') != 'flat' ? 'shadow-theme' : '';
                echo ' app-' . $__device; ?>" data-theme="<?= $__theme; ?>" data-action-pos="<?= setting('pos_action_button'); ?>">
    <div class="container">
        <div class="p-4">
            <div class="card mb-4">
                <div class="card-body">
                    <nav class="navbar navbar-expand-lg navbar-light">
                        <div class="container-fluid">
                            <!-- <a class="navbar-brand" href="#">
                                <img src="<? #= asset_url('uploads/settings/' . setting('logo')); 
                                            ?>" height="36" alt="" />
                            </a> -->
                            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                    <li class="nav-item">
                                        <a class="nav-link fw-bold" aria-current="page" href="<?= base_url(); ?>">Order Ticket</a>
                                    </li>
                                </ul>
                                <div class="d-flex">
                                    <a href="<?= base_url('auth/login'); ?>" class="btn btn-app">Login</a>
                                </div>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
            <?= $__content; ?>
        </div>
    </div>
    <?php
    Asset::js('jquery.js', true);
    Asset::js('bootstrap.bundle.min.js', true);
    Asset::js('app.fn.js', true);
    echo Asset::render();
    echo $__js;
    ?>
</body>

</html>