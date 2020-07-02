<?php

/*
 * (c) ALEXEYGREK Studio
 * https://alexeygrek.com
 */

class STLCalc {

	// Properties
	private $volume;
	private $weight;
	private $density = 1.04;
	private $triangles_count;
	private $triangles_data;
	private $b_binary;
	private $points;
	private $fstl_handle;
	private $fstl_path;
	private $triangles;
	private $flag = false;

	// Initialises the STLCalc class by passing the path to the binary .stl file.
	function __construct ( $filepath ) {
		$b = $this->IsAscii ( $filepath );
		if( !$b ) {
			echo "BINARY STL Suspected.\n";
			$this->b_binary = TRUE;
			$this->fstl_handle = fopen ( $filepath, 'rb' );
			$this->fstl_path = $filepath;
		} else {
			echo "ASCII STL Suspected.\n";
		}
		$this->triangles = array();
	}

	// Returns the calculated Volume (cc) of the 3D object represented in the binary STL. If $unit is 'cm' then returns volume in cubic cm, but If $unit is 'inch' then returns volume in cubic inches.
	public function GetVolume ( $unit ) {
		if ( !$this->flag ) {
			$v = $this->CalculateVolume();
			$this->volume = $v;
			$this->flag = true;
		}
		$volume = 0;
		if( $unit == 'cm' ) {
			$volume = ( $this->volume / 1000 );
		} else {
			$volume = $this->Inch3 ( $this->volume / 1000 );
		}
		return $volume;
	}

	// Returns the calculated Weight (gm) of the 3D object represented in the binary STL.
	public function getWeight() {
		$volume = $this->GetVolume ( 'cm' );
		$weight = $this->CalculateWeight ( $volume );
		return $weight;
	}

	// Returns the set Density (gm/cc) of the material.
	public function GetDensity() {
		return $this->density;
	}

	// Sets the Density (gm/cc) of the material.
	public function SetDensity ( $den ) {
		$this->density = $den;
	}

	// Returns the number of trianges specified in the binary STL definition of the 3D object.
	public function GetTrianglesCount() {
		$tcount = $this->triangles_count;
		return $tcount;
	}

	// Invokes the binary file reader to read the header, serially reads all the normal vector and triangular co-ordinates, calls the math function to calculate signed tetrahedral volumes for each trangle, sums up these volumes to give the final volume of the 3D object represented in the .stl binary file.
	private function CalculateVolume(){
		$totalVolume = 0;
		if ( $this->b_binary ) {
			$totbytes = filesize ( $this->fstl_path );
			$totalVolume = 0;
			try {
				$this->ReadHeader();
				$this->triangles_count = $this->ReadTrianglesCount();
				$totalVolume = 0;
				try {
					while ( ftell ( $this->fstl_handle ) < $totbytes ){
						$totalVolume += $this->ReadTriangle();
					}
				}
				catch ( Exception $e ) {
					return $e;
				}	
			}
			catch ( Exception $e ) {
				return $e;
			}
			fclose ( $this->fstl_handle );
		} else {
			$k = 0;
			while ( count ( $this->triangles_data[4] ) > 0 ) {
				$totalVolume += $this->ReadTriangleAscii();
				$k += 1;
			}
			$this->triangles_count = $k;
		}
		return abs ( $totalVolume );
	}

	// Wrapper around PHP's unpack() function which decodes binary numerical data to float, int, etc types. $sig specifies the type of data (i.e. integer, float, etc), $l specifies number of bytes to read.
	function PhUnpack ( $sig, $l ) {
		$s = fread ( $this->fstl_handle, $l );
		$stuff = unpack ( $sig, $s );
		return $stuff;
	}

	// Appends to an array either a single var or the contents of another array.
	function PhAppend ( $myarr, $mystuff ) {
		if ( gettype ( $mystuff ) == 'array' ) {
			$myarr = array_merge ( $myarr, $mystuff );
		} else {
			$ctr = count ( $myarr );
			$myarr[$ctr] = $mystuff;
		}
		return $myarr;
	}

	// Reads the binary header field in the STL file and offsets the file reader pointer to enable reading the triangle-normal data.
	function ReadHeader() {
		fseek ( $this->fstl_handle, ftell ( $this->fstl_handle ) + 80 );
	}

	// Reads the binary field in the STL file which specifies the total number of triangles and returns that integer.
	function ReadTrianglesCount() {
		$length = $this->PhUnpack ( 'I', 4 );		
		return $length[1];
	}

	// Reads a triangle data from the binary STL and returns its signed volume. A binary STL is a representation of a 3D object as a collection of triangles and their normal vectors. Its specifiction can be found here: http://en.wikipedia.org/wiki/STL_(file_format)%23Binary_STL This function reads the bytes of the binary STL file, decodes the data to give float XYZ co-ordinates of the trinaglular vertices and the normal vector for a triangle.
	function ReadTriangle() {
		$n	= $this->PhUnpack ( 'f3', 12 );
		$p1	= $this->PhUnpack ( 'f3', 12 );
		$p2	= $this->PhUnpack ( 'f3', 12 );
		$p3	= $this->PhUnpack ( 'f3', 12 );
		$b	= $this->PhUnpack ( 'v', 2 );

		$l = count ( $this->points );
		$this->PhAppend ( $this->triangles, array ( $l, $l+1, $l+2 ) );
		return $this->SignedVolumeOfTriangle ( $p1, $p2, $p3 );
	}

	// Reads a triangle data from the ascii STL and returns its signed volume.
	function ReadTriangleAscii() {
		$p1[1] = floatval ( array_pop ( $this->triangles_data[4] ) );
		$p1[2] = floatval ( array_pop ( $this->triangles_data[5] ) );
		$p1[3] = floatval ( array_pop ( $this->triangles_data[6] ) );
		$p2[1] = floatval ( array_pop ( $this->triangles_data[7] ) );
		$p2[2] = floatval ( array_pop ( $this->triangles_data[8] ) );
		$p2[3] = floatval ( array_pop ( $this->triangles_data[9] ) );
		$p3[1] = floatval ( array_pop ( $this->triangles_data[10] ) );
		$p3[2] = floatval ( array_pop ( $this->triangles_data[11] ) );
		$p3[3] = floatval ( array_pop ( $this->triangles_data[12] ) );
		$l = count ( $this->points );
		$this->PhAppend ( $this->triangles, array ( $l, $l+1, $l+2 ) );
		return $this->SignedVolumeOfTriangle ( $p1, $p2, $p3 );
	}

	// Checks if the given file is an ASCII file. Populates the triangles_data array if TRUE.
	function IsAscii ( $filename ) {
		$b = FALSE;
		$namePattern = '/facet\\s+normal\\s+([-+]?\\b(?:[0-9]*\\.)?[0-9]+(?:[eE][-+]?[0-9]+)?\\b)\\s+([-+]?\\b(?:[0-9]*\\.)?[0-9]+(?:[eE][-+]?[0-9]+)?\\b)\\s+([-+]?\\b(?:[0-9]*\\.)?[0-9]+(?:[eE][-+]?[0-9]+)?\\b)\\s+'
			. 'outer\\s+loop\\s+'
			. 'vertex\\s+([-+]?\\b(?:[0-9]*\\.)?[0-9]+(?:[eE][-+]?[0-9]+)?\\b)\\s+([-+]?\\b(?:[0-9]*\\.)?[0-9]+(?:[eE][-+]?[0-9]+)?\\b)\\s+([-+]?\\b(?:[0-9]*\\.)?[0-9]+(?:[eE][-+]?[0-9]+)?\\b)\\s+'
			. 'vertex\\s+([-+]?\\b(?:[0-9]*\\.)?[0-9]+(?:[eE][-+]?[0-9]+)?\\b)\\s+([-+]?\\b(?:[0-9]*\\.)?[0-9]+(?:[eE][-+]?[0-9]+)?\\b)\\s+([-+]?\\b(?:[0-9]*\\.)?[0-9]+(?:[eE][-+]?[0-9]+)?\\b)\\s+'
			. 'vertex\\s+([-+]?\\b(?:[0-9]*\\.)?[0-9]+(?:[eE][-+]?[0-9]+)?\\b)\\s+([-+]?\\b(?:[0-9]*\\.)?[0-9]+(?:[eE][-+]?[0-9]+)?\\b)\\s+([-+]?\\b(?:[0-9]*\\.)?[0-9]+(?:[eE][-+]?[0-9]+)?\\b)\\s+'
			. 'endloop\\s+' . 'endfacet/';
		$fdata = file_get_contents ( $filename );
		preg_match_all ( $namePattern, $fdata, $matches );
		if ( count ( $matches[0] ) > 0 ) {
			$b = TRUE;
			$this->triangles_data = $matches;
		}
		return $b;
	}

	// Returns the signed volume of a triangle as determined by its 3D, XYZ co-ordinates. The var $pn contains an array ( x, y, z).
	function SignedVolumeOfTriangle ( $p1, $p2, $p3 ) {
		$v321 = $p3[1] * $p2[2] * $p1[3];
		$v231 = $p2[1] * $p3[2] * $p1[3];
		$v312 = $p3[1] * $p1[2] * $p2[3];
		$v132 = $p1[1] * $p3[2] * $p2[3];
		$v213 = $p2[1] * $p1[2] * $p3[3];
		$v123 = $p1[1] * $p2[2] * $p3[3];
		return ( 1.0 / 6.0 ) * ( -$v321 + $v231 + $v312 - $v132 - $v213 + $v123 );
	}
	// Converts the volume specified in cubic cm to cubic inches.
	function Inch3 ( $v ) {
		return $v * 0.0610237441;
	}
	// Calculates the weight of the supplied volume specified in cubic cm and returns it in gms.
	function CalculateWeight ( $volumeInCm ) {
		return $volumeInCm * $this->density;
	}
}

?>
