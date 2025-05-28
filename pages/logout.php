<?php
    session_start();

    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
    );


    session_destroy();
    setcookie("login", "", time() - 3600, "/"); // Hapus cookie login

    header("Location: login.php");
    exit;
?>