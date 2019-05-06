jQuery(document).ready(function($) {
	function stopSortStatuses(event, ui) {
        var postid     = ui.item.children('td').first().data('post_id'); // this post id
        var nextpostid = ui.item.next().children('td').first().data('post_id');
        var data = {
          action : 'wc_sa_sort',
          id     : postid,
          nextid : nextpostid
        }

        ui.item.find('.column-sort').append('<span class="spinner" style="display: block; visibility: visible;"></span>');
        ui.item.find('.column-sort').addClass('saving');
        $.ajax({
          url: wc_sa_sortable_opt.ajax_url,
          data: data,
          type: 'POST',
          success: function( response ) {
            ui.item.find('.column-sort .spinner').remove();
            ui.item.find('.column-sort').removeClass('saving');
          }
        });
    }

    function changeWorkflowOrder(event, ui) {
        var postid     = ui.item.data('status-id');
        var workflowOrder = {};
        $(".wc_sa_workflow_order_item").each(function(i, el){
            var key = $(el).parents('tr').data('status-key');
            workflowOrder[key] = $(el).val();
        });
        var data = {
            action : 'wc_sa_save_status_workflow_order',
            order: workflowOrder
        };

        ui.item.find('.column-sort').append('<span class="spinner" style="display: block; visibility: visible;"></span>');
        ui.item.find('.column-sort').addClass('saving');
        $.ajax({
            url: wc_sa_sortable_opt.ajax_url,
            data: data,
            type: 'POST',
            success: function( response ) {
                ui.item.find('.column-sort .spinner').remove();
                ui.item.find('.column-sort').removeClass('saving');
                $("#wc_sa_status_workflow_order").val(workflowOrder);
            }
        });
    }
  
  if($('table.widefat').not('.wc-sa-statuses-workflow').length > 0 ){
    $( "table.widefat tbody" ).sortable({
          placeholder: "ui-state-highlight",
          axis: "y",
          handle: ".column-sort",
          stop: stopSortStatuses
        });
  }

    var workflowTable = $('table.wc-sa-statuses-workflow');
    if(workflowTable.length > 0 ){
        workflowTable.find('tbody').sortable({
            placeholder: "ui-state-highlight",
            axis: "y",
            handle: ".column-sort",
            stop: changeWorkflowOrder
        });
    }

});