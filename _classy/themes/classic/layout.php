<!DOCTYPE html>

<html>

    <head>

        <title>Directory listing of <?php echo htmlspecialchars($lister->getListedPath(), ENT_QUOTES, 'UTF-8'); ?></title>
        <link rel="shortcut icon" href="<?php echo THEMEPATH; ?>/img/folder.png">

        <!-- Set the theme before first paint so switching pages/reloading doesn't flash the wrong colors -->
        <script>
            (function() {
                var stored = localStorage.getItem('classy-theme');
                if (stored === 'dark' || stored === 'light') {
                    document.documentElement.setAttribute('data-theme', stored);
                }
            })();
        </script>

        <!-- STYLES (self-hosted: no CDN dependency, nothing to SRI-pin) -->
        <link rel="stylesheet" href="<?php echo THEMEPATH; ?>/vendor/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo THEMEPATH; ?>/vendor/bootstrap-icons/bootstrap-icons.css">
        <link rel="stylesheet" href="<?php echo THEMEPATH; ?>/vendor/roboto/roboto.css">
        <link rel="stylesheet" type="text/css" href="<?php echo THEMEPATH; ?>/css/classy.css">

        <!-- SCRIPTS -->
        <script type="text/javascript" src="<?php echo THEMEPATH; ?>/vendor/jquery/jquery.min.js"></script>
        <script src="<?php echo THEMEPATH; ?>/vendor/bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?php echo THEMEPATH; ?>/js/classy.js"></script>

        <!-- META -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="utf-8">

        <?php file_exists('analytics.inc') ? include('analytics.inc') : false; ?>

    </head>

    <body>

        <div id="page-navbar" class="navbar navbar-default navbar-fixed-top">
            <div class="container">

                <?php $breadcrumbs = $lister->listBreadcrumbs(); ?>

                <p class="navbar-text">
                    <?php foreach($breadcrumbs as $breadcrumb): ?>
                        <?php if ($breadcrumb != end($breadcrumbs)): ?>
                                <a href="<?php echo htmlspecialchars($breadcrumb['link'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($breadcrumb['text'], ENT_QUOTES, 'UTF-8'); ?></a>
                                <span class="divider">/</span>
                        <?php else: ?>
                            <?php echo htmlspecialchars($breadcrumb['text'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </p>

                <ul class="nav navbar-nav navbar-right">

                    <li id="page-top-nav">
                        <a href="javascript:void(0)" id="page-top-link">
                            <i class="bi bi-arrow-up-circle bi-lg"></i>
                        </a>
                    </li>

                    <li>
                        <a href="javascript:void(0)" id="theme-toggle" title="Toggle dark mode">
                            <i class="bi bi-moon bi-lg"></i>
                        </a>
                    </li>

                    <?php  if ($lister->isZipEnabled()): ?>
                        <li id="page-top-download-all">
                            <a href="?zip=<?php echo $lister->getDirectoryPath(); ?>" id="download-all-link">
                                <i class="bi bi-download bi-lg"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                </ul>

            </div>
        </div>

        <div id="page-content" class="container">

            <?php file_exists('header.php') ? include('header.php') : include($lister->getThemePath(true) . "/header.php"); ?>

            <?php if($lister->getSystemMessages()): ?>
                <?php foreach ($lister->getSystemMessages() as $message): ?>
                    <div class="alert alert-<?php echo $message['type']; ?>">
                        <?php echo $message['text']; ?>
                        <a class="close" data-dismiss="alert" href="#">&times;</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div id="directory-list-header">
                <div class="row">
                    <div class="col-md-7 col-sm-6 col-xs-10">File</div>
                    <div class="col-md-2 col-sm-2 col-xs-2 text-right">Size</div>
                    <div class="col-md-3 col-sm-4 hidden-xs text-right">Last Modified</div>
                </div>
            </div>

            <ul id="directory-listing" class="nav nav-pills nav-stacked">

                <?php foreach($dirArray as $name => $fileInfo): ?>
                    <li data-name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" data-href="<?php echo htmlspecialchars($fileInfo['url_path'], ENT_QUOTES, 'UTF-8'); ?>">
                        <a href="<?php echo htmlspecialchars($fileInfo['url_path'], ENT_QUOTES, 'UTF-8'); ?>" class="clearfix" data-name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">


                            <div class="row">
                                <span class="file-name col-md-7 col-sm-6 col-xs-9">
                                    <i class="bi <?php echo htmlspecialchars($fileInfo['icon_class'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                                    <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>
                                </span>

                                <span class="file-size col-md-2 col-sm-2 col-xs-3 text-right">
                                    <?php echo htmlspecialchars($fileInfo['file_size'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>

                                <span class="file-modified col-md-3 col-sm-4 hidden-xs text-right">
                                    <?php echo htmlspecialchars($fileInfo['mod_time'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </div>

                        </a>

                        <?php if (is_file($fileInfo['file_path'])): ?>

                                <a href="javascript:void(0)" class="file-info-button">
                                <i class="bi bi-info-circle"></i>
                            </a>

                        <?php else: ?>

                            <?php if ($lister->containsIndex($fileInfo['file_path'])): ?>

                                <a href="<?php echo htmlspecialchars($fileInfo['file_path'], ENT_QUOTES, 'UTF-8'); ?>" class="web-link-button" <?php if($lister->externalLinksNewWindow()): ?>target="_blank"<?php endif; ?>>
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>

                            <?php endif; ?>

                        <?php endif; ?>

                    </li>
                <?php endforeach; ?>

            </ul>
        </div>

        <?php file_exists('footer.php') ? include('footer.php') : include($lister->getThemePath(true) . "/footer.php"); ?>

        <div id="file-info-modal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">{{modal_header}}</h4>
                    </div>

                    <div class="modal-body">

                        <table id="file-info" class="table table-bordered">
                            <tbody>

                                <tr>
                                    <td class="table-title">MD5</td>
                                    <td class="md5-hash">{{md5_sum}}</td>
                                </tr>

                                <tr>
                                    <td class="table-title">SHA1</td>
                                    <td class="sha1-hash">{{sha1_sum}}</td>
                                </tr>

                                <tr>
                                    <td class="table-title">Size</td>
                                    <td class="filesize">{{size}}</td>
                                </tr>

                            </tbody>
                        </table>

                    </div>

                </div>
            </div>
        </div>

    </body>

</html>
