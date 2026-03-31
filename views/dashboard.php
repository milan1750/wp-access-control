<h2>Dashboard</h2>
<p>Welcome, <?php echo wp_get_current_user()->user_login; ?>!</p>
<a href="<?php echo esc_url(home_url('/wpac-platform/logout')); ?>">Logout</a>
