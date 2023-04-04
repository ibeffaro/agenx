$(document).ready(function() {
    var relationColor       = '#417ff9';
    var $flowchart          = $('#flowchartworkspace');
    var $container          = $flowchart.parent();
    var $top                = 0;
    var $left               = 0;
    var $operatorProperties = $('.flowchart-action-container');
    var $buttonOperator     = $operatorProperties.find('.btn');
    var $flowSelected       = false;

    if(typeof $flowchart.attr('data-link-color') != 'undefined') {
        relationColor = $flowchart.attr('data-link-color');
    }

    $(document).on('contextmenu',function(e){
        $top    = e.pageY;
        $left   = e.pageX;
    });

    $(document).on('contextmenu','.flowchart-link',function(e){
        e.preventDefault();
        $(this).click();
        setTimeout(function(){
            $operatorProperties.css({
                top : $top,
                left : $left
            });
            $buttonOperator.text($buttonOperator.attr('data-label-link'));
            $operatorProperties.addClass('d-block');
        },100);
    });

    $(document).on('contextmenu','.flowchart-operator',function(e){
        e.preventDefault();
        $(this).children('.flowchart-operator-title').click();
        setTimeout(function(){
            $operatorProperties.css({
                top : $top,
                left : $left
            });
            $buttonOperator.text($buttonOperator.attr('data-label-tabel'));
            $operatorProperties.addClass('d-block');
        },100);
    });

    $flowchart.flowchart({
        defaultLinkColor: relationColor,
        data: typeof defaultFlowchartData != 'undefined' ? defaultFlowchartData : {},
        grid: 10,
        multipleLinksOnInput: false,
        multipleLinksOnOutput: true,
        onOperatorSelect: function(operatorId) {
            $flowSelected = true;
            return true;
        },
        onOperatorUnselect: function() {
            $operatorProperties.removeClass('d-block');
            $flowSelected = false;
            return true;
        },
        onLinkSelect: function(linkId) {
            $flowSelected = true;
            return true;
        },
        onLinkUnselect: function() {
            $operatorProperties.removeClass('d-block');
            $flowSelected = false;
            return true;
        }
    });

    $buttonOperator.click(function() {
        $flowchart.flowchart('deleteSelected');

        $('.list-table.d-none').removeClass('d-none');
        var op = $flowchart.flowchart('getData');
        if(typeof op.operators == 'object') {
            $.each(op.operators, function(k,v){
                $('.list-table[data-table="'+k+'"]').addClass('d-none');
            });
        }
    });

    $(document).on('keydown',function(e){
        if(e.which == 46 && $flowSelected) {
            $buttonOperator.click();
        }
    });

    $('.list-table').draggable({
        cursor: "move",
        opacity: 0.7,

        // helper: 'clone',
        appendTo: 'body',
        zIndex: 1000,

        helper: function(e) {
            var $this = $(this);
            var data = getOperatorData($this);
            return $flowchart.flowchart('getOperatorElement', data);
        },
        stop: function(e, ui) {
            var $this = $(this);
            var elOffset = ui.offset;
            var containerOffset = $container.offset();
            if (elOffset.left > containerOffset.left &&
                elOffset.top > containerOffset.top &&
                elOffset.left < containerOffset.left + $container.width() &&
                elOffset.top < containerOffset.top + $container.height()) {

                var flowchartOffset = $flowchart.offset();

                var relativeLeft = elOffset.left - flowchartOffset.left;
                var relativeTop = elOffset.top - flowchartOffset.top;

                var positionRatio = $flowchart.flowchart('getPositionRatio');
                relativeLeft /= positionRatio;
                relativeTop /= positionRatio;

                var data = getOperatorData($this);
                data.left = relativeLeft;
                data.top = relativeTop;

                $flowchart.flowchart('createOperator', $this.attr('data-table'), data);
                $this.addClass('d-none');
            }
        }
    });

    function getOperatorData($element) {
        var data = {
            properties: {
                title: $element.attr('data-table'),
                outputs: {},
                inputs: {},
                default: {}
            }
        };

        var pk  = $element.attr('data-pk').split('|');
        var fk  = $element.attr('data-fk').split('|');
        var def = $element.attr('data-def').split('|');

        $.each(pk, function(k,v){
            if(v) {
                data.properties.outputs[v] = {
                    label: v
                };
            }
        });

        $.each(fk, function(k,v){
            if(v) {
                data.properties.inputs[v] = {
                    label: v
                };
            }
        });

        $.each(def, function(k,v){
            if(v) {
                data.properties.default[v] = {
                    label: v
                };
            }
        });

        return data;
    }

    $('.list-table').dblclick(function() {
        var data = {
            top: rand(0, $flowchart.height() - 160), // toleransi disamain aja dengan width
            left: rand(0, $flowchart.width() - 160), // 160 = 10rem (width element di set di css)
            properties: {
                title: $(this).attr('data-table'),
                inputs: {},
                outputs: {},
                default: {}
            }
        };
        var pk  = $(this).attr('data-pk').split('|');
        var fk  = $(this).attr('data-fk').split('|');
        var def = $(this).attr('data-def').split('|');

        $.each(pk, function(k,v){
            if(v) {
                data.properties.outputs[v] = {
                    label: v
                };
            }
        });

        $.each(fk, function(k,v){
            if(v) {
                data.properties.inputs[v] = {
                    label: v
                };
            }
        });

        $.each(def, function(k,v){
            if(v) {
                data.properties.default[v] = {
                    label: v
                };
            }
        });

        $flowchart.flowchart('createOperator', $(this).attr('data-table'), data);
        $(this).addClass('d-none');
    });

    if(typeof defaultJSON != 'undefined') {
        var obj = JSON.parse(defaultJSON);
        $.each(obj.attr_flowchart,function(k,v){
            var $this = $('[data-table="'+k+'"]');
            var data = {
                top: v[1],
                left: v[0],
                properties: {
                    title: $this.attr('data-table'),
                    inputs: {},
                    outputs: {},
                    default: {}
                }
            };
            var pk  = $this.attr('data-pk').split('|');
            var fk  = $this.attr('data-fk').split('|');
            var def = $this.attr('data-def').split('|');
    
            $.each(pk, function(k,v){
                if(v) {
                    data.properties.outputs[v] = {
                        label: v
                    };
                }
            });
    
            $.each(fk, function(k,v){
                if(v) {
                    data.properties.inputs[v] = {
                        label: v
                    };
                }
            });
    
            $.each(def, function(k,v){
                if(v) {
                    data.properties.default[v] = {
                        label: v
                    };
                }
            });
    
            $flowchart.flowchart('createOperator', $this.attr('data-table'), data);
            $this.addClass('d-none');
        });

        $.each(obj.relations,function(k,v){
            var x       = v.split('=');
            var data    = {};
            if(x.length == 2) {
                var y1  = x[0].trim().split('.');
                var y2  = x[1].trim().split('.');
                if(y1.length == 2 && y2.length == 2) {
                    data = {
                        fromOperator    : y1[0],
                        fromConnector   : y1[1],
                        fromSubConnector: 0,
                        toOperator      : y2[0],
                        toConnector     : y2[1],
                        toSubConnector  : 0,
                    };
                    $flowchart.flowchart('createLink',k,data);
                }
            }
        });
    }

    $('#save-relasi').click(function(e){
        e.preventDefault();
        var data = $flowchart.flowchart('getData');
        if(typeof data.operators == 'object' && Object.keys(data.operators).length > 0) {

            if(typeof xhrAll.saveRelasi == 'undefined') xhrAll.saveRelasi = null;
            if(xhrAll.saveRelasi == null) {
                xhrAll.saveRelasi   = $.ajax({
                    url : baseURL('development/manajemen-database/save-relasi'),
                    data : {
                        data    : JSON.stringify(data),
                        key     : $('#flowchart-container').attr('data-key')
                    },
                    type : 'post',
                    dataType : 'json',
                    success : function(r) {
                        xhrAll.saveRelasi = null;
                        if(r.status == 'success') {
                            cAlert.open(r.message,r.status,'toMainMenu');
                        } else {
                            cAlert.open(r.message,r.status);
                        }
                    }
                });
            }

        }
    });

    $('#delete-relasi').click(function(e){
        e.preventDefault();
        cConfirm.open(lang.apakah_anda_yakin+'?','deleteRelasi');
    });
});
function toMainMenu() {
    window.location = baseURL("development/manajemen-database/relasi");
}
function deleteRelasi() {
    if(typeof xhrAll.deleteRelasi == 'undefined') xhrAll.deleteRelasi = null;
    if(xhrAll.deleteRelasi == null) {
        xhrAll.deleteRelasi   = $.ajax({
            url : baseURL('development/manajemen-database/delete-relasi'),
            data : {
                key     : $('#flowchart-container').attr('data-key')
            },
            type : 'post',
            dataType : 'json',
            success : function(r) {
                xhrAll.saveRelasi = null;
                if(r.status == 'success') {
                    cAlert.open(r.message,r.status,'toMainMenu');
                } else {
                    cAlert.open(r.message,r.status);
                }
            }
        });
    }
}