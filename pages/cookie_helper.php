<?php
    function set_user_cookie($user_id) {
        setcookie('user_id', $user_id, time() + 86400 * 30, '/');
    }
    function clear_user_cookie() {
        setcookie('user_id', '', time() - 3600, '/');
    }
?>
