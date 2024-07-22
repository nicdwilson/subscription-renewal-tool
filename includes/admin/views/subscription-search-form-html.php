<?php


?>

<div class="wrap">
    <h1><?php esc_html_e( 'Subscription Renewal Tool', 'subscription-renewal-tool' ); ?></h1>
    <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
        <input type="hidden" name="action" value="get_subscription_from_email">
        <input type="email" name="email" placeholder="Enter email address">
        <input type="submit" value="Get Subscription">
    </form>
</div>