<?php
$met = false;
$status_count = count($workflow_statuses);
?>
<div id="placeholder" class="demo-placeholder"></div>
<style>
    #placeholder {
        width: 700px;
        height: 700px;
        margin: auto;
    }
    .pieLabel .active {
        color: #fff;
    }
    .pieLabel .innerLabel.active {
        color: #fff;
    }
    .pieLabel .innerLabel.active .counter {
	    background: #fff;
	    color: #333;
    }
    .pieLabel .innerLabel {
        text-align: center;
    }
    .pieLabel .innerLabel .counter {
	    border-radius: 100%;
	    width: 30px;
	    height: 30px;
	    line-height: 30px;
	    display: block;
	   	background: #333;
	   	color: #fff;
	    margin: auto;
    }
</style>
<script>
    jQuery(document).ready(function ($) {
        var data = [],
            statuses = <?php echo json_encode($workflow_statuses) ?>,
            statuses_count = Object.keys(statuses).length,
            counter = 0,
            current_status = <?php echo json_encode($order->get_status()); ?>,
            active_background = <?php echo json_encode(get_option('wc_sa_workflow_active_color', '#96588a')); ?>,
            not_active_background = <?php echo json_encode(get_option('wc_sa_workflow_not_active_color', '#dddddd')); ?>,
            met = false;

        if(active_background.trim().length < 1){
            active_background = "#96588a";
        }

        if(not_active_background.trim().length < 1){
            not_active_background = "#dddddd";
        }

        $.each(statuses, function (i, status) {
            var background = !met ? active_background : not_active_background;

            data[counter] = {
                label: status,
                data: 360 / statuses_count,
                counter: counter + 1,
                color: background,
                cssClasses: !met ? "active" : "not-active"
            };

            if(i === "wc-" + current_status){
                met = true;
            }

            counter++;
        });

        $.plot('#placeholder', data, {
            series: {
                pie: {
                    show: true,
                    radius: 1,
                    label: {
                        show: true,
                        radius: 3/4,
                        formatter: function labelFormatter(label, series) {
                            return "<div class='innerLabel "+series.cssClasses+"'><span class='counter'>" + series.counter + "</span><br/>" + label + "</div>";
                        },
                        background: {
                            opacity: 0.5,
                        }
                    }
                }
            },
            legend: {
                show: false
            }
        });
    });
</script>
