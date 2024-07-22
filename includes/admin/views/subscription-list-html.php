<?php
/**
 * Contains the HTML for the subscription list tablle items
 * Used once per subscription...
 */

?>



	<tbody id="the-list" data-wp-lists="list:order">

		<tr id="order-<?php echo $subscription->get_id(); ?>" class="order-<?php echo $subscription->get_id(); ?> type-shop_subscription status-active">

            <td class="status column-status" data-colname="Status">
                <mark class="subscription-status order-status status-<?php echo $subscription->get_status(); ?> <?php echo $subscription->get_status(); ?> tips">
                    <span><?php echo $subscription->get_status(); ?></span>
                </mark>
            </td>

            <td class="order_title column-order_title has-row-actions column-primary" data-colname="Subscription">
                <div class="tips">
                    <a href="<?php echo admin_url('admin.php?page=wc-orders--shop_subscription&amp;action=edit&amp;id=' . $subscription->get_id() ); ?>">
                        #<strong><?php echo $subscription->get_id(); ?></strong>
                    </a> for <a href="<?php echo admin_url('user-edit.php?user_id=' . $subscription->get_customer_id() ); ?>">
                        <?php echo $subscription->get_user()->first_name; ?> <?php echo $subscription->get_user()->last_name; ?>
                    </a>
                </div>
            </td>

            <td class="order_items column-order_items" data-colname="Items">

                <?php foreach( $subscription->get_items() as $item_id => $item ): ?>

                    <div class="order-item">
                        <a href="<?php echo admin_url('post.php?post=' . $item->get_product_id() . '&amp;action=edit'); ?>">
                            <?php echo $item->get_name(); ?>
                        </a>
                    </div>
                    
                <?php endforeach; ?>

            </td>

            <td class="recurring_total column-recurring_total" data-colname="Total">
                $<?php echo $subscription->get_total(); ?> / <?php echo $subscription->get_billing_period(); ?>
                <small class="meta">
                    <?php echo $subscription->get_payment_method_to_display(); ?>
                </small>
            </td>

            <td class="start_date column-start_date" data-colname="Start Date">
                <time class="start_date" title="<?php echo $subscription->get_date( 'start' ); ?>">
                    <?php echo $subscription->get_date_to_display( 'start' ); ?>
                </time>
            </td>

            <td class="end_date column-end_date" data-colname="End Date">
                <?php echo $subscription->get_date_to_display( 'end' ); ?>
            </td>

            <td class="next_payment_date column-next_payment_date" data-colname="Next Payment">
                <time class="next_payment_date" title=" <?php echo $subscription->get_date_to_display( 'next_payment' ); ?>">
                    <?php echo $subscription->get_date_to_display( 'next_payment' ); ?>
                </time>
            </td>

            <td class="last_payment_date column-last_payment_date" data-colname="Last Order Date">
                <?php echo $subscription->get_date_to_display( 'last_order_date_paid' ); ?>
            </td>

            <td class="orders column-orders" data-colname="Orders">
                <a href="<?php echo admin_url('admin.php?page=wc-orders&amp;status=all&amp;_subscription_related_orders=' . $subscription->get_id() ); ?>">
                    <?php echo count( $subscription->get_related_orders() ); ?>
                </a>
            </td>

    </tr>
    <tr>

        <td colspan="5" class="order-notes">
            <div class="order-notes-inner">
                <div class="order-note">
                    <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                        <?php echo wp_nonce_field( 'renew-subscription' ); ?>
                        <input type="hidden" name="action" value="process_renewal_tool_subscription_renewal">
                        <input type="hidden" name="subscription_id" value="<?php echo $subscription->get_id(); ?>">
                        Set custom renewal date&nbsp;<input type="date" name="next_renewal" />
                        &nbsp;
                        <button type="submit" class="button save_order button-primary" name="sunscription_renewal" value="Run renewal">Run renewal</button>
                    </form>
                </div>
            </div>
        </td>

        <td colspan="4" class="order-notes">
            <div class="order-notes-inner">
                <div class="order-note">
                    <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                        <?php echo wp_nonce_field( 'renew-subscription' ); ?>
                        <input type="hidden" name="action" value="process_renewal_tool_subscription_renewal">
                        <input type="hidden" name="subscription_id" value="<?php echo $subscription->get_id(); ?>">
                        <input type="hidden" name="next_renewal" value="year">
                        <button type="submit" class="button save_order button-primary" name="sunscription_renewal" value="Renew for one year">Renew for one year</button>
                    </form>
                </div>
            </div>
        </td>

    </tr>
</tbody>
