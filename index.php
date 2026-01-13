<?php

    // Include the DirectoryLister class
    require_once('_resources/DirectoryLister.php');

    // Initialize the DirectoryLister object
    $lister = new DirectoryLister();

    // Restrict access to current directory
    ini_set('open_basedir', getcwd());

    // Return file hash
    if (isset($_GET['hash'])) {

        // Return JSON-encoded file hash information
        header('Content-Type: application/json; charset=utf-8');
        $hashParam = isset($_GET['hash']) ? rawurldecode($_GET['hash']) : '';
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

        // Initialize the directory array
        if (isset($_GET['dir'])) {
            $dirParam = rawurldecode($_GET['dir']);
            // Basic sanitization for directory parameter
            if (strpos($dirParam, "\0") !== false || strpos($dirParam, '..') !== false || strpos($dirParam, '/') === 0 || strpos($dirParam, '<') !== false || strpos($dirParam, '>') !== false) {
                $dirArray = $lister->listDirectory('.');
            } else {
                $dirArray = $lister->listDirectory($dirParam);
            }
        } else {
            $dirArray = $lister->listDirectory('.');
        }

        // Define theme path
        if (!defined('THEMEPATH')) {
            define('THEMEPATH', $lister->getThemePath());
        }

        // Set path to theme index
        $themeIndex = $lister->getThemePath(true) . '/index.php';

        // Initialize the theme
        if (file_exists($themeIndex)) {
            include($themeIndex);
        } else {
            die('ERROR: Failed to initialize theme');
        }

    }
