<?php

class DirectoryLister {

    // Define application version
    const VERSION = '2.6.1';

    // Reserve some variables
    protected $_themeName     = null;
    protected $_directory     = null;
    protected $_appDir        = null;
    protected $_appURL        = null;
    protected $_config        = null;
    protected $_fileTypes     = null;
    protected $_systemMessage = null;
    protected $_hiddenFilesMerged = false;


    /**
     * DirectoryLister construct function. Runs on object creation.
     */
    public function __construct() {

        // Set class directory constant
        if(!defined('__DIR__')) {
            define('__DIR__', dirname(__FILE__));
        }

        // Set application directory
        $this->_appDir = __DIR__;

        // Build the application URL
        $this->_appURL = $this->_getAppUrl();

        // Load the configuration file
        $configFile = $this->_appDir . '/config.php';

        // Set the config array to a global variable
        if (file_exists($configFile)) {
            $this->_config = require_once($configFile);
        } else {
            die('ERROR: Missing application config file at ' . $configFile);
        }

        // Set the file types array to a global variable
        $this->_fileTypes = require_once($this->_appDir . '/fileTypes.php');

        // Set the theme name
        $this->_themeName = $this->_config['theme_name'];

        // Merge dotfile hidden patterns once if enabled
        if (!empty($this->_config['hide_dot_files']) && !$this->_hiddenFilesMerged) {
            $this->_config['hidden_files'] = array_merge(
                $this->_config['hidden_files'],
                array('.*', '*/.*')
            );
            $this->_hiddenFilesMerged = true;
        }

    }

     /**
     * If it is allowed to zip whole directories
     *
     * @param string $directory Relative path of directory to list
     * @return true or false
     * @access public
     */
    public function isZipEnabled() {
        foreach ($this->_config['zip_disable'] as $disabledPath) {
            if (fnmatch($disabledPath, $this->_directory)) {
                return false;
            }
        }
        return $this->_config['zip_dirs'];
    }

     /**
     * Creates zipfile of directory
     *
     * @param string $directory Relative path of directory to list
     * @access public
     */
    public function zipDirectory($directory) {
        if (!$this->_config['zip_dirs']) {
            return;
        }

        // Cleanup directory path
        $directory = $this->setDirectoryPath($directory);

        if ($directory != '.' && $this->_isHidden($directory)) {
            echo "Access denied.";
            return;
        }

        $filename_no_ext = basename($directory);

        if ($directory == '.') {
            $filename_no_ext = 'Home';
        }

        // Temporary zip file
        $tmp_zip = tempnam(sys_get_temp_dir(), 'classyzip') . '.zip';

        $zipCreated = false;
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($tmp_zip, ZipArchive::CREATE) === true) {
                $zipCreated = true;
            }
        }

        // If ZipArchive is not available/fails, we'll try a safe shell fallback if possible
        $useShellFallback = false;
        if (!$zipCreated) {
            // Check for zip binary and that exec/popen exist
            if ((is_executable('/usr/bin/zip') || is_executable('/bin/zip')) && (function_exists('exec') || function_exists('popen'))) {
                $useShellFallback = true;
            } else {
                $this->setSystemMessage('danger', '<b>ERROR:</b> Zip archive is not available on this system');
                return;
            }
        }

        // Build exclude patterns (from config hidden files + index.php)
        $exclude_patterns = array_merge($this->_config['hidden_files'], array('index.php'));

        // Normalize exclude patterns
        $exclude_patterns = array_map('trim', $exclude_patterns);

        // Recursively add files
        $baseDir = $directory;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            $filePath = $file->getPathname();
            // Build relative path
            $relativePath = ltrim(str_replace($baseDir, '', $filePath), DIRECTORY_SEPARATOR);
            // Normalize to forward slashes for matching
            $relativeForMatch = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            // Skip excluded patterns
            $skip = false;
            foreach ($exclude_patterns as $pattern) {
                if ($pattern === '') continue;
                if (fnmatch($pattern, $relativeForMatch) || fnmatch($pattern, $file->getFilename())) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) continue;

            // Add file to zip under its relative path
            if ($zipCreated) {
                $zip->addFile($filePath, $relativePath);
            } else {
                // Collect files for shell fallback
                $shellFiles[] = array('full' => $filePath, 'rel' => $relativePath);
            }
        }

        if ($zipCreated) {
            $zip->close();

            // Deliver the zip file
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($filename_no_ext) . '.zip"');
            header('Content-Length: ' . filesize($tmp_zip));

            readfile($tmp_zip);
            unlink($tmp_zip);
            return;
        }

        // Shell fallback: create a temporary directory structure and call zip binary safely
        if ($useShellFallback) {
            // Build include list and run zip with -j to store relative paths
            $cwd = getcwd();
            // We'll run zip from the baseDir, pass relative paths
            $escapedTmp = escapeshellarg($tmp_zip);
            $zipBin = is_executable('/usr/bin/zip') ? '/usr/bin/zip' : '/bin/zip';

            // Build file list argument safely
            $relList = array();
            foreach ($shellFiles as $f) {
                $relList[] = escapeshellarg($f['rel']);
            }

            // Change to base dir and run zip with safe args
            $cmd = 'cd ' . escapeshellarg($baseDir) . ' && ' . escapeshellarg($zipBin) . ' -' . (int)$this->_config['zip_compression_level'] . ' -r ' . $escapedTmp . ' --quiet ' . implode(' ', $relList);

            if (function_exists('exec')) {
                exec($cmd, $out, $ret);
            } else {
                $proc = popen($cmd, 'r');
                if ($proc) {
                    pclose($proc);
                }
            }

            if (!file_exists($tmp_zip)) {
                $this->setSystemMessage('danger', '<b>ERROR:</b> Failed to create zip archive');
                return;
            }

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($filename_no_ext) . '.zip"');
            header('Content-Length: ' . filesize($tmp_zip));
            readfile($tmp_zip);
            unlink($tmp_zip);
            return;
        }

    }


    /**
     * Creates the directory listing and returns the formatted XHTML
     *
     * @param string $directory Relative path of directory to list
     * @return array Array of directory being listed
     * @access public
     */
    public function listDirectory($directory) {

        // Set directory
        $directory = $this->setDirectoryPath($directory);

        // Set directory variable if left blank
        if ($directory === null) {
            $directory = $this->_directory;
        }

        // Get the directory array
        $directoryArray = $this->_readDirectory($directory);

        // Return the array
        return $directoryArray;
    }


    /**
     * Parses and returns an array of breadcrumbs
     *
     * @param string $directory Path to be breadcrumbified
     * @return array Array of breadcrumbs
     * @access public
     */
    public function listBreadcrumbs($directory = null) {

        // Set directory variable if left blank
        if ($directory === null) {
            $directory = $this->_directory;
        }

        // Explode the path into an array
        $dirArray = explode('/', $directory);

        // Statically set the Home breadcrumb
        $breadcrumbsArray[] = array(
            'link' => $this->_appURL,
            'text' => 'Home'
        );

        // Generate breadcrumbs
        foreach ($dirArray as $key => $dir) {

            if ($dir != '.') {

                $dirPath  = null;

                // Build the directory path
                for ($i = 0; $i <= $key; $i++) {
                    $dirPath = $dirPath . $dirArray[$i] . '/';
                }

                // Remove trailing slash
                if(substr($dirPath, -1) == '/') {
                    $dirPath = substr($dirPath, 0, -1);
                }

                // Combine the base path and dir path
                $link = $this->_appURL . '?dir=' . rawurlencode($dirPath);

                $breadcrumbsArray[] = array(
                    'link' => $link,
                    'text' => $dir
                );

            }

        }

        // Return the breadcrumb array
        return $breadcrumbsArray;
    }


    /**
     * Determines if a directory contains an index file
     *
     * @param string $dirPath Path to directory to be checked for an index
     * @return boolean Returns true if directory contains a valid index file, false if not
     * @access public
     */
    public function containsIndex($dirPath) {

        // Check if directory contains an index file
        foreach ($this->_config['index_files'] as $indexFile) {

            if (file_exists($dirPath . '/' . $indexFile)) {

                return true;

            }

        }

        return false;

    }


    /**
     * Get path of the listed directory
     *
     * @return string Path of the listed directory
     * @access public
     */
    public function getListedPath() {

        // Build the path
        if ($this->_directory == '.') {
            $path = $this->_appURL;
        } else {
            $path = $this->_appURL . $this->_directory;
        }

        // Return the path
        return $path;
    }


    /**
     * Returns the theme name.
     *
     * @return string Theme name
     * @access public
     */
    public function getThemeName() {
        // Return the theme name
        return $this->_config['theme_name'];
    }


    /**
     * Returns open links in another window
     *
     * @return boolean Returns true if in config is enabled open links in another window, false if not
     * @access public
     */
    public function externalLinksNewWindow() {
        return $this->_config['external_links_new_window'];
    }


    /**
     * Returns the path to the chosen theme directory
     *
     * @param bool $absolute Whether or not the path returned is absolute (default = false).
     * @return string Path to theme
     * @access public
     */
    public function getThemePath($absolute = false) {
        if ($absolute) {
            // Set the theme path
            $themePath = $this->_appDir . '/themes/' . $this->_themeName;
        } else {
            // Get relative path to application dir
            $realtivePath = $this->_getRelativePath(getcwd(), $this->_appDir);

            // Set the theme path
            $themePath = $realtivePath . '/themes/' . $this->_themeName;
        }

        return $themePath;
    }


    /**
     * Get an array of error messages or false when empty
     *
     * @return array|bool Array of error messages or false
     * @access public
     */
    public function getSystemMessages() {
        if (isset($this->_systemMessage) && is_array($this->_systemMessage)) {
            return $this->_systemMessage;
        } else {
            return false;
        }
    }


    /**
     * Returns string of file size in human-readable format
     *
     * @param  string $filePath Path to file
     * @return string Human-readable file size
     * @access public
     */
    function getFileSize($filePath) {

        // Get file size
        $bytes = filesize($filePath);

        // Array of file size suffixes
        $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');

        // Calculate file size suffix factor
        $factor = floor((strlen($bytes) - 1) / 3);

        // Calculate the file size
        $fileSize = sprintf('%.2f', $bytes / pow(1024, $factor)) . $sizes[$factor];

        return $fileSize;

    }


    /**
     * Returns array of file hash values
     *
     * @param  string $filePath Path to file
     * @return array Array of file hashes
     * @access public
     */
    public function getFileHash($filePath) {

        $hashArray = array(
            'md5' => null,
            'sha1' => null,
            'size' => null
        );

        // Resolve real path
        $real = realpath($filePath);

        if ($real === false || !is_file($real)) {
            return $hashArray;
        }

        // Prevent access to hidden files
        if ($this->_isHidden($filePath)) {
            return $hashArray;
        }

        // Ensure resolved path is inside the current working directory
        $cwd = getcwd();
        if (strpos($real, $cwd) !== 0) {
            return $hashArray;
        }

        $size = filesize($real);
        $hashArray['size'] = $size === false ? null : $size;

        // Prevent hashing if file is too big
        if ($size !== false && $size > $this->_config['hash_size_limit']) {
            $hashArray['md5']  = '[ File size exceeds threshold ]';
            $hashArray['sha1'] = '[ File size exceeds threshold ]';
            return $hashArray;
        }

        // Generate file hashes
        $hashArray['md5']  = hash_file('md5', $real);
        $hashArray['sha1'] = hash_file('sha1', $real);

        return $hashArray;

    }


    /**
     * Set directory path variable
     *
     * @param string $path Path to directory
     * @return string Sanitizd path to directory
     * @access public
     */
    public function setDirectoryPath($path = null) {

        // Set the directory global variable
        $this->_directory = $this->_setDirectoryPath($path);

        return $this->_directory;

    }

    /**
     * Get directory path variable
     *
     * @return string Sanitizd path to directory
     * @access public
     */
    public function getDirectoryPath() {
        return $this->_directory;
    }


    /**
     * Add a message to the system message array
     *
     * @param string $type The type of message (ie - error, success, notice, etc.)
     * @param string $message The message to be displayed to the user
     * @return bool true on success
     * @access public
     */
    public function setSystemMessage($type, $text) {

        // Create empty message array if it doesn't already exist
        if (isset($this->_systemMessage) && !is_array($this->_systemMessage)) {
            $this->_systemMessage = array();
        }

        // Set the error message
        $this->_systemMessage[] = array(
            'type'  => $type,
            'text'  => $text
        );

        return true;
    }


    /**
     * Validates and returns the directory path
     *
     * @param string $dir Directory path
     * @return string Directory path to be listed
     * @access protected
     */
    protected function _setDirectoryPath($dir) {

        // Check for an empty variable
        if (empty($dir) || $dir == '.') {
            return '.';
        }

        // Eliminate double slashes
        while (strpos($dir, '//')) {
            $dir = str_replace('//', '/', $dir);
        }

        // Remove trailing slash if present
        if(substr($dir, -1, 1) == '/') {
            $dir = substr($dir, 0, -1);
        }

        // Verify file path exists and is a directory
        if (!file_exists($dir) || !is_dir($dir)) {
            // Set the error message
            $this->setSystemMessage('danger', '<b>ERROR:</b> File path does not exist');

            // Return the web root
            return '.';
        }

        // Prevent access to hidden files
        if ($this->_isHidden($dir)) {
            // Set the error message
            $this->setSystemMessage('danger', '<b>ERROR:</b> Access denied');

            // Set the directory to web root
            return '.';
        }

        // Prevent access to parent folders
        if (strpos($dir, '<') !== false || strpos($dir, '>') !== false
        || strpos($dir, '..') !== false || strpos($dir, '/') === 0) {
            // Set the error message
            $this->setSystemMessage('danger', '<b>ERROR:</b> An invalid path string was detected');

            // Set the directory to web root
            return '.';
        } else {
            // Should stop all URL wrappers (Thanks to Hexatex)
            $directoryPath = $dir;
        }

        // Return
        return $directoryPath;
    }


    /**
     * Loop through directory and return array with file info, including
     * file path, size, modification time, icon and sort order.
     *
     * @param string $directory Directory path
     * @param string $sort Sort method (default = natcase)
     * @return array Array of the directory contents
     * @access protected
     */
    protected function _readDirectory($directory, $sort = 'natcase') {

        // Initialize array
        $directoryArray = array();

        // Get directory contents
        $files = scandir($directory);

        // Read files/folders from the directory
        foreach ($files as $file) {

            if ($file != '.') {

                // Get files relative path
                $relativePath = $directory . '/' . $file;

                if (substr($relativePath, 0, 2) == './') {
                    $relativePath = substr($relativePath, 2);
                }

                // Don't check parent dir if we're in the root dir
                if ($this->_directory == '.' && $file == '..'){

                    continue;

                } else {

                    // Get files absolute path
                    $realPath = realpath($relativePath);

                    // Determine file type by extension
                    if (is_dir($realPath)) {
                        $iconClass = 'fa-folder';
                        $sort = 1;
                    } else {
                        // Get file extension
                        $fileExt = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));

                        if (isset($this->_fileTypes[$fileExt])) {
                            $iconClass = $this->_fileTypes[$fileExt];
                        } else {
                            $iconClass = $this->_fileTypes['blank'];
                        }

                        $sort = 2;
                    }

                }

                if ($file == '..') {

                    if ($this->_directory != '.') {
                        // Get parent directory path
                        $pathArray = explode('/', $relativePath);
                        unset($pathArray[count($pathArray)-1]);
                        unset($pathArray[count($pathArray)-1]);
                        $directoryPath = implode('/', $pathArray);

                        if (!empty($directoryPath)) {
                            $directoryPath = '?dir=' . rawurlencode($directoryPath);
                        }

                        // Add file info to the array
                        $directoryArray['..'] = array(
                            'file_path'  => $this->_appURL . $directoryPath,
                            'url_path'   => $this->_appURL . $directoryPath,
                            'file_size'  => '-',
                            'mod_time'   => date('Y-m-d H:i:s', filemtime($realPath)),
                            'icon_class' => 'fa-level-up',
                            'sort'       => 0
                        );
                    }

                } elseif (!$this->_isHidden($relativePath)) {

                    // Add all non-hidden files to the array
                    if ($this->_directory != '.' || $file != 'index.php') {

                        // Build the file path
                        $urlPath = implode('/', array_map('rawurlencode', explode('/', $relativePath)));

                        if (is_dir($relativePath)) {
                            $urlPath = '?dir=' . $urlPath;
                        } else {
                            $urlPath = $urlPath;
                        }

                        // Add the info to the main array
                        $directoryArray[pathinfo($relativePath, PATHINFO_BASENAME)] = array(
                            'file_path'  => $relativePath,
                            'url_path'   => $urlPath,
                            'file_size'  => is_dir($realPath) ? '-' : $this->getFileSize($realPath),
                            'mod_time'   => date('Y-m-d H:i:s', filemtime($realPath)),
                            'icon_class' => $iconClass,
                            'sort'       => $sort
                        );
                    }

                }
            }

        }

        // Sort the array
        $reverseSort = in_array($this->_directory, $this->_config['reverse_sort']);
        $sortedArray = $this->_arraySort($directoryArray, $this->_config['list_sort_order'], $reverseSort);

        // Return the array
        return $sortedArray;

    }


    /**
     * Sorts an array by the provided sort method.
     *
     * @param array $array Array to be sorted
     * @param string $sortMethod Sorting method (acceptable inputs: natsort, natcasesort, etc.)
     * @param boolen $reverse Reverse the sorted array order if true (default = false)
     * @return array
     * @access protected
     */
    protected function _arraySort($array, $sortMethod, $reverse = false) {
        // Create empty arrays
        $sortedArray = array();
        $finalArray  = array();

        // Create new array of just the keys and sort it
        $keys = array_keys($array);

        switch ($sortMethod) {
            case 'asort':
                asort($keys);
                break;
            case 'arsort':
                arsort($keys);
                break;
            case 'ksort':
                ksort($keys);
                break;
            case 'krsort':
                krsort($keys);
                break;
            case 'natcasesort':
                natcasesort($keys);
                break;
            case 'natsort':
                natsort($keys);
                break;
            case 'shuffle':
                shuffle($keys);
                break;
        }

        // Loop through the sorted values and move over the data
        if ($this->_config['list_folders_first']) {

            foreach ($keys as $key) {
                if ($array[$key]['sort'] == 0) {
                    $sortedArray['0'][$key] = $array[$key];
                }
            }

            foreach ($keys as $key) {
                if ($array[$key]['sort'] == 1) {
                    $sortedArray[1][$key] = $array[$key];
                }
            }

            foreach ($keys as $key) {
                if ($array[$key]['sort'] == 2) {
                    $sortedArray[2][$key] = $array[$key];
                }
            }

            if ($reverse) {
                $sortedArray[1] = array_reverse($sortedArray[1]);
                $sortedArray[2] = array_reverse($sortedArray[2]);
            }

        } else {

            foreach ($keys as $key) {
                if ($array[$key]['sort'] == 0) {
                    $sortedArray[0][$key] = $array[$key];
                }
            }

            foreach ($keys as $key) {
                if ($array[$key]['sort'] > 0) {
                    $sortedArray[1][$key] = $array[$key];
                }
            }

            if ($reverse) {
                $sortedArray[1] = array_reverse($sortedArray[1]);
            }

        }

        // Merge the arrays
        foreach ($sortedArray as $array) {
            if (empty($array)) continue;
            foreach ($array as $key => $value) {
                $finalArray[$key] = $value;
            }
        }

        // Return sorted array
        return $finalArray;

    }


    /**
     * Determines if a file is specified as hidden
     *
     * @param string $filePath Path to file to be checked if hidden
     * @return boolean Returns true if file is in hidden array, false if not
     * @access protected
     */
    protected function _isHidden($filePath) {

        // Normalize variants to test against patterns: original, without leading ./ or /, basename, and realpath
        $variants = array();
        $variants[] = $filePath;
        $variants[] = ltrim($filePath, './');
        $variants[] = ltrim($filePath, '/');
        $variants[] = basename($filePath);

        $real = realpath($filePath);
        if ($real !== false) {
            $variants[] = $real;
            // also relative to cwd
            $cwd = getcwd();
            if (strpos($real, $cwd) === 0) {
                $variants[] = ltrim(substr($real, strlen($cwd)), DIRECTORY_SEPARATOR);
            }
        }

        // Compare path variants to all hidden file paths
        foreach ($this->_config['hidden_files'] as $hiddenPath) {
            if ($hiddenPath === '') continue;
            foreach ($variants as $v) {
                if ($v === '') continue;
                if (fnmatch($hiddenPath, $v)) {
                    return true;
                }
            }
        }

        return false;

    }


    /**
     * Builds the root application URL from server variables.
     *
     * @return string The application URL
     * @access protected
     */
    protected function _getAppUrl() {

        // Get the server protocol
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }

        // Get the server hostname
        $host = $_SERVER['HTTP_HOST'];

        // Get the URL path
        $pathParts = pathinfo($_SERVER['PHP_SELF']);
        $path      = $pathParts['dirname'];

        // Remove backslash from path (Windows fix)
        if (substr($path, -1) == '\\') {
            $path = substr($path, 0, -1);
        }

        // Ensure the path ends with a forward slash
        if (substr($path, -1) != '/') {
            $path = $path . '/';
        }

        // Build the application URL
        $appUrl = $protocol . $host . $path;

        // Return the URL
        return $appUrl;
    }


    /**
      * Compares two paths and returns the relative path from one to the other
     *
     * @param string $fromPath Starting path
     * @param string $toPath Ending path
     * @return string $relativePath Relative path from $fromPath to $toPath
     * @access protected
     */
    protected function _getRelativePath($fromPath, $toPath) {

        // Define the OS specific directory separator
        if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

        // Remove double slashes from path strings
        $fromPath = str_replace(DS . DS, DS, $fromPath);
        $toPath = str_replace(DS . DS, DS, $toPath);

        // Explode working dir and cache dir into arrays
        $fromPathArray = explode(DS, $fromPath);
        $toPathArray = explode(DS, $toPath);

        // Remove last fromPath array element if it's empty
        $x = count($fromPathArray) - 1;

        if(!trim($fromPathArray[$x])) {
            array_pop($fromPathArray);
        }

        // Remove last toPath array element if it's empty
        $x = count($toPathArray) - 1;

        if(!trim($toPathArray[$x])) {
            array_pop($toPathArray);
        }

        // Get largest array count
        $arrayMax = max(count($fromPathArray), count($toPathArray));

        // Set some default variables
        $diffArray = array();
        $samePath = true;
        $key = 1;

        // Generate array of the path differences
        while ($key <= $arrayMax) {

            // Get to path variable
            $toPath = isset($toPathArray[$key]) ? $toPathArray[$key] : null;

            // Get from path variable
            $fromPath = isset($fromPathArray[$key]) ? $fromPathArray[$key] : null;

            if ($toPath !== $fromPath || $samePath !== true) {

                // Prepend '..' for every level up that must be traversed
                if (isset($fromPathArray[$key])) {
                    array_unshift($diffArray, '..');
                }

                // Append directory name for every directory that must be traversed
                if (isset($toPathArray[$key])) {
                    $diffArray[] = $toPathArray[$key];
                }

                // Directory paths have diverged
                $samePath = false;
            }

            // Increment key
            $key++;
        }

        // Set the relative thumbnail directory path
        $relativePath = implode('/', $diffArray);

        // Return the relative path
        return $relativePath;

    }

}
