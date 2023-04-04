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
    Asset::css('fontawesome.regular.css', true);
    Asset::css('styles.css', true);
    echo Asset::render();
    if (setting('font_type') && setting('font_type') != 'roboto') {
        echo '<link rel="stylesheet" href="' . asset_url('fonts/' . setting('font_type') . '/font.css') . '" />' . "\n";
    }
    echo $__css;
    echo $__custom_css;
    ?>
</head>

<body class="<?php echo setting('style_sidebar') . ' ';
                echo get_cookie('app-shadow-theme') != 'flat' ? 'shadow-theme' : '';
                echo get_cookie('app-min-menu') ? ' min-menu' : '';
                if ($__right_panel) echo ' right-panel-show';
                echo ' app-' . $__device; ?>" data-theme="<?= $__theme; ?>" data-action-pos="<?= setting('pos_action_button'); ?>">
    <div id="left-panel">
        <div class="sidebar<?= $type_module; ?>">
            <a href="<?= base_url('welcome'); ?>" class="app-logo">
                <?php $ext = explode('.', setting('favicon'));
                if (end($ext) == 'svg') {
                    echo file_to_svg(asset_url('uploads/settings/' . setting('favicon')), 'img-min');
                } else { ?>
                    <img src="<?= asset_url('uploads/settings/' . setting('favicon')); ?>" class="img-min" alt="" />
                <?php } ?>
                <?php $ext = explode('.', setting('logo'));
                if (end($ext) == 'svg') {
                    echo file_to_svg(asset_url('uploads/settings/' . setting('logo')), 'img-max');
                } else { ?>
                    <img src="<?= asset_url('uploads/settings/' . setting('logo')); ?>" class="img-max" alt="" />
                <?php } ?>
            </a>
            <div class="panel-header">
                <div class="title" id="menu-title">&nbsp;</div>
            </div>
            <?php if (setting('pos_account_notif') != 'header') { ?>
                <div class="panel-header profile-panel">
                    <div class="profile-container">
                        <a href="<?= base_url('notification'); ?>" class="notification">
                            <i class="fa-bell"></i>
                            <?php if ($__notif_count) { ?>
                                <div class="app-badge badge-top badge-danger badge-pulse"></div>
                            <?php } ?>
                        </a>
                        <div class="dropdown">
                            <a href="#" class="profile dropdown-toggle" id="dropdownProfil" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?= user('foto'); ?>" alt="<?= user('nama'); ?>" class="rounded-circle" />
                                <div class="app-badge badge-bottom badge-success"></div>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="dropdownProfil">
                                <li>
                                    <a class="dropdown-item dropdown-item-icon" href="<?= base_url('account/profile'); ?>">
                                        <i class="fa-user"></i><?= lang('profil'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-icon" href="<?= base_url('account/change-password'); ?>">
                                        <i class="fa-key"></i><?= lang('kata_sandi'); ?>
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider" size="0">
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-icon" href="<?= base_url('auth/logout'); ?>">
                                        <i class="fa-sign-out"></i><?= lang('keluar'); ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="list-menu">
                <ul class="top-menu">
                    <?php foreach ($__menu[0] as $_menu) {
                        if ($_menu['urutan'] <= 100) { ?>
                            <li class="<?php if (isset($__menu[$_menu['id']]) && count($__menu[$_menu['id']])) {
                                            echo 'have-child';
                                            if ($__active['l1'] == $_menu['id']) echo ' open';
                                        }
                                        if ($__active['l1'] == $_menu['id']) echo ' active'; ?>">
                                <a href="<?= base_url($_menu['target']); ?>">
                                    <i class="<?= $_menu['icon']; ?>"></i>
                                    <span><?= lang('_' . strtolower(str_replace([' ', '/'], '_', $_menu['target'])), $_menu['nama']); ?></span>
                                    <?php if (isset($__badge) && isset($__badge[$_menu['target']]) && $__badge[$_menu['target']]) { ?>
                                        <span class="badge badge-danger"><?= $__badge[$_menu['target']]; ?></span>
                                    <?php } ?>
                                </a>
                                <?php if (isset($__menu[$_menu['id']]) && count($__menu[$_menu['id']])) { ?>
                                    <ul>
                                        <?php foreach ($__menu[$_menu['id']] as $_menu1) { ?>
                                            <li class="<?php if (isset($__menu[$_menu1['id']]) && count($__menu[$_menu1['id']])) {
                                                            echo 'have-child';
                                                            if ($__active['l2'] == $_menu1['id']) echo ' open';
                                                        }
                                                        if ($__active['l2'] == $_menu1['id']) echo ' active'; ?>">
                                                <a href="<?= base_url($_menu1['target']); ?>">
                                                    <i class="<?= $_menu1['icon']; ?>"></i>
                                                    <span><?= lang('_' . strtolower(str_replace([' ', '/'], '_', $_menu1['target'])), $_menu1['nama']); ?></span>
                                                    <?php if (isset($__badge) && isset($__badge[$_menu1['target']]) && $__badge[$_menu1['target']]) { ?>
                                                        <span class="badge badge-danger"><?= $__badge[$_menu1['target']]; ?></span>
                                                    <?php } ?>
                                                </a>
                                                <?php if (isset($__menu[$_menu1['id']]) && count($__menu[$_menu1['id']])) { ?>
                                                    <ul>
                                                        <?php foreach ($__menu[$_menu1['id']] as $_menu2) { ?>
                                                            <li class="<?php if ($__active['l3'] == $_menu2['id']) echo 'active'; ?>">
                                                                <a href="<?= base_url($_menu2['target']); ?>">
                                                                    <i class="<?= $_menu2['icon']; ?>"></i>
                                                                    <span><?= lang('_' . strtolower(str_replace([' ', '/'], '_', $_menu2['target'])), $_menu2['nama']); ?></span>
                                                                    <?php if (isset($__badge) && isset($__badge[$_menu2['target']]) && $__badge[$_menu2['target']]) { ?>
                                                                        <span class="badge badge-danger"><?= $__badge[$_menu2['target']]; ?></span>
                                                                    <?php } ?>
                                                                </a>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                <?php } ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                <?php } ?>
                            </li>
                    <?php }
                    } ?>
                </ul>
                <ul class="bottom-menu">
                    <?php foreach ($__menu[0] as $_menu) {
                        if ($_menu['urutan'] > 100) { ?>
                            <li class="<?php if (isset($__menu[$_menu['id']]) && count($__menu[$_menu['id']])) {
                                            echo 'have-child';
                                            if ($__active['l1'] == $_menu['id']) echo ' open';
                                        }
                                        if ($__active['l1'] == $_menu['id']) echo ' active'; ?>">
                                <a href="<?= base_url($_menu['target']); ?>">
                                    <i class="<?= $_menu['icon']; ?>"></i>
                                    <span><?= lang('_' . strtolower(str_replace([' ', '/'], '_', $_menu['target'])), $_menu['nama']); ?></span>
                                    <?php if (isset($__badge) && isset($__badge[$_menu['target']]) && $__badge[$_menu['target']]) { ?>
                                        <span class="badge badge-danger"><?= $__badge[$_menu['target']]; ?></span>
                                    <?php } ?>
                                </a>
                                <?php if (isset($__menu[$_menu['id']]) && count($__menu[$_menu['id']])) { ?>
                                    <ul>
                                        <?php foreach ($__menu[$_menu['id']] as $_menu1) { ?>
                                            <li class="<?php if (isset($__menu[$_menu1['id']]) && count($__menu[$_menu1['id']])) {
                                                            echo 'have-child';
                                                            if ($__active['l2'] == $_menu1['id']) echo ' open';
                                                        }
                                                        if ($__active['l2'] == $_menu1['id']) echo ' active'; ?>">
                                                <a href="<?= base_url($_menu1['target']); ?>">
                                                    <i class="<?= $_menu1['icon']; ?>"></i>
                                                    <span><?= lang('_' . strtolower(str_replace([' ', '/'], '_', $_menu1['target'])), $_menu1['nama']); ?></span>
                                                    <?php if (isset($__badge) && isset($__badge[$_menu1['target']]) && $__badge[$_menu1['target']]) { ?>
                                                        <span class="badge badge-danger"><?= $__badge[$_menu1['target']]; ?></span>
                                                    <?php } ?>
                                                </a>
                                                <?php if (isset($__menu[$_menu1['id']]) && count($__menu[$_menu1['id']])) { ?>
                                                    <ul>
                                                        <?php foreach ($__menu[$_menu1['id']] as $_menu2) { ?>
                                                            <li class="<?php if ($__active['l3'] == $_menu2['id']) echo 'active'; ?>">
                                                                <a href="<?= base_url($_menu2['target']); ?>">
                                                                    <i class="<?= $_menu2['icon']; ?>"></i>
                                                                    <span><?= lang('_' . strtolower(str_replace([' ', '/'], '_', $_menu2['target'])), $_menu2['nama']); ?></span>
                                                                    <?php if (isset($__badge) && isset($__badge[$_menu2['target']]) && $__badge[$_menu2['target']]) { ?>
                                                                        <span class="badge badge-danger"><?= $__badge[$_menu2['target']]; ?></span>
                                                                    <?php } ?>
                                                                </a>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                <?php } ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                <?php } ?>
                            </li>
                        <?php }
                    }
                    if (setting('pos_account_notif') != 'header') { ?>
                        <li class="list-notification">
                            <a href="#">
                                <i class="fa-bell"></i>
                                <?php if ($__notif_count) { ?>
                                    <div class="app-badge badge-top badge-danger badge-pulse"></div>
                                <?php } ?>
                                <span><?= lang('pemberitahuan'); ?></span>
                            </a>
                            <ul></ul>
                        </li>
                        <li class="list-profile have-child<?php if (substr($uri_string, 0, 7) == 'account') echo ' open active'; ?>">
                            <a href="#">
                                <img src="<?= user('foto'); ?>" alt="<?= user('nama'); ?>" class="rounded-circle" />
                                <div class="app-badge badge-bottom badge-success"></div>
                                <span><?= user('nama'); ?></span>
                            </a>
                            <ul>
                                <li class="<?php if ($uri_string == 'account/profile') echo 'active'; ?>">
                                    <a href="<?= base_url('account/profile'); ?>">
                                        <i class="fa-user"></i>
                                        <span><?= lang('profil'); ?></span>
                                    </a>
                                </li>
                                <li class="<?php if ($uri_string == 'account/change-password') echo 'active'; ?>">
                                    <a href="<?= base_url('account/change-password'); ?>">
                                        <i class="fa-key"></i>
                                        <span><?= lang('kata_sandi'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <hr class="menu-divider" size="0">
                                </li>
                                <li>
                                    <a href="<?= base_url('auth/logout'); ?>">
                                        <i class="fa-sign-out"></i>
                                        <span><?= lang('keluar'); ?></span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
    <div id="main-panel">
        <div class="panel-header<?php if (setting('header_color_primary')) echo ' primary-header'; ?>">
            <a href="javascript:;" class="toggle-menu ms-2"><i class="fa-bars"></i></a>
            <div class="title">
                <?= $title; ?>
                <?php if (isset($sub_title) && $sub_title) { ?>
                    <div class="sub-title"><?= $sub_title; ?></div>
                <?php } ?>
            </div>
            <div class="search-input">
                <input type="text" class="form-control" id="app-search-input" placeholder="<?= lang('cari_menu'); ?>" />
                <i class="search-icon fa-search"></i>
            </div>
            <?php if (isset($action_header) && $action_header && setting('pos_add_button') != "table" && empty($hide_action_header)) { ?>
                <div class="action-header">
                    <?php echo $action_header; ?>
                </div>
            <?php } ?>
            <?php if ($__right_panel) { ?>
                <a href="javascript:;" class="toggle-right-panel" title="<?= $__right_panel_title; ?>">
                    <i class="<?= $__right_panel_toggle_icon; ?>"></i>
                </a>
            <?php } ?>
            <?php if (setting('pos_account_notif') == 'header') { ?>
                <div class="dropdown setting-menu setting-notification">
                    <a href="#" class="notification dropdown-toggle" id="dropdownNotif" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-bell"></i>
                        <?php if ($__notif_count) { ?>
                            <div class="app-badge badge-top badge-danger badge-pulse"></div>
                        <?php } ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="dropdownNotif">
                        <ul class="notification-lists">
                            <?php if (count($__notif_lists)) {
                                foreach ($__notif_lists as $nl) { ?>
                                    <li class="notification-item">
                                        <a class="dropdown-item notification-link" href="<?= base_url('notification/read/' . encode_id($nl->id)); ?>">
                                            <div class="notification-icon bg-<?= $nl->notif_type; ?>"><i class="<?= $nl->notif_icon; ?>"></i></div>
                                            <?php if (!$nl->is_read) { ?>
                                                <div class="notification-unread"></div>
                                            <?php } ?>
                                            <div class="notification-info">
                                                <div class="mb-1<?php if (!$nl->is_read) echo ' fw-bold text-app'; ?>"><?= $nl->title; ?></div>
                                                <div class="f-80 mb-2"><?= $nl->description; ?></div>
                                                <div class="f-75"><?= timeago($nl->notif_date); ?></div>
                                            </div>
                                        </a>
                                    </li>
                                <?php }
                            } else { ?>
                                <li>
                                    <div class="py-4 px-2 text-center"><i class="fa-bell f-lg"></i>
                                        <div class="mt-2"><?= lang('tidak_ada_pemberitahuan'); ?></div>
                                    </div>
                                </li>
                            <?php } ?>
                        </ul>
                        <?php if (count($__notif_lists)) { ?>
                            <ul class="notification-lists">
                                <li>
                                    <hr class="dropdown-divider" size="0">
                                </li>
                                <li>
                                    <a class="dropdown-item text-center" href="<?= base_url('notification'); ?>">
                                        <?= lang('semua_pemberitahuan'); ?>
                                    </a>
                                </li>
                            </ul>
                        <?php } ?>
                    </div>
                </div>
                <div class="dropdown setting-menu setting-profile">
                    <a href="#" class="profile dropdown-toggle" id="dropdownProfil" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= setting('header_color_primary') ? user('foto2') : user('foto'); ?>" alt="<?= user('nama'); ?>" class="rounded-circle" />
                        <div class="app-badge badge-bottom badge-success"></div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownProfil">
                        <li>
                            <a class="dropdown-item dropdown-item-icon" href="<?= base_url('account/profile'); ?>">
                                <i class="fa-user"></i><?= lang('profil'); ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item dropdown-item-icon" href="<?= base_url('account/change-password'); ?>">
                                <i class="fa-key"></i><?= lang('kata_sandi'); ?>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider" size="0">
                        </li>
                        <li>
                            <a class="dropdown-item dropdown-item-icon" href="<?= base_url('auth/logout'); ?>">
                                <i class="fa-sign-out"></i><?= lang('keluar'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            <?php } ?>
            <div class="setting-menu dropdown">
                <a href="#" class="dropdown-toggle" id="dropdownSetting" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"><i class="fa-ellipsis-v"></i></a>
                <div class="dropdown-menu dropdown-menu-end dropdown-setting p-3" aria-labelledby="dropdownSetting">
                    <div class="fw-bold mb-2"><?= lang('tema'); ?></div>
                    <div class="d-flex">
                        <div class="w-50 mb-1 me-1">
                            <a href="javascript:;" class="display-setting display-theme<?php if ($__theme == 'light') echo ' active'; ?>" data-val="light">
                                <i class="fa-sun"></i>
                                <?= lang('terang'); ?>
                            </a>
                        </div>
                        <div class="w-50 mb-1 ms-1">
                            <a href="javascript:;" class="display-setting display-theme<?php if ($__theme == 'dark') echo ' active'; ?>" data-val="dark">
                                <i class="fa-moon"></i>
                                <?= lang('gelap'); ?>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <div class="mt-1 w-100">
                            <a href="javascript:;" class="display-setting display-theme<?php if ($__theme == 'dark2') echo ' active'; ?>" data-val="dark2">
                                <i class="fa-moon-stars"></i>
                                <?= lang('gelap_alternatif'); ?>
                            </a>
                        </div>
                    </div>
                    <div class="fw-bold mb-2"><?= lang('desain'); ?></div>
                    <div class="d-flex mb-3">
                        <div class="w-50 me-1">
                            <a href="javascript:;" class="display-setting display-design<?php if (get_cookie('app-shadow-theme') == 'flat') echo ' active'; ?>" data-val="flat">
                                <i class="fa-square"></i>
                                <?= lang('datar'); ?>
                            </a>
                        </div>
                        <div class="w-50 ms-1">
                            <a href="javascript:;" class="display-setting display-design<?php if (get_cookie('app-shadow-theme') != 'flat') echo ' active'; ?>" data-val="shadow">
                                <i class="fa-clone"></i>
                                <?= lang('bayang'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-content<?= !isset($padding) || (isset($padding) && $padding) ? ' p-4' : ''; ?>">
            <?= $__content; ?>
        </div>
    </div>
    <?php if (isset($access['import']) && $access['import']) { ?>
        <div aria-hidden="true" tabindex="-1" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-title"><?= $title; ?><small><?= lang('import_data'); ?></small></div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="form-import-default" app-link="default" autocomplete="off">
                            <div class="row mb-1">
                                <label class="col-md-3 form-label required" for="file-import"><?= lang('file'); ?><small class="ms-2">(xls / xlsx)</small></label>
                                <div class="col-md-9">
                                    <input type="text" data-type="upload-file" id="file-import" name="file" data-validation="required" data-accept="xls|xlsx" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-9 offset-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="overwrite-import" name="overwrite" value="1">
                                        <label class="form-check-label" for="overwrite-import">
                                            <?= lang('timpa_data_jika_sudah_ada'); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-theme" data-bs-dismiss="modal"><?= lang('batal'); ?></button>
                        <button type="submit" class="btn btn-app" form="form-import-default"><?= lang('import'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <?= $__right_panel; ?>
    <?php if (isset($__print_header) && $__print_header) { ?>
        <div id="appinity-print-header" class="d-none">
            <?= $__print_header; ?>
        </div>
    <?php } ?>
    <?php
    Asset::js('jquery.js', true);
    Asset::js('jquery.fileupload.js', true);
    Asset::js('jquery.inputmask.js', true);
    Asset::js('jquery.contextmenu.js', true);
    Asset::js('bootstrap.bundle.min.js', true);
    Asset::js('hashids.min.js', true);
    Asset::js('moment.min.js', true);
    Asset::js('select2.full.js', true);
    Asset::js('lang_' . setting('language_code') . '.js', true);
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