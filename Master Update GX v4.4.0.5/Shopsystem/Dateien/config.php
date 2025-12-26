<?php

return [
    //*************************
    //Permissions configuration
    //******************
    'delete_files'                            => false,
    'create_folders'                          => false,
    'delete_folders'                          => false,
    'upload_files'                            => false,
    'rename_files'                            => false,
    'rename_folders'                          => false,
    'duplicate_files'                         => false,
    'copy_cut_files'                          => false, // for copy/cut files
    'copy_cut_dirs'                           => false, // for copy/cut directories
    'chmod_files'                             => false, // change file permissions
    'chmod_dirs'                              => false, // change folder permissions
    'preview_text_files'                      => false, // eg.: txt, log etc.
    'edit_text_files'                         => false, // eg.: txt, log etc.
    'create_text_files'                       => false, // only create files with exts. defined in $editable_text_file_exts
    
    //**********************
    // Hidden files and folders
    //**********************
    // set the names of any folders you want hidden (eg "hidden_folder1", "hidden_folder2" ) Remember all folders with these names will be hidden (you can set any exceptions in config.php files on folders)
    'hidden_folders' => [
        'actindo',
        'admin',
        'analytics',
        'cache',
        'callback',
        'debug',
        'ext',
        'gambio_installer',
        'gambio_updater',
        'gm',
        'GambioAdmin',
        'GambioApi',
        'GambioCore',
        'GambioShop',
        'GProtector',
        'GXEngine',
        'GXMainComponents',
        'GXModules',
        'GXUserComponents',
        'localhost',
        'inc',
        'includes',
        'JSEngine',
        'lang',
        'lettr',
        'logfiles',
        'PdfCreator',
        'pub',
        'public',
        'ResponsiveFilemanager',
        'shopgate',
        'styleedit',
        'StyleEdit3',
        'system',
        'templates',
        'themes',
        'vendor',
        'version_info',
    ],
    
    // set the names of any files you want hidden. Remember these names will be hidden in all folders (eg "this_document.pdf", "that_image.jpg" )
    'hidden_files' => [
        'config.php',
        'fax.html',
        'GPL-LICENSE.txt',
        'GPL-LIZENZUEBERSETZUNG.txt',
        'PayPal-SDK-LICENSE.txt',
        'robots.txt',
    ],
];
