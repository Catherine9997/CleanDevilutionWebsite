<?php
// returns true if $needle is a substring of $haystack
function contains($needle, $haystack)
{
    return strpos($haystack, $needle) !== false;
}

if(isset($_POST["repo"]) and ctype_digit($_POST["repo"]) and isset($_POST["fileName"]) and isset($_POST["fileContent"]) and isset($_POST["functionName"])){
	$myfile = fopen("devilution_".$_POST["repo"]."/Source/".$_POST["fileName"], "w") or die("Unable to open file!");
	fwrite($myfile, $_POST["fileContent"]);
	fclose($myfile);	
	$out = "";
	$mvse = array();
	$hideaddr = "";
	if(isset($_POST["hideaddr"])){
		$hideaddr = "--no-mem-disp";
	}
	$out = exec("compare.bat ".$_POST["repo"]." ".$_POST["functionName"]. " ".$hideaddr, $mvse, $out);
	$skipped = 0;
	foreach( $mvse as $line ) {
		if($line != ""){
			if(contains($_POST["fileName"],$line) or contains("Found ".$_POST["functionName"],$line) or contains(" error ",$line) or contains("Could not find the specified symbol in the config",$line)){
				if($skipped < 2){
					$skipped++;
				} else{
					echo json_encode(htmlspecialchars( $line ));
				}
			}
		}
	}
	//echo json_encode($out);
}
?>