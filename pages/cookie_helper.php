<?php
    // cookie_helper.php - Cookie utilities
    function set_user_cookie($user_id) {
        setcookie('user_id', $user_id, time() + 86400 * 30, '/'); // 30 days
    }
    function clear_user_cookie() {
        setcookie('user_id', '', time() - 3600, '/');
    }
?>
