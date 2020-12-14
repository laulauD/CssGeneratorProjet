<?php

$folder = './asset/';

//Tableau multidimensionel
$pngsource = array();

$pngnum = 0;
$cxmax = 0;
$cymax = 0;


//ajoute les images dans le tableau $pngsouce
function addpng($path) {
    global $pngsource, $pngnum;
    list($cx, $cy) = getimagesize($path);
    imagecreatefrompng($path);
    // on crée un tableau $pngsource, pngnum s'incremente a chaque valeur récuperé avec le tableau multidimentionnel
    $pngsource[$pngnum] = array("cx"=>$cx,"cy"=>$cy,"path"=>$path);
    $pngnum = $pngnum+1;
}


//utilise addpng et my_scandir pour chaque images (récursive)
function recupPath($pathname) {
    $files = my_scanDir($pathname);
    foreach($files as $key => $value){
        $path = $value;
        if(!is_dir($path)) {
          addpng($path);
        } elseif($value != "." && $value != "..") {
            if($mode_recursif = true) {
                recupPath($path);
            }
        }
    }
}


//cherche les images .png et les ajoutent dans le tableau, puis retourne le tableau créer
function my_scanDir($path){
  $dir = $path.'*.png';
  $files = glob($dir,GLOB_BRACE);
  $res = array();
  foreach($files as $image) {
     $f = str_replace($path,'',$image);
     array_push($res,$path.$f);
  }
  return $res;
}


// trouve la taille finale de mon sprite
function buildNewSprite(){
    global $pngsource, $cxmax, $cymax;
    $fixedSize = false;
    $cxfixe = 200;
    $cyfixe = 200;
    $spriteLarge = false;
    //pour chaque img du tableau, on recupere sa taille (width, height), on les compare avec la taille stocké du sprite, si l'image est plus grande on additionne sa width et sa height avec la w et h du sprite. ex : $cxmax = $cxmax + $cx
    foreach ($pngsource as $png) {
        $cx = $fixedSize == true ? $cxfixe : $png["cx"];
        $cy = $fixedSize == true ? $cyfixe : $png["cy"];

        if ($spriteLarge == false) {
           $cxmax = $cx > $cxmax ? $cx : $cxmax;
           $cymax = $cy + $cymax;
         } else {
           $cymax = $png["cy"] > $cymax ? $png["cy"] : $cymax;
           $cxmax = $png["cx"] + $cxmax;
         }
    }
}


//creer le fichier .css
function createCss($cssfile, $filename){
    global $pngsource, $cptimg, $spriteLarge;

    $positionx = 0;
    $positiony = 0;

    $fp = fopen("$cssfile", "w+");
    fwrite($fp, ".sprite {
    background-image: url($filename);
    background-repeat: no-repeat;
    display: block;
}\n");

    foreach($pngsource as $key => $value) {

        $width = $value["cx"];
        $height = $value["cy"];

        fwrite($fp, "#img$cptimg {
    width: " . $width . "px;
    height: " . $height . "px;
    background-position: " . $positionx . "px ".$positiony."px;
}");

        $cptimg += 1;

        if ($spriteLarge == true) {
            $positionx += $width + 1;
        } else {
            $positiony += $height + 1;
        }
    }
    fclose($fp);
}

//Creer le sprite final grace aux infos des fonctions et l'enregistre
function sprite($name){
  global $pngsource, $cxmax, $cymax;
  $background = imagecreatetruecolor($cxmax, $cymax);
  $pos = 0;

  foreach($pngsource as $i){
    $pngtmp = imagecreatefrompng($i['path']);
    imagecopy($background, $pngtmp, 0, $pos, 0, 0, $i['cy'], $i['cx']);
    $pos += $i['cx'];
    imagedestroy($pngtmp);
  }
  imagepng($background, $name);
}


              //////// PROGRAMME CSS GENERATOR ////////


//D'abord, je recupère le dossier "asset" ainsi que toutes ses images .png,
//ensuite, je les met dans un tableau multidimensionel
recupPath($folder);
//Je recupere la longueur et la largeur finale du sprite
buildNewSprite();
//Je build un sprite en .png avec le tableau
sprite("sprite.png");
//Et enfin, je creer le fichier .css
createCss("myStyle.css", "sprite.png");
?>
