<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Uploader 
{
	function uploadImage($fileName, $maxSize, $maxW, $fullPath, $relPath, $colorR, $colorG, $colorB, $maxH = null){
		$folder = $relPath;
		//echo $folder;die();
		$maxlimit = $maxSize;
		$allowed_ext = "jpg,jpeg,gif,png,bmp,doc,docx,xls,xlsx,ppt,pptx,vsd,pdf,vcf";
		$match = "";
		$errorList=array();
		$filesize = $_FILES[$fileName]['size'];
		if($filesize > 0){	
			$filename = strtolower($_FILES[$fileName]['name']);
			$filename = preg_replace('/\s/', '_', $filename);
		   	if($filesize < 1){ 
				$errorList[] = "File size is empty.";
			}
			if($filesize > $maxlimit){ 
				$errorList[] = "File size is too big.";
			}
			if(count($errorList)<1){
				$file_ext = preg_split("/\./",$filename);
				$allowed_ext = preg_split("/\,/",$allowed_ext);
				foreach($allowed_ext as $ext){
					if($ext==end($file_ext)){
						$match = "1"; // File is allowed
						$NUM = time();
						$front_name = substr($file_ext[0], 0, 15);
						$newfilename = $front_name."_".$NUM.".".end($file_ext);
						$filetype = end($file_ext);
						$save = $folder.$newfilename;
						if(!file_exists($save)){
							if(getimagesize($_FILES[$fileName]['tmp_name'])){
								list($width_orig, $height_orig) = getimagesize($_FILES[$fileName]['tmp_name']);
								if($maxH == null){
									if($width_orig < $maxW){
										$fwidth = $width_orig;
									}else{
										$fwidth = $maxW;
									}
									$ratio_orig = $width_orig/$height_orig;
									$fheight = $fwidth/$ratio_orig;
									
									$blank_height = $fheight;
									$top_offset = 0;
										
								}else{
									if($width_orig <= $maxW && $height_orig <= $maxH){
										$fheight = $height_orig;
										$fwidth = $width_orig;
									}else{
										if($width_orig > $maxW){
											$ratio = ($width_orig / $maxW);
											$fwidth = $maxW;
											$fheight = ($height_orig / $ratio);
											if($fheight > $maxH){
												$ratio = ($fheight / $maxH);
												$fheight = $maxH;
												$fwidth = ($fwidth / $ratio);
											}
										}
										if($height_orig > $maxH){
											$ratio = ($height_orig / $maxH);
											$fheight = $maxH;
											$fwidth = ($width_orig / $ratio);
											if($fwidth > $maxW){
												$ratio = ($fwidth / $maxW);
												$fwidth = $maxW;
												$fheight = ($fheight / $ratio);
											}
										}
									}
									if($fheight == 0 || $fwidth == 0 || $height_orig == 0 || $width_orig == 0){
										die("FATAL ERROR REPORT ERROR CODE [add-pic-line-67-orig]");
									}
									if($fheight < 45){
										$blank_height = 45;
										$top_offset = round(($blank_height - $fheight)/2);
									}else{
										$blank_height = $fheight;
									}
								}
								
								switch($filetype){
									case "gif":
										$image_p = imagecreatetruecolor($fwidth, $blank_height);
										$image = @imagecreatefromgif($_FILES[$fileName]['tmp_name']);
										imagepalettecopy($image, $image_p);
										imagefill($image_p, 0, 0, $transparent_index);
										imagecolortransparent($image_p, $transparent_index);
										imagetruecolortopalette($image_p, true, 256);
									break;
									case "jpg":
										$image_p = imagecreatetruecolor($fwidth, $blank_height);
										//$white = imagecolorallocate($image_p, $colorR, $colorG, $colorB);
										//imagefill($image_p, 0, 0, $white);
										$image = @imagecreatefromjpeg($_FILES[$fileName]['tmp_name']);
										imagepalettecopy($image, $image_p);
										imagefill($image_p, 0, 0, $transparent_index);
										imagecolortransparent($image_p, $transparent_index);
										imagetruecolortopalette($image_p, true, 256);
									break;
									case "jpeg":
										$image_p = imagecreatetruecolor($fwidth, $blank_height);
										//$white = imagecolorallocate($image_p, $colorR, $colorG, $colorB);
										//imagefill($image_p, 0, 0, $white);
										$image = @imagecreatefromjpeg($_FILES[$fileName]['tmp_name']);
										imagepalettecopy($image, $image_p);
										imagefill($image_p, 0, 0, $transparent_index);
										imagecolortransparent($image_p, $transparent_index);
										imagetruecolortopalette($image_p, true, 256);
										
									break;
									case "png":
										$image = @imagecreatefrompng($_FILES[$fileName]['tmp_name']);
										$image_p = imagecreatetruecolor($fwidth, $blank_height);
										$transparent_index = imagecolortransparent($image);
										imagealphablending($image_p, false);
										$transparent_index = imagecolorallocatealpha($image, 0, 0, 0, 127);
										imagefill($image_p, 0, 0, $transparent_index);
										imagesavealpha($image_p, true);
										
									break;
								}
								@imagecopyresampled($image_p, $image, 0, $top_offset, 0, 0, $fwidth, $fheight, $width_orig, $height_orig);
								switch($filetype){
									case "gif":
										if(!@imagegif($image_p, $save)){
											$errorList[]= "PERMISSION DENIED [GIF]";
										}
									break;
									case "jpg":
										if(!@imagejpeg($image_p, $save, 100)){
											$errorList[]= "PERMISSION DENIED [JPG]";
										}
									break;
									case "jpeg":
										if(!@imagejpeg($image_p, $save, 100)){
											$errorList[]= "PERMISSION DENIED [JPEG]";
										}
									break;
									case "png":
										if(!@imagepng($image_p, $save, 0)){
											$errorList[]= "PERMISSION DENIED [PNG]";
										}
									break;
								}
								@imagedestroy($filename);
								$type="image";
							}
							else{
								$result=move_uploaded_file($_FILES[$fileName]['tmp_name'],$save);
								$type="doc";
							}
						}else{
							$errorList[]= "CANNOT MAKE IMAGE IT ALREADY EXISTS";
						}	
					}
				}		
			}
		}else{
			$errorList[]= "NO FILE SELECTED";
		}
		if(!$match){
		   	$errorList[]= "File type isn't allowed: $filename";
		}
		if(sizeof($errorList) == 0){
			return $fullPath.$newfilename."#".$newfilename."#".$type;
		}else{
			$eMessage = array();
			for ($x=0; $x<sizeof($errorList); $x++){
				$eMessage[] = $errorList[$x];
			}
		   	return $eMessage;
		}
	}
}

?>