<?php
/**
 * @var WC_SA_Status $status
 */
?>


<a id="go-back-button" style="display: none"
   href="<?php echo home_url() ?>/wp-admin/admin.php?page=wc-settings&amp;tab=wc_sa_settings"><img draggable="false" class="emoji" alt="⤴" src="https://s.w.org/images/core/emoji/2.4/svg/2934.svg"></a>
<div class="panel-wrap <?php echo wc_sa_is_core_status($status->label) ? 'status-core' : 'status-custom'; ?>">
    <ul class="wc-sa-tabs wc-tabs">
        <li class="general_options active">
            <a href="#general_options_tab"><?php _e('General', 'woocommerce_status_actions'); ?></a>
        </li>
        <li class="style_options">
            <a href="#style_options_tab"><?php _e('Style', 'woocommerce_status_actions'); ?></a>
        </li>
        <li class="email_options">
            <a href="#email_options_tab"><?php _e('Email', 'woocommerce_status_actions'); ?></a>
        </li>
        <li class="customer_options">
            <a href="#customer_options_tab"><?php _e('Customer', 'woocommerce_status_actions'); ?></a>
        </li>
        <li class="product_options">
            <a href="#product_options_tab"><?php _e('Product', 'woocommerce_status_actions'); ?></a>
        </li>
        <li class="action_options">
            <a href="#action_options_tab"><?php _e('Action', 'woocommerce_status_actions'); ?></a>
        </li>
    </ul>
    <div class="panel wc_sa_options_panel woocommerce_options_panel" style="display: block;" id="general_options_tab">
        <div class="options_group">
            <?php if(!wc_sa_is_core_status($status->label)): ?>
            <div class="options_group">
                <p class="form-field">
                    <label for="Label"><?php _e('Slug', 'woocommerce_status_actions'); ?><span
                                class="required">*</span></label>
                    <input type="text" name="post_name" id="status_label" value="<?php echo $status->label; ?>"
                           maxlength="17">
                    <span class="woocommerce-help-tip"
                          data-tip="<?php _e('Enter the label of the status which you would like to add. This should be lower case as will be displayed on the status label.', 'woocommerce_status_actions'); ?>"</span>
                </p>
            </div>
            <?php endif; ?>
            <p class="form-field">
                <label for="status_reports"><?php _e('Reports', 'woocommerce_status_actions'); ?></label>
                <input type="checkbox" name="display_in_reports"
                       id="status_reports" <?php checked('yes', $status->display_in_reports, true); ?> value="yes">
                <span class="description"><?php _e('Check this box allow this status to be considered as a placed order in the reports.', 'woocommerce_status_actions'); ?></span>
            </p>
            <p class="form-field">
                <label for="status_dashboard_widget"><?php _e('Dashboard Widget', 'woocommerce_status_actions'); ?></label>
                <input type="checkbox" name="dashboard_widget"
                       id="status_dashboard_widget" <?php checked('yes', $status->dashboard_widget, true); ?>
                       value="yes">
                <span class="description"><?php _e('Enable to display an order count with this status in the Dashboard widget.', 'woocommerce_status_actions'); ?></span>
            </p>
        </div>
    </div>
    <div class="panel wc_sa_options_panel woocommerce_options_panel" id="style_options_tab">
        <div class="options_group">
            <p class="form-field">
                <label for="status_icon"><?php _e('Icon', 'woocommerce_status_actions'); ?></label>
                <input type="text" name="status_icon" id="status_icon" value="<?php echo $status->status_icon; ?>"
                       class="icon-picker-field status_icon">

            </p>
            <p class="form-field">
                <label for="status_colour"><?php _e('Colour', 'woocommerce_status_actions'); ?></label>
                <span class="colorpickpreview" style="background-color: <?php echo $status->status_colour; ?>"></span>
                <input type="text" name="status_colour" id="status_colour" value="<?php echo $status->status_colour; ?>"
                       class="color-picker-field">
            </p>
            <?php if (version_compare(WC_VERSION, '3.3', '>=')) { ?>
                <fieldset class="form-field _icon_style_field ">
                    <legend><?php _e('Style', 'woocommerce_status_actions'); ?></legend>
                    <?php
                    $icod = !empty($status->status_icon) ? $status->status_icon : 'e301';
                    $icolor = !empty($status->status_colour) ? $status->status_colour : '#828282';
                    $istyles = array(
                        'background-color:' . $icolor . '40; color:' . $icolor . ';',
                        'color:' . $icolor . '; border-color:' . $icolor . ';',
                    );
                    ?>
                    <?php if (get_option('woocommerce_status_actions_default_style', '1') == '1') { ?>
                        <span class="wc-sa-icon_style wc-sa-text-color" style="<?php echo $istyles[0]; ?>">
                            echo $status->title; ?>
							</span>
                    <?php } else { ?>
                        <span class="wc-sa-icon_style wc-sa-icon-color" style="<?php echo $istyles[0]; ?>">
								<span class="wc-sa-icon-uni<?php echo $icod; ?>"></span>
							</span>
                    <?php } ?>
                </fieldset>
            <?php } else { ?>
                <fieldset class="form-field _icon_style_field ">
                    <legend><?php _e('Style', 'woocommerce_status_actions'); ?></legend>
                    <?php
                    $icod = !empty($status->status_icon) ? $status->status_icon : 'e301';
                    $icolor = !empty($status->status_colour) ? $status->status_colour : '#828282';
                    $istyles = array(
                        'background-color:' . $icolor . '; border-color:' . $icolor . ';',
                        'color:' . $icolor . '; border-color:' . $icolor . ';',
                    );
                    ?>
                    <ul class="wc-radios">
                        <li>
                            <label>
                                <input type="radio" style="" class="select short input-wc-sa-icon" value="icon-color"
                                       name="icon_style" <?php checked('icon-color', $status->icon_style, true); ?> >
                                <span class="wc-sa-icon_style wc-sa-icon-color" style="<?php echo $istyles[0]; ?>">
								<i class="wc-sa-icon-uni<?php echo $icod; ?>"></i>
							</span>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" style="" class="select short input-wc-sa-icon" value="icon-outline"
                                       name="icon_style" <?php checked('icon-outline', $status->icon_style, true); ?>>
                                <span class="wc-sa-icon_style wc-sa-icon-outline" style="<?php echo $istyles[1]; ?>">
								<i class="wc-sa-icon-uni<?php echo $icod; ?>"></i>
							</span>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" style="" class="select short" value="text-color"
                                       name="icon_style" <?php checked('text-color', $status->icon_style, true); ?>>
                                <span class="wc-sa-icon_style wc-sa-text-color" style="<?php echo $istyles[0]; ?>">
							<?php
                            $title = $status->get_title();
                            echo !empty($status->title) ? $status->title : __('Status', 'woocommerce_status_actions'); ?>
							</span>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" style="" class="select short" value="text-outline"
                                       name="icon_style" <?php checked('text-outline', $status->icon_style, true); ?>>
                                <span class="wc-sa-icon_style wc-sa-text-outline" style="<?php echo $istyles[1]; ?>">
								<?php echo !empty($status->title) ? $status->title : __('Status', 'woocommerce_status_actions'); ?>
							</span>
                            </label>
                        </li>
                    </ul>
                </fieldset>
            <?php } ?>
        </div>
    </div>

    <div class="panel wc_sa_options_panel woocommerce_options_panel" id="email_options_tab">
        <div class="options_group">
            <p class="form-field">
                <label for="status_email_notification"><?php _e('Email Notification', 'woocommerce_status_actions'); ?></label>
                <input type="checkbox" name="email_notification"
                       id="status_email_notification" <?php checked('yes', $status->email_notification, true); ?>
                       value="yes">
                <span class="description"><?php _e('Enable email notification', 'woocommerce_status_actions'); ?></span>
            </p>
            <div class="show_if_email_notification">
                <div class="options_group">
                    <p class="form-field">
                        <label for="status_email_type"><?php _e('Email Type', 'woocommerce_status_actions'); ?></label>
                        <select name="email_type" id="status_email_type">
                            <option value="html" <?php selected($status->email_type, 'html', true); ?>><?php _e('HTML', 'woocommerce_status_actions'); ?></option>
                            <option value="plain" <?php selected($status->email_type, 'plain', true); ?> ><?php _e('Plain text', 'woocommerce_status_actions'); ?></option>
                        </select>
                        <span class="woocommerce-help-tip"
                              data-tip="<?php _e('Choose which format of email to send.', 'woocommerce_status_actions'); ?>"</span>
                    </p>
                    <p class="form-field">
                        <label for="status_email_recipients"><?php _e('Recipient(s)', 'woocommerce_status_actions'); ?></label>
                        <select name="email_recipients" id="status_email_recipients">
                            <?php
                            $email_to = array(
                                'customer' => __('Customer', 'woocommerce_status_actions'),
                                'admin' => __('Administrator', 'woocommerce_status_actions'),
                                'both' => __('Administrator & Customer', 'woocommerce_status_actions'),
                                'custom' => __('Custom Email Address', 'woocommerce_status_actions')
                            );
                            foreach ($email_to as $key => $value) {
                                ?>
                                <option value="<?php echo $key; ?>" <?php selected($status->email_recipients, $key, true); ?>><?php echo $value; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                        <span class="woocommerce-help-tip"
                              data-tip="<?php printf(
                                                __('Choose to whom you want the email to be sent to when the custom status is applied. Current admin email address %s.', 'woocommerce_status_actions'),
                                                '<code>' . get_option('admin_email') . '</code>'
                                        ) ?>">
                        </span>
                    </p>
                    <p class="form-field show_if_email_recipients_custom">
                        <label for="status_email_custom_address"><?php _e('Recipient Email Address', 'woocommerce_status_actions'); ?>
                            <span class="required">*</span></label>
                        <input type="text" name="email_custom_address" id="status_email_custom_address"
                               value="<?php echo $status->email_custom_address; ?>">
                        <span class="description"><?php _e('Enter the email address which you want to be notified when status is trigger.', 'woocommerce_status_actions'); ?></span>
                    </p>
                </div>
                <div class="options_group">
                    <p class="form-field">
                        <label for="status_email_from_name"><?php _e('"From" Name', 'woocommerce_status_actions'); ?>
                            <span
                                    class="required">*</span></label>
                        <input type="text" name="email_from_name" id="status_email_from_name"
                               value="<?php echo $status->email_from_name; ?>"
                               placeholder="<?php echo get_option('blogname'); ?>">
                        <span class="woocommerce-help-tip"
                              data-tip="<?php _e('Enter the email name which will appear when the email is sent.', 'woocommerce_status_actions'); ?>"</span>
                    </p>
                    <p class="form-field">
                        <label for="status_email_from_address"><?php _e('"From" Email Address', 'woocommerce_status_actions'); ?>
                            <span class="required">*</span></label>
                        <input type="text" name="email_from_address" id="status_email_from_address"
                               value="<?php echo $status->email_from_address; ?>"
                               placeholder="<?php echo get_option('admin_email'); ?>">
                        <span class="woocommerce-help-tip"
                              data-tip="<?php _e('Enter the email address which will appear when the email is sent.', 'woocommerce_status_actions'); ?>"</span>
                    </p>
                </div>
                <div class="options_group">
                    <p class="form-field">
                        <label for="status_email_subject"><?php _e('Email Subject', 'woocommerce_status_actions'); ?>
                            <span
                                    class="required">*</span></label>
                        <input type="text" name="email_subject" id="status_email_subject"
                               value="<?php echo $status->email_subject; ?>" <?php echo empty($status->email_subject) ? 'placeholder="' . $status->email_subject_default . '"' : ''; ?> >
                        <span class="woocommerce-help-tip"
                              data-tip="<?php _e('Enter the email subject which will appear when the email is sent.', 'woocommerce_status_actions'); ?>"></span>
                    </p>
                    <p class="form-field">
                        <label for="status_email_heading"><?php _e('Email Heading', 'woocommerce_status_actions'); ?>
                            <span
                                    class="required">*</span></label>
                        <input type="text" name="email_heading" id="status_email_heading"
                               value="<?php echo $status->email_heading; ?>" <?php echo empty($status->email_heading) ? 'placeholder="' . $status->email_heading_default . '"' : ''; ?>>
                        <span class="woocommerce-help-tip"
                              data-tip="<?php _e('Enter the heading which you want to appear on the email that is sent.', 'woocommerce_status_actions'); ?>"></span>
                    </p>
                    <p class="form-field">
                        <label for="status_email_attachment">
                            <?php _e('Email Attachment', 'woocommerce_status_actions'); ?>
                            <span class="required">*</span>
                        </label>
                        <button class="button" id="choose_email_attachment" type="button"><?php _e("Choose files") ?></button>
                        <input type="hidden" name="email_attachments" id="status_email_attachment" value="<?php echo implode(",", $status->email_attachments) ?>">
                        <span class="woocommerce-help-tip"
                              data-tip="<?php _e('Attachments, which is to be attached in email.', 'woocommerce_status_actions'); ?>">
                        </span>
                        <span id="attachments_container">
                            <?php
                            if(count($status->email_attachments)){
                                foreach ($status->email_attachments as $attachment): ?>
                                    <?php
                                    if(empty($attachment))
                                        continue;

                                    $type = get_post_mime_type($attachment);
                                    $img_types = array('image/jpeg', 'image/png', 'image/gif');
                                    $url = in_array($type, $img_types) ? wp_get_attachment_url($attachment) : includes_url("images/media/document.png");
                                    ?>
                                    <span class="att-wrapper" data-id="<?php echo $attachment ?>">
                                        <span class="att-clr"></span>
                                        <img src="<?php echo $url ?>">
                                    </span>
                                <?php endforeach;
                            }
                            ?>
                        </span>
                    </p>
                </div>
                <div class="options_group">
                    <p class="form-field">
                        <label for="status_email_order_info"><?php _e('Order Information', 'woocommerce_status_actions'); ?></label>
                        <input type="checkbox" name="email_order_info"
                               id="status_email_order_info" <?php checked('yes', $status->email_order_info, true); ?>
                               value="yes">
                        <span class="description"><?php _e('Check this box to include order information which consists of billing and shipping address, contact details, order items, subtotal and total.', 'woocommerce_status_actions'); ?></span>
                    </p>
                    <p class="form-field">
                        <label for="status_email_message"><?php _e('Custom Message', 'woocommerce_status_actions'); ?></label>
                        <input type="checkbox" name="email_message"
                               id="status_email_message" <?php checked('yes', $status->email_message, true); ?>
                               value="yes">
                        <span class="description"><?php _e('Check this box to include a custom message to the email body. This can include shortcodes from third party plugins.', 'woocommerce_status_actions'); ?></span>
                    </p>
                    <p class="form-field show_if_custom_message">
                        <label for="status_email_custom_message"><?php _e('Message', 'woocommerce_status_actions'); ?></label>
                    <div class="show_if_custom_message" id="status_email_custom_message">
                        <?php
                        $settings = array(
                            'media_buttons' => false,
                        );
                        wp_editor($status->email_message_text, 'email_message_text', $settings);
                        ?>
                    </div>
                    <p class="form-field show_if_custom_message"><?php _e('Enter the message which will be sent in the email. Click on the trolley icon to load available shortcodes.', 'woocommerce_status_actions'); ?></p>
                    </p>
                </div>
                <p>
					<span class="description">
						<?php printf(__('You can customise the look and layout of the emails by clicking %s.', 'woocommerce_status_actions'), '<a href="' . admin_url('admin.php?page=wc-settings&tab=email') . '" target="_blank">' . __('here', 'woocommerce_status_actions') . '</a>'); ?>
					</span>
                </p>
            </div>
        </div>
    </div>
    <div class="panel wc_sa_options_panel woocommerce_options_panel" id="customer_options_tab">
        <div class="options_group">
            <p class="form-field">
                <label for="status_customer_pay_button"><?php _e('Pay Button', 'woocommerce_status_actions'); ?></label>
                <input type="checkbox" name="customer_pay_button"
                       id="status_customer_pay_button" <?php checked('yes', $status->customer_pay_button, true); ?>
                       value="yes">
                <span class="description"><?php _e('Enable this to display "Pay" button on the "My Account" page when this status is applied.', 'woocommerce_status_actions'); ?></span>
            </p>
            <p class="form-field">
                <label for="status_customer_cancel_orders"><?php _e('Cancel Orders', 'woocommerce_status_actions'); ?></label>
                <input type="checkbox" name="customer_cancel_orders"
                       id="status_customer_cancel_orders" <?php checked('yes', $status->customer_cancel_orders, true); ?>
                       value="yes">
                <span class="description"><?php _e('Enable this to allow customer to cancel orders when this status is applied.', 'woocommerce_status_actions'); ?></span>
            </p>
            <h2 class="status_label_header"><?php _e('Customer Permissions', 'woocommerce_status_actions'); ?>
            </h2>
            <p class="status_label_description description">
                <?php _e('Provide the ability for customers to inform you of their status on an order through their My Account page.', 'woocommerce_status_actions'); ?>
            </p>
            <div class="options_group">
                <p class="form-field">
                    <label for="status_customer_account"><?php _e('My Account', 'woocommerce_status_actions'); ?></label>
                    <input type="checkbox" name="customer_account"
                           id="status_customer_account" <?php checked('yes', $status->customer_account, true); ?>
                           value="yes">
                    <span class="description"><?php _e('Check this box to allow customer to set this status from the My Account page.', 'woocommerce_status_actions'); ?></span>
                </p>
                <p class="form-field show_if_customer_account">
                    <label for="status_customer_account_visibility"><?php _e('Visibility Rule', 'woocommerce_status_actions'); ?>
                        <span class="required">*</span></label>
                    <select multiple="multiple" name="customer_account_visibility[]"
                            id="status_customer_account_visibility"
                            class="wc-enhanced-select"
                            data-placeholder="<?php esc_attr_e('Select Status', 'woocommerce_status_actions'); ?>"
                            style="min-width: 200px; width: 50%;">
                        <?php
                        foreach ($order_statuses as $status_key => $status_name) {
                            if ($status_key == 'wc-' . $status->slug) {
                                continue;
                            }
                            ?>
                            <option value="<?php echo $status_key; ?>" <?php selected(in_array($status_key, $status->customer_account_visibility), true, true); ?>><?php echo $status_name; ?></option>
                            <?php
                        }
                        ?>
                    </select>
                    <span class="woocommerce-help-tip"
                          data-tip="<?php _e('Select what the status of the order has to be before the button is shown.', 'woocommerce_status_actions'); ?>"</span>
                </p>
                <p class="form-field show_if_customer_account">
                    <label for="status_customer_account_button_label"><?php _e('Button Label', 'woocommerce_status_actions'); ?>
                        <span class="required">*</span></label>
                    <input type="text" name="customer_account_button_label" id="status_customer_account_button_label"
                           value="<?php echo $status->customer_account_button_label; ?>">
                    <span class="woocommerce-help-tip"
                          data-tip="<?php _e('Enter the text that appears on the button.', 'woocommerce_status_actions'); ?>"</span>
                </p>
                <p class="form-field show_if_customer_account">
                    <label for="status_customer_confirm_prompt"><?php _e('Confirmation Prompt', 'woocommerce_status_actions'); ?></label>
                    <input type="checkbox" name="customer_confirm_prompt"
                           id="status_customer_confirm_prompt" <?php checked('yes', $status->customer_confirm_prompt, true); ?>
                           value="yes">
                    <span class="description"><?php _e('Enable this to show the "Prompt" window when the user click on button.', 'woocommerce_status_actions'); ?></span>
                </p>
            </div>
            <p class="form-field">
                <label for="status_product_reviews"><?php _e('Product Reviews', 'woocommerce_status_actions'); ?></label>
                <input type="checkbox" name="product_reviews"
                       id="status_product_reviews" <?php checked('yes', $status->product_reviews, true); ?> value="yes">
                <span class="description"><?php _e('Check this box to allow customers to leave a review for the products purchased in the order when this status set.', 'woocommerce_status_actions'); ?></span>
            </p>
        </div>
    </div>
    <div class="panel wc_sa_options_panel woocommerce_options_panel" id="product_options_tab">
        <div class="options_group">
            <p class="form-field">
                <label for="status_downloads_permitted"><?php _e('Downloads Permitted', 'woocommerce_status_actions'); ?></label>
                <input type="checkbox" name="downloads_permitted"
                       id="status_downloads_permitted" <?php checked('yes', $status->downloads_permitted, true); ?>
                       value="yes">
                <span class="description"><?php _e('Enable to permit downloads of virtual products in the order when this status is set.', 'woocommerce_status_actions'); ?></span>
            </p>
            <p class="form-field">
                <label for="status_item_editing"><?php _e('Item Editing', 'woocommerce_status_actions'); ?></label>
                <input type="checkbox" name="item_editing"
                       id="status_item_editing" <?php checked('yes', $status->item_editing, true); ?> value="yes">
                <span class="description"><?php _e('Enable to allow item editing in the order. If unchecked, the order would be considered paid and no edits can be made.', 'woocommerce_status_actions'); ?></span>
            </p>
            <p class="form-field">
                <label for="status_stock_status"><?php _e('Stock Status', 'woocommerce_status_actions'); ?></label>
                <select name="stock_status" id="status_stock_status">
                    <?php
                    $stock_status_act = array(
                        'no_affection' => __('No affection to stock status', 'woocommerce_status_actions'),
                        'reduce' => __('Reduce stock levels', 'woocommerce_status_actions'),
                        'restore' => __('Restore stock levels', 'woocommerce_status_actions')
                    );
                    foreach ($stock_status_act as $key => $value) {
                        ?>
                        <option value="<?php echo $key; ?>" <?php selected($status->stock_status, $key, true); ?>><?php echo $value; ?></option>
                        <?php
                    }
                    ?>
                </select>
                <span class="woocommerce-help-tip"
                      data-tip="<?php _e('Select what happens to the stock levels of the products when the status is applied to the order.', 'woocommerce_status_actions'); ?>"</span>
            </p>
        </div>
    </div>
    <div class="panel wc_sa_options_panel woocommerce_options_panel" id="action_options_tab">
        <div class="options_group">
            <p class="form-field relative">
                <label for="status_action_icon"><?php _e('Action Icon', 'woocommerce_status_actions'); ?></label>
                <span class="selector-button"
                      id="use-style-icon"><?php echo __('Use Style Icon', 'woocommerce_status_actions') ?></span>
                <input type="text" name="action_icon" id="status_action_icon"
                       value="<?php echo $status->action_icon; ?>" class="icon-picker-field">
                <span class="woocommerce-help-tip style-icon-tip"
                      data-tip="<?php _e('Select the icon you would like to display as the Action button.', 'woocommerce_status_actions'); ?>"</span>
            </p>
            <h2 class="status_label_header"><?php _e('Workflow', 'woocommerce_status_actions'); ?>
            </h2>
            <p class="status_label_description description">
                <?php _e('Select the statuses required of an order where this status can be assigned.', 'woocommerce_status_actions'); ?>
            </p>
            <p class="form-field">
                <label for="status_action_visibility"><?php _e('Action Visibility', 'woocommerce_status_actions'); ?></label>
                <select multiple="multiple" name="action_visibility[]" id="status_action_visibility"
                        class="wc-enhanced-select"
                        data-placeholder="<?php esc_attr_e('Select Status', 'woocommerce_status_actions'); ?>"
                        style="min-width: 200px; width: 50%;">
                    <?php
                    foreach ($order_statuses as $status_key => $status_name) {
                        if ($status_key == 'wc-' . $status->label) {
                            continue;
                        }
                        ?>
                        <option value="<?php echo $status_key; ?>" <?php selected(in_array($status_key, $status->action_visibility), true, true); ?>><?php echo $status_name; ?></option>
                        <?php
                    }
                    ?>
                </select>
                <span class="woocommerce-help-tip"
                      data-tip="<?php _e('Select which statuses need to be applied to the order before the action button is shown. Leave blank to not display the action button.', 'woocommerce_status_actions'); ?>"</span>
            </p>
            <h2 class="status_label_header"><?php _e('Permissions', 'woocommerce_status_actions'); ?>
            </h2>
            <p class="status_label_description description">
                <?php _e('Control who can assign this status to an order and who can remove the assignment of this status from an order.', 'woocommerce_status_actions'); ?>
            </p>
            <p class="form-field">
                <label for="status_action_users"><?php _e('To Unassign', 'woocommerce_status_actions'); ?></label>
                <select multiple="multiple" name="action_from_users[]" id="status_action_users"
                        class="wc-customer-search"
                        data-placeholder="<?php esc_attr_e('Select Users', 'woocommerce_status_actions'); ?>"
                        style="min-width: 200px; width: 50%;">
                    <?php if ($status->action_from_users) { ?>
                        <?php foreach ($status->action_from_users as $u_id) { ?>
                            <?php
                            $customer = new WC_Customer($u_id);
                            /* translators: 1: user display name 2: user ID 3: user email */
                            $found_customer = sprintf(
                                esc_html__('%1$s (#%2$s &ndash; %3$s)', 'woocommerce'),
                                $customer->get_first_name() . ' ' . $customer->get_last_name(),
                                $customer->get_id(),
                                $customer->get_email()
                            );
                            ?>
                            <option value="<?php echo $u_id ?>" selected><?php echo $found_customer ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
                <span class="woocommerce-help-tip"
                      data-tip="<?php _e('Select the users that are allowed to execute this action. Leave blank for everyone.', 'woocommerce_status_actions'); ?>"</span>
            </p>
            <p class="form-field">
                <label for="status_action_users"><?php _e('To Assign', 'woocommerce_status_actions'); ?></label>
                <select multiple="multiple" name="action_to_users[]" id="status_action_users"
                        class="wc-customer-search"
                        data-placeholder="<?php esc_attr_e('Select Users', 'woocommerce_status_actions'); ?>"
                        style="min-width: 200px; width: 50%;">
                    <?php if ($status->action_to_users) { ?>
                        <?php foreach ($status->action_to_users as $u_id) { ?>
                            <?php
                            $customer = new WC_Customer($u_id);
                            /* translators: 1: user display name 2: user ID 3: user email */
                            $found_customer = sprintf(
                                esc_html__('%1$s (#%2$s &ndash; %3$s)', 'woocommerce'),
                                $customer->get_first_name() . ' ' . $customer->get_last_name(),
                                $customer->get_id(),
                                $customer->get_email()
                            );
                            ?>
                            <option value="<?php echo $u_id ?>" selected><?php echo $found_customer ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
                <span class="woocommerce-help-tip"
                      data-tip="<?php _e('Select the users that are allowed to execute this action. Leave blank for everyone.', 'woocommerce_status_actions'); ?>"</span>
            </p>
            <h2 class="status_label_footer"></h2>
            <p class="form-field">
                <label for="status_hide_bulk_actions"><?php _e('Hide Bulk Actions', 'woocommerce_status_actions'); ?></label>
                <input type="checkbox" name="hide_bulk_actions"
                       id="status_hide_bulk_actions" <?php checked('yes', $status->hide_bulk_actions, true); ?>
                       value="yes">
                <span class="description"><?php _e('Check this box to hide the action for this status from the Bulk Actions menu.', 'woocommerce_status_actions'); ?></span>
            </p>
            <p class="form-field">
                <label for="status_order_note_prompt"><?php _e('Order Note Prompt', 'woocommerce_status_actions'); ?></label>
                <input type="checkbox" name="order_note_prompt"
                       id="status_order_note_prompt" <?php checked('yes', $status->order_note_prompt, true); ?>
                       value="yes">
                <span class="description"><?php _e('Check this box to display a modal window to enter a custom note when clicking on the action button.', 'woocommerce_status_actions'); ?></span>
            </p>
            <?php if (get_option('wc_sa_google_cloud_enable', 'no') == 'yes') { ?>
                <h2 class="status_label_header"><?php _e('Google Cloud Printing', 'woocommerce_status_actions'); ?>
                </h2>
                <p class="status_label_description description">
                    <?php _e('Print an invoice automatically, over the cloud, when this status is assigned to an order.', 'woocommerce_status_actions'); ?>
                </p>
                <p class="form-field">
                    <label for="status_google_print"><?php _e('Enable / Disable', 'woocommerce_status_actions'); ?></label>
                    <input type="checkbox" name="google_print"
                           id="status_google_print" <?php checked('yes', $status->google_print, true); ?>
                           value="yes">
                    <span class="description"><?php _e('Enable printing when this status is assigned to an order.', 'woocommerce_status_actions'); ?></span>
                </p>
                <p class="form-field">
                    <label for="status_google_print_printer"><?php _e('Printer', 'woocommerce_status_actions'); ?></label>
                    <select name="google_print_printer" id="status_google_print_printer"
                            data-placeholder="<?php esc_attr_e('Select Printer', 'woocommerce_status_actions'); ?>"
                            data-allow_clear="true" style="min-width: 200px; width: 50%;">
                        <option value=""></option>
                        <?php
                        foreach ($cloudPrint->get_printers() as $printer) { ?>
                            <option value="<?php echo $printer->id; ?>" <?php selected($printer->id, $status->google_print_printer, true); ?>><?php echo $printer->displayName; ?></option>
                            <?php
                        }
                        ?>
                    </select>
                    <span class="woocommerce-help-tip"
                          data-tip="<?php _e('Select the printer to send printing to.', 'woocommerce_status_actions'); ?>"</span>
                </p>
                <p class="form-field">
                    <label for="status_google_print_copies"><?php _e('Copies', 'woocommerce_status_actions'); ?>
                        <span class="required">*</span></label>
                    <input style="width: 50px;" type="number" name="google_print_copies" id="status_google_print_copies"
                           value="<?php echo $status->google_print_copies ?>">
                    <span class="woocommerce-help-tip"
                          data-tip="<?php _e('Set the number of copies to print.', 'woocommerce_status_actions'); ?>"</span>
                </p>
                <p class="form-field">
                    <label for="status_google_print_template"><?php _e('Template', 'woocommerce_status_actions'); ?>
                        <span class="required">*</span></label>
                    <select name="google_print_template" id="status_google_print_template"
                            data-placeholder="<?php esc_attr_e('Choose an option', 'woocommerce_status_actions'); ?>"
                            data-allow_clear="true" style="min-width: 200px; width: 50%;">
                        <option value="invoice" <?php echo ('invoice' == $status->google_print_template) ? 'selected' : '' ?> ><?php _e('Invoice', 'woocommerce_status_actions') ?></option><!--

                        <option value="packing" <?php echo ('packing' == $status->google_print_template) ? 'selected' : '' ?>><?php _e('Packing', 'woocommerce_status_actions') ?></option>
-->
                    </select>
                    <span class="woocommerce-help-tip"
                          data-tip="<?php _e('Select which template to print the order details on.', 'woocommerce_status_actions'); ?>"</span>
                    </php>
                </p>
            <?php } ?>
            <h2 class="status_label_header"><?php _e('Automation', 'woocommerce_status_actions'); ?>
            </h2>
            <p class="status_label_description description">
                <?php _e('Automate your workflow and trigger another order status to the order once this order status has been assigned.', 'woocommerce_status_actions'); ?>
            </p>
            <p class="form-field">
                <label for="status_automatic_trigger"><?php _e('Automatic Trigger', 'woocommerce_status_actions'); ?></label>
                <input type="checkbox" name="automatic_trigger"
                       id="status_automatic_trigger" <?php checked('yes', $status->automatic_trigger, true); ?>
                       value="yes">
                <span class="description"><?php _e('Triggers another status after a period of time when this status is set.', 'woocommerce_status_actions'); ?></span>
            </p>
            <p class="form-field show_if_automatic_trigger">
                <label for="status_triggered_status"><?php _e('Triggered Status', 'woocommerce_status_actions'); ?><span
                            class="required">*</span></label>
                <select name="triggered_status" id="status_triggered_status" class="wc-enhanced-select"
                        data-placeholder="<?php esc_attr_e('Select Status', 'woocommerce_status_actions'); ?>"
                        data-allow_clear="true" style="min-width: 200px; width: 50%;">
                    <option value=""></option>
                    <?php
                    foreach ($order_statuses as $status_key => $status_name) {
                        if ($status_key == 'wc-' . $status->slug) {
                            continue;
                        }
                        ?>
                        <option value="<?php echo $status_key; ?>" <?php selected($status_key, $status->triggered_status, true); ?>><?php echo $status_name; ?></option>
                        <?php
                    }
                    ?>
                </select>
                <span class="woocommerce-help-tip"
                      data-tip="<?php _e('Select which status should be triggered after the period of time.', 'woocommerce_status_actions'); ?>"</span>
            </p>
            <p class="form-field show_if_automatic_trigger">
                <label for="status_time_period"><?php _e('Time Period', 'woocommerce_status_actions'); ?><span
                            class="required">*</span></label>
                <input type="number" name="time_period" id="status_time_period"
                       value="<?php echo $status->time_period; ?>" style="width: 14%; ">
                <select name="time_period_type" id="time_period_type" style="width: 35%; margin-left: .5em;">
                    <?php
                    $pediods = array(
                        'minutes' => __('Minutes', 'woocommerce_status_actions'),
                        'hours' => __('Hours', 'woocommerce_status_actions'),
                        'days' => __('Days', 'woocommerce_status_actions'),
                        'weeks' => __('Weeks', 'woocommerce_status_actions'),
                        'months' => __('Months', 'woocommerce_status_actions'),
                        'years' => __('Years', 'woocommerce_status_actions'),
                    );
                    foreach ($pediods as $pediod_key => $pediod_name) {
                        ?>
                        <option value="<?php echo $pediod_key; ?>" <?php selected($pediod_key, $status->time_period_type, true); ?>><?php echo $pediod_name; ?></option>
                        <?php
                    }
                    ?>
                </select>
                <span class="woocommerce-help-tip"
                      data-tip="<?php _e('Select the time period after which the triggered status is set.', 'woocommerce_status_actions'); ?>"</span>
            </p>
            <p class="form-field">
                <label for="update_order_date"><?php _e('Order Date', 'woocommerce_status_actions'); ?></label>
                <input type="checkbox" name="update_order_date"
                       id="status_update_order_date" <?php checked('yes', $status->update_order_date, true); ?>
                       value="yes">
                <span class="description"><?php _e('Update order date when this status is applied.', 'woocommerce_status_actions'); ?></span>
            </p>

        </div>
    </div>


</div>