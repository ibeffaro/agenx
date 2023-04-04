<action-header>
    <button class="btn btn-app" id="readAll"><i class="fa-check"></i><span>ln{tandai_sudah_dilihat}</span></button>
</action-header>
<div class="row justify-content-center">
    <div class="col-xl-5 col-lg-7 col-md-8 col-sm-10">
        <app-card>
            <ul id="notification" class="notification-menu-list"></ul>
        </app-card>
    </div>
</div>
<script>
$(document).ready(function(){
    loadNotification($('#notification'));
});
$('#main-panel .panel-content').scroll(function(){
    var scrollTop   = Math.round($(this).scrollTop() + $(this).outerHeight());
    var limitScroll = $(this)[0].scrollHeight - 100;
    if(scrollTop >= limitScroll) { 
        loadNotification($('#notification'));
    }
});
$('#readAll').click(function(){
    cConfirm.open(lang.apakah_anda_yakin+'?','readAll');
});
function readAll() {
    $.get(baseURL('notification/read-all/') + encodeId(rand()),function(r){
        reload();
    });
}
</script>