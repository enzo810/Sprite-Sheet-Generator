<?php
    if($argc > 2 && is_dir($argv[1])) {
        if(in_array("-r", $argv) or in_array("--recursive", $argv)){
            $dir = $argv[1];
            $list = [];
            scanDirectoryRecursive($dir, $list);
        }
        else {
            $dir = $argv[1];
            $list = [];
            scanDirectory($dir, $list);
        }
        if (in_array("-i", $argv) or in_array("--output-image=", $argv)){
            if(in_array("-i", $argv)){
               $key = array_search('-i', $argv);
               CreateSprite($list, $argv[$key+1] . ".png");
            }
            else {
               $key = array_search('--output-image=', $argv);
               CreateSprite($list, $argv[$key+1] . ".png");
            }
        }
        else {
        CreateSprite($list);
        }
        if (in_array("-s", $argv) or in_array("--output-style=", $argv)){
            if(in_array("-s", $argv)){
                $key = array_search('-s', $argv);
                rename("style.css",$argv[$key+1] . ".css");
            }
            else {
                $key = array_search('--output-style=', $argv);
                rename("style.css",$argv[$key+1] . ".css");
            }
        }
    }

    if ($argc == 2 && is_dir($argv[1])) {
        $dir = $argv[1];
        $list = [];
        scanDirectory($dir, $list);
        CreateSprite($list);
    }

    if ($argc < 2) {
        echo "Un dossier doit etre passé en paramètre\n";
    }
//----------------------------------------------------------------------------
    function scanDirectoryRecursive($dir, &$files = []){
        if ($dir_open  = opendir($dir)) {
            while (($filename = readdir($dir_open)) !== false) {
                $extension = new SplFileInfo($filename);
                if ( $filename !== "." && $filename !=="..") {
                    $path_dir = $dir . "/" . $filename;
                    if(is_dir($path_dir)){
                        scanDirectoryRecursive($path_dir, $files);
                    } else {
                        if ($extension-> getExtension() == "png" && is_file($path_dir)){
                            $files[] = $path_dir;
                        }
                    }
                }
            }
            closedir($dir_open);
        }
    }
//------------------------------------------------------------------------------
    function scanDirectory($dir, &$files = []){
        if ($dir_open  = opendir($dir)) {
            while (($filename = readdir($dir_open)) !== false) {
                $extension = new SplFileInfo($filename);
                if ( $filename !== "." && $filename !=="..") {
                    $path_dir = $dir . "/" . $filename;
                    if ($extension-> getExtension() == "png" && is_file($path_dir)){
                        $files[] = $path_dir;
                    }
                }
            }
            closedir($dir_open);
        }
    }
//------------------------------------------------------------------------------
    function CreateSprite($arrPng, $spriteName = "sprite.png"){
        foreach ($arrPng as $cle => $valeur) {
            $img = $arrPng[$cle];
            list($width, $height) = getimagesize($img);
            $allWidths[] = $width;
            $allHeights[] = $height;
        }
        $destWidth = array_sum($allWidths);
        $maxHeight = max($allHeights);
        $dest = imagecreatetruecolor($destWidth, $maxHeight);
        imagesavealpha($dest, true);
        $color = imagecolorallocatealpha($dest, 0, 0, 0, 127);
        imagefill($dest, 0, 0, $color);
        //////////////////////////////////////////////////////////////////////
        $i = 0;
        foreach ($arrPng as $key => $value) {
            $img = $arrPng[$key];
            list($width, $height) = getimagesize($img);
            $src = imagecreatefrompng($img);
            imagecopy($dest, $src,$i ,0 ,0 ,0 , $width, $height);
            imagedestroy($src);
            $i = $i + $width;
        }
        imagepng($dest, $spriteName);
        ///////////////////////////////////////////////////////////////////////
        $cssFile = fopen("style.css", "w+");
        $i = 0;
        $class = 1;
        foreach($arrPng as $key => $value){
            $img = $arrPng[$key];
            list($width, $height) = getimagesize($img);
            $cssContent = "";
            $cssContent = ".png$class " . "{\n    background-image: url($spriteName);\n    width : $width"."px;\n    height : $height" ."px;\n    background-position: -$i". "px 0px;\n}\n\n";
            $class = $class + 1;
            fwrite($cssFile, $cssContent);
            $i = $i + $width;
        }
        imagedestroy($dest);
    }
?> 