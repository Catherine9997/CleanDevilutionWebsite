<!DOCTYPE html>
<html lang="en">
<head>
<style type="text/css" media="screen">
    #editor {
        position: absolute;
        top: 0px;
        right: 100px;
        bottom: 100px;
        left: 0;
    }
  .equal {
    color: #5cf442
  }
</style>
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.3/ace.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" src="http://cemerick.github.io/jsdifflib/diffview.js"></script>
<link rel="stylesheet" type="text/css" href="http://cemerick.github.io/jsdifflib/diffview.css"/>
<script type="text/javascript" src="http://cemerick.github.io/jsdifflib/difflib.js"></script>
<script>
<?php
exec('update.bat');
function bigNumber(): int
{
    $output = mt_rand(1, 9);
    for ($i = 0; $i < 30; $i++) {
        $output .= mt_rand(0, 9);
    }

    return $output;
}
$bignum = bigNumber();
exec('copyrepo.bat ' . $bignum);
?>
$.ajaxSetup({ cache: false });
var repoNum = <?php echo json_encode($bignum); ?>;
var fileChosen = false;
var chosenFileName = "";
function searchList(){
    var search = $("input[name='searchFile']").val();
    $('.sourceFile').each(function () {
        var val = $(this).text();
		if($(this).text().includes($("input[name='searchFile']").val())){
			$(this).css("display","block")
		} else{
			$(this).css("display","none")
		}
    });
}

function prepareStuff(){
var width = $("input[name='searchFile']").css( "width" );
$("#sourceList").css("width",width);
width = width.replace(/[^-\d\.]/g, '')
var increasedWidth = (parseInt(width)+50)+"px";
var increasedWidth2 = (parseInt(width)+55)+"px";
//$("#editor").css("width","100%");
//$("#editor").css("height","100%");


//$("#editor").css("position","absolute");
$("#editor").css("left",increasedWidth);
//$("#editor").css("top",100);
//$("#editor").css("bottom",100);
//$("#editor").css("right",100);
//$('#editor').css('width', '100%').css('width', '-='+increasedWidth2);
$('#editor').css('height', '80%')
$('#editor').css('width', '60%')
window.editor.resize();


$("#diffoutput").css("position","absolute");
$('#diffoutput').css('width', '39%').css('width', '-='+increasedWidth2);
$('#diffoutput').css('left', '60%').css('left', '+='+increasedWidth2);
//$("#diffoutput").css("top","60%");

$('#compileButton').css("top",$('#editor').css("height"));
$('#compileButton').css("left",$('#editor').css("left"));

$('#dataStatus').css("top",$('#editor').css("height"));
$('#dataStatus').css("left",$('#editor').css("left")).css('left', '+='+$('#compileButton').css("width"));

$("input[name='functionName']").css("position","absolute");
$("input[name='functionName']").css("left",$('#editor').css("left"));
$("input[name='functionName']").css("top",$('#editor').css("height")).css('top', '+='+$('#compileButton').css("height"));

$('#compileLog').css("top",$('#editor').css("height")).css('top', '+='+$('#compileButton').css("height")).css('top', '+='+$("input[name='functionName']").css("height")).css('top', '+='+"10px");
$('#compileLog').css("left",$('#editor').css("left"));
$('#compileLog').css("width",$('#editor').css("width"));
}

function listClicked(e){
	fileChosen = true;
	$('.sourceFile').each(function () {
		$(this).css("color","#5cf442");
		$(this).css("font-weight", "normal")
    });
	e.style.color =  "orange";
	e.style.fontWeight = "bold";
	console.log(e);
	console.log(e.textContent);
	chosenFileName=e.textContent;
	jQuery.get('http://grzyby.ddns.net/dev/devilution_'+repoNum+'/Source/'+e.textContent, function(data) {
	//data= data.replace(/(?:\r\n|\r|\n)/g, '<br>');
    //$("#editor").text(data);
	window.editor.setValue(data);
});
}


function diffUsingJS(v1,v2) {

	"use strict";
	var byId = function (id) { return document.getElementById(id); },
		base = difflib.stringAsLines(v1),
		newtxt = difflib.stringAsLines(v2),
		sm = new difflib.SequenceMatcher(base, newtxt),
		opcodes = sm.get_opcodes(),
		diffoutputdiv = byId("diffoutput"),
		contextSize = null;

	diffoutputdiv.innerHTML = "";
	contextSize = contextSize || null;

	diffoutputdiv.appendChild(diffview.buildView({
		baseTextLines: base,
		newTextLines: newtxt,
		opcodes: opcodes,
		baseTextName: "Original ASM",
		newTextName: "Devilution ASM",
		contextSize: contextSize,
		viewType: 0
	}));
}
function buttonClicked(){
	$('#dataStatus').text("Waiting for data...");
	$('#dataStatus').css('color', 'yellow');
	if(!fileChosen){
		$('#dataStatus').text("Error - no file chosen");
		$('#dataStatus').css('color', 'red');
		return;}
	//if(isset($_POST["repo"]) and ctype_digit($_POST["repo"]) and isset($_POST["fileName"]) and isset($_POST["fileContent"]) isset($_POST["functionName"])){

	var fName = $("input[name='functionName']").val();
	$.post( "http://grzyby.ddns.net/dev/updateFile.php", { repo: repoNum, fileName: chosenFileName, fileContent: window.editor.getValue(), functionName: fName}).done(function( repoData ) {
		$('#dataStatus').text("Done");
		$("#compileLog").text(repoData);
		$('#dataStatus').css('color', '#5cf442');
		jQuery.get('http://grzyby.ddns.net/dev/devilution_'+repoNum+'/orig.asm?nocachepls='+$.now(), function(data) {
			jQuery.get('http://grzyby.ddns.net/dev/devilution_'+repoNum+'/compare.asm?nocachepls='+$.now(), function(data2) {
				diffUsingJS(data,data2);
				$("#diffoutput").css("height","90%");
			});
		});
  });


}
</script>
</head>
<title>Clean the code like a god</title>
<body bgcolor="#000000">
<body onload="prepareStuff();">
<input type="text" name="searchFile" placeholder="file name" onkeyup="searchList()"><br>
<div id="sourceList" style="position:absolute;height:95%;width:250px;border:1px solid #ccc;font:16px/26px Georgia, Garamond, Serif;overflow:auto;color:#5cf442">
<?php

if ($handle = opendir('devilution_' . $bignum . '/Source/')) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry !== '.' && $entry !== '..') {
            if (mb_strpos($entry, '.cpp') !== false or mb_strpos($entry, '.h') !== false) {
                echo "<div class='sourceFile' onmousedown='listClicked(this)'>" . htmlspecialchars($entry) . '</div>';
            }
        }
    }

    closedir($handle);
}
//exec("/B test.bat");
//$("input[name='searchFile']").val()
//$(".sourceFile").css("display","none")
//<textarea id="editFile" spellcheck="false" placeholder="I can has code? (Choose a file from the list)"></textarea>
?>
</div>



<div id="editor">function foo(items) {
    var x = "All this is syntax highlighted";
    return x;
}</div>
<script>
var editor = ace.edit("editor");
editor.setTheme("ace/theme/monokai");
editor.session.setMode("ace/mode/c_cpp");
editor.setOptions({
        autoScrollEditorIntoView: true
    });
    editor.renderer.setScrollMargin(10, 10, 10, 10);
window.editor = editor;
</script>
<button id="compileButton" onClick="buttonClicked()" style="position:absolute;left:150px"> Compile and view diff</button>
<div id="dataStatus" style="position:absolute"></div>
<div id="diffoutput" style="overflow-y: scroll;"></div>
<input type="text" name="functionName" placeholder="function name"><br>
<div id="compileLog" style="position:absolute;color:#5cf442"></div>
