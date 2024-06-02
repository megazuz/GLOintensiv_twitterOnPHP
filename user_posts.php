<?php

include_once "includes/functions.php";
$error = get_error_message();

if (isset($_SESSION['user']['id']) && !empty($_SESSION['user']['id'])) {
    $id = $_SESSION['user']['id'];
} else if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $id = 0;
}

if ($id == 0) {
    $title = 'Твиты';
    $posts = get_posts();
} else {
    $posts = get_posts($id);
    $title = 'Твиты пользователя';
    if (!empty($posts)) $title = 'Твиты пользователя @' . $posts[0]['login'];
}

include_once "includes/header.php"; 

include_once "includes/tweet_form.php";
include_once "includes/posts.php"; 

include_once "includes/footer.php"; 
?>