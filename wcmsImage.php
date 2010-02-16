<?php
	/*
	 *
	 * RandomSnippets by Jaan Janesmae
	 *
	 * Part of: WCMS - Simple PHP WebCMS
	 *   class: wcmsImages	
	 *
	 * Author: Jaan Janesmae <jaan@naojo.se>
	 * Copyright (c) 2009-2010 Jaan Janesmae
	 * 
	 * License: MIT License
	 *
	 * Permission is hereby granted, free of charge, to any person
	 * obtaining a copy of this software and associated documentation
	 * files (the "Software"), to deal in the Software without
	 * restriction, including without limitation the rights to use,
	 * copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the
	 * Software is furnished to do so, subject to the following
	 * conditions:
	 *
	 * The above copyright notice and this permission notice shall be
	 * included in all copies or substantial portions of the Software.
	 *
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
	 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
	 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
	 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
	 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
	 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
	 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
	 * OTHER DEALINGS IN THE SOFTWARE.
	 *
	 */

	/*
	 * USAGE:
	 *
	 * wcmsImage.php?img=filename.jpg&X=400&Y=300
	 * 
	 * used variables:
	 *    img => image filename
	 *    X   => Image maximum width
	 *    Y   => Image maximum height
	 *
	 * The original image's and resized cache file's folders can be set.
	 * The Cache file's folder must have writing permmissions.
	 *
	 */

class wcmsImages{
	function wcmsImageSize($f) {
		$s = getimagesize($f);
		return $s;
	}

	function wcmsImageThumbSize($f,$w,$h) {
		$s = wcmsImages::wcmsImageSize($f);
		$nw = ( $w < $s[0]) ? $w : $s[0];	// check if new width smaller
		$nh = ( $h < $s[1]) ? $h : $s[1];

		$xw = $s[0]/$nw;	// we need the biggest shrinkage
		$xh = $s[1]/$nh;

		// now we can limit the thumbnail size!
		$x = ($xw < $xh) ? $xh : $xw;
		
		// we have shrinkage!
		$r[0] = round($s[0]/$x);
		$r[1] = round($s[1]/$x);

		return $r;
	}

	function wcmsResizeImage($f,$nw, $nh) {
		global $image_dir, $image_thumbs_dir;
		if(!file_exists($image_dir."/".$f)) return false;

		// Size matters

		$s = wcmsImages::wcmsImageSize($image_dir."/".$f);
		$w = $s[0];
		$h = $s[1];
		$type = $s[2];

		$n = wcmsImages::wcmsImageThumbSize($image_dir."/".$f, $nw, $nh);
		// we have shrinkage!
		$thumb_w = $n[0];
		$thumb_h = $n[1];
		
		$thumb = imagecreatetruecolor($thumb_w, $thumb_h);

		if ($type == 2) {
			$src = imagecreatefromjpeg($image_dir."/".$f);
		} elseif ($type == 3) {
			$src = imagecreatefrompng($image_dir."/".$f);
		} else {
			return false;
		}

		$white = imagecolorallocate($thumb,255,255,255);
		imagefill($thumb,0,0,$white);
		if (function_exists("imagecopyresampled")) {
			imagecopyresampled($thumb, $src, 0,0,0,0, $thumb_w, $thumb_h, $w, $h);
		} else {
			imagecopyresized($thumb, $src, 0,0,0,0, $thumb_w, $thumb_h, $w, $h);
		}

		$t = $image_thumbs_dir."/tn_".$thumb_w."_".$thumb_h."_".$f;

		imagedestroy($src);
		imagejpeg($thumb, $t, 100);
		imagedestroy($thumb);
		@chmod($t, 0777);
		return true;
	}
}

/* Settings */

$image_dir = "./images";
$image_thumbs_dir = $image_dir."/thumbs";

/* Settings end */

$f = (isset($_GET["img"]) && preg_match('/^[a-z][.-\w]*$/i', $_GET["img"])) ? $_GET["img"] : NULL;
$x = (isset($_GET["X"]) && preg_match('/^[0-9]*$/i', $_GET["X"])) ? $_GET["X"] : 400;
$y = (isset($_GET["Y"]) && preg_match('/^[0-9]*$/i', $_GET["Y"])) ? $_GET["Y"] : 300;

if($f != NULL) {
	$mf = $image_dir."/".$f;
	$s = wcmsImages::wcmsImageThumbSize($mf,$x,$y);

	$tf = $image_thumbs_dir."/tn_".$s[0]."_".$s[1]."_".$f;

	if (!file_exists($tf) && file_exists($mf)) {
		wcmsImages::wcmsResizeImage($f,$s[0],$s[1]);
	}
	if (file_exists($mf) && file_exists($tf)) {
		header('Content-Type: image/jpeg');
		if (file_exists($tf)) readfile($tf);
	} else {
		echo "Something may have gone wrong!";
	}
} else {
	echo "Something may have gone wrong!";
}

?>