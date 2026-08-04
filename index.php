<?php

    // Include the Classy class
    require_once('_classy/Classy.php');

    // Initialize the Classy object
    $lister = new Classy();

    // Restrict access to current directory
    ini_set('open_basedir', getcwd());

    // Same headers as the .htaccess, set here too so PHP-served responses
    // are covered even on hosts without mod_headers (e.g. nginx)
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header("Content-Security-Policy: object-src 'none'; frame-ancestors 'self'");

    // Return file hash
    if (isset($_GET['hash'])) {

        // Return JSON-encoded file hash information
        header('Content-Type: application/json; charset=utf-8');
        $hashParam = isset($_GET['hash']) ? $_GET['hash'] : '';
        // Basic sanitization: disallow absolute paths, null bytes, and parent traversal
        if (strpos($hashParam, "\0") !== false || strpos($hashParam, '..') !== false || strpos($hashParam, '/') === 0 || strpos($hashParam, '<') !== false || strpos($hashParam, '>') !== false) {
            http_response_code(400);
            echo json_encode(array('md5' => null, 'sha1' => null, 'size' => null));
            exit;
        }

        $hashes = $lister->getFileHash($hashParam);
        echo json_encode($hashes);
        exit;

    }

    if (isset($_GET['zip'])) {

        $dirArray = $lister->zipDirectory($_GET['zip']);

    } else {

        // Page number for the listing, defaults to 1 on anything invalid
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) {
            $page = 1;
        }

        // Initialize the directory array
        if (isset($_GET['dir'])) {
            $dirParam = $_GET['dir'];
            // Basic sanitization for directory parameter
            if (strpos($dirParam, "\0") !== false || strpos($dirParam, '..') !== false || strpos($dirParam, '/') === 0 || strpos($dirParam, '<') !== false || strpos($dirParam, '>') !== false) {
                $dirArray = $lister->listDirectory('.', $page);
            } else {
                $dirArray = $lister->listDirectory($dirParam, $page);
            }
        } else {
            $dirArray = $lister->listDirectory('.', $page);
        }

        // Define theme path
        if (!defined('THEMEPATH')) {
            define('THEMEPATH', $lister->getThemePath());
        }

        // Set path to theme layout template
        $themeIndex = $lister->getThemePath(true) . '/layout.php';

        // Initialize the theme
        if (file_exists($themeIndex)) {
            include($themeIndex);
        } else {
            die('ERROR: Failed to initialize theme');
        }

    }
