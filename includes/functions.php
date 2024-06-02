<?php
include_once "config.php";

function debug($var, $stop = false) {
    echo "<pre>";
    print_r($var);
    echo "</pre>";
    if ($stop) die;
}

function get_page_title($title = '') {
    if (empty($title)) {
        return SITE_NAME;
    } else {
        return SITE_NAME . " / $title";
    }    
}


function get_url($page = '') {
    return HOST . "/$page";
}

function db() {
    try {
        return new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS, 
            [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    } catch (PDOException $e) {
        die($e->getMessage());
    }

}

function db_query($sql, $exec = false) {
    if (empty($sql)) return false;

    if ($exec) return db()->exec($sql);

    return db()->query($sql);
}

function get_posts($user_id = 0) {
    if ($user_id > 0) {
        return db_query("SELECT posts.*, users.name, users.login, users.avatar FROM `posts` JOIN `users` ON users.id = posts.user_id WHERE posts.user_id = $user_id;")->fetchAll();
    }
    else {
    return db_query("SELECT posts.*, users.name, users.login, users.avatar FROM `posts` JOIN `users` ON users.id = posts.user_id;")
    ->fetchAll();
    }
}

function get_user_info($login) {
    return  db_query("SELECT * FROM `users` WHERE `login` = '$login';")->fetch();
}

function user_add($login, $pass) {
    $name = ucfirst($login);
    $password = password_hash($pass, PASSWORD_DEFAULT);
    return  db_query("INSERT INTO `users` (`id`, `login`, `pass`, `name`) VALUES (NULL, '$login', '$password', '$name');", true);
}

function user_register($auth_data) {
    $login = trim($auth_data['login']);
    if (empty($auth_data) || !isset($login) || empty($login) || !isset($auth_data['pass']) || empty($auth_data['pass']) || !isset($auth_data['pass2']) || empty($auth_data['pass2'])) return false;
    $user = get_user_info($login);
    if (!empty($user)) {
        $_SESSION['error'] = 'Пользователь ' . $login . ' уже существует';
        header("Location: " . get_url('register.php'));
        die;
    }

    if ($auth_data['pass'] !== $auth_data['pass2']) {
        $_SESSION['error'] = 'Пароли не совпадают';
        header("Location: " . get_url('register.php'));
        die;
    }

    if (user_add($login, $auth_data['pass'])) {
        header("Location: " . get_url('user_posts.php?id=' . get_user_info($login)['id']));
        die;
    }
}

function user_login($auth_data) {
    $login = trim($auth_data['login']);
    if (empty($auth_data) || !isset($login) || empty($login) || !isset($auth_data['pass']) || empty($auth_data['pass'])) return false;
    $user = get_user_info($login);
    if (empty($user)) {
        $_SESSION['error'] = 'Пользователь ' . $auth_data['login'] . ' не найден.';
        header("Location: " . get_url());
        die;        
    }
    if (password_verify($auth_data['pass'], $user['pass'])) {
        $_SESSION['user'] = $user;
        $_SESSION['error'] = '';
        header("Location: " . get_url('user_posts.php?id=' . $user['id']));
    } else {
        $_SESSION['error'] = 'Пользователь неверный.';
        header("Location: " . get_url());
        die;            
    }
    debug($user, true);    
}

function get_error_message() {
    $error = '';
    if (isset($_SESSION['error']) && !empty($_SESSION['error'])) {
        $error = $_SESSION['error'];
        $_SESSION['error'] = '';
    }
    return $error;
}



?>