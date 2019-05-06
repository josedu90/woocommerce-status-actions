<?php $met = false; ?>
<div class="tracking-progress-bar">
    <?php
    $percent = 100 / ((count($workflow_statuses) * 2) - 1);
    $width = $percent == 100 ? 99 : $percent;
    $active_background = get_option('wc_sa_workflow_active_color', '#96588a');
    $not_active_background = get_option('wc_sa_workflow_not_active_color', '#dddddd');

    if(empty($active_background)){
        $active_background = "#96588a";
    }

    if($not_active_background){
        $not_active_background = "#dddddd";
    }
    ?>
    <style>
	    .tracking-progress-bar__item--active span {
		    color: <?php echo !$met ? $active_background : $not_active_background ?>;
	    }
    </style>
    <?php foreach ($workflow_statuses as $key => $status): ?>
        <?php
        $icon = 'f6d8';
        $_status = wc_sa_get_status_by_name(str_replace('wc-', '', $key));
        if($_status){
            $_status = new WC_SA_Status($_status->id);
            $icon = $_status->status_icon;
        }
        ?>
        <div class="tracking-progress-bar__item <?php echo !$met ? 'tracking-progress-bar__item--active' : '' ?>"
             data-icon="<?php echo "&#x" . $icon ?>"
             style="width: <?php echo $width . '%' ?>; background: <?php echo !$met ? $active_background : $not_active_background ?>;"><span><?php echo $status ?></span>
        </div>
        <?php
        if($key == 'wc-' . $order->get_status()){
            $met = true;
        }
        ?>
        <span class="tracking-progress-bar__item__bar <?php echo !$met ? 'tracking-progress-bar__item__bar--active' : '' ?>"
              style="width: calc(<?php echo $width . '%' ?> - 2px); background:dddddd <?php echo !$met ? $active_background : $not_active_background ?>;">
        </span>
    <?php endforeach; ?>
</div>