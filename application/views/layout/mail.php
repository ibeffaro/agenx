<div style="background: #f9fbfd; padding: 25px 0;">
    <div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Roboto','Ubuntu','Open Sans','Helvetica Neue',sans-serif; max-width: 500px; margin: 20px auto; color: #454c56">
        <img src="<?=base_url(upload_path('settings').setting('logo'));?>" alt="<?=setting('title');?>" width="250" />
        <div style="margin-top: 10px; border-top: 4px solid <?=setting('color');?>; border-radius: 4px; background: #fff;">
            <div style="padding: 20px; margin-bottom: 20px;">
                <?=$content;?>
            </div>
            <?php if(setting('company_name') && setting('company_logo')) { ?>
            <div style="background: #f2f3f7; padding: 20px;">
                <img src="<?=base_url(upload_path('settings').setting('company_logo'));?>" alt="<?=setting('company_name');?>" width="100" style="display: block; margin-bottom: 10px" />
                <div style="font-weight: 600; margin-bottom: 5px;"><?=setting('company_name');?></div>
                <?php if(setting('company_address')) { ?>
                <div style="color: #778092; margin-bottom: 5px;">
                    <?=str_replace("\n",'<br />',setting('company_address'));?>
                </div>
                <?php } ?>
                <table width="100%" style="color: #778092;">
                    <?php if(setting('company_email')) { ?>
                    <tr>
                        <td width="100">Surel</td>
                        <td width="5">:</td>
                        <td><a href="mailto:<?=setting('company_email');?>"><?=setting('company_email');?></a></td>
                    </tr>
                    <?php } if(setting('company_phone')) { ?>
                    <tr>
                        <td width="100">Telepon</td>
                        <td width="5">:</td>
                        <td><?=setting('company_phone');?></td>
                    </tr>
                    <?php } if(setting('company_fax')) { ?>
                    <tr>
                        <td width="100">Faksimili</td>
                        <td width="5">:</td>
                        <td><?=setting('company_fax');?></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
            <?php } ?>
        </div>
    </div>
</div>