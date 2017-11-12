<?php

@include ( '../stlcalc.php' );

$allowedExts	= array ( 'stl' );
$value			= explode ( '.', $_FILES['file']['name'] );
$extension		= strtolower ( end ( $value ) );

if( in_array ( $extension, $allowedExts ) ) {
	if ( $_FILES['file']['error'] > 0 ) {
		echo 'Error: ' . $_FILES['file']['error'] . '<br><br>';
	} else {
		echo '<b>STLCalc Example</b><br><br>';
		$filename = $_FILES['file']['tmp_name'];
		$obj = new STLCalc ( $filename );
		echo '<br><br><b>Basic Usage</b><br>';
		$unit = 'cm';
		$vol = $obj->GetVolume ( $unit );
		echo 'Volume: ' . $vol . ' cubic ' . $unit . '<br>';
		$weight = $obj->GetWeight();
		echo 'Weight: ' . $weight . ' gm<br>';
		$den = $obj->GetDensity();
		echo 'Density: ' . $den . ' gm/cc' . '<br>';
		$tcount = $obj->GetTrianglesCount();
		echo 'Triangles Count: ' . $tcount . ' triangles read<br>';
		echo '<br><b>Units -> inch</b><br>';
		$unit = 'inch';
		$vol = $obj->GetVolume ( $unit );
		echo 'Volume: ' . $vol . ' cubic ' . $unit . '<br>';
		$weight = $obj->GetWeight();
		echo 'Weight: ' . $weight . ' gm<br>';
		$den = $obj->GetDensity();
		echo 'Density: ' . $den . ' gm/cc' . '<br>';
		$tcount = $obj->GetTrianglesCount();
		echo 'Triangles Count: ' . $tcount . ' triangles read<br>';
		echo '<br><b>Change Density (default 1.04g/cc -> 2.44g/cc)</b><br>';
		$obj->SetDensity ( 2.44 );
		$unit = 'cm';
		$vol = $obj->GetVolume ( $unit );
		echo 'Volume: ' . $vol . ' cubic ' . $unit . '<br>';
		$weight = $obj->GetWeight();
		echo 'Weight: ' . $weight . ' gm<br>';
		$den = $obj->GetDensity();
		echo 'Density: ' . $den . ' gm/cc' . '<br>';
		$tcount = $obj->GetTrianglesCount();
		echo 'Triangles Count: ' . $tcount . ' triangles read<br>';
	}
} else {
	echo 'File too large or bad file extension.';
}

function __autoload ( $class_name ) {
	include 'classes/' . $class_name . '.php';
}

?>
