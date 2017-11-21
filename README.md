### STLCalc
-----
Simple and available way to calculate the cost of 3D printing STL model.

### Introduction
-----

STL format is widely used to represent information about 3D objects in 3D printing. It is important to know the volume, weight and cost of material that can turn into an object on a 3D printer, before starting 3D printing. This method helps to calculate the cost, materials spent and, in fact, the time of 3D printing of a 3D product.

Automatic recognition of ASCII and BINARY formats is supported.

PHP code has been commented on.

The use of this implementation is available in a simple example.

### Install
-----
Follow these steps to start using the **STLCalc class** in your applications:

* Copy the `stlcalc.php` file to your webserver folder where your application can resolve the path and find this class file.
* Use code from example or something like this:

   ```php
   $path = '/var/www/files/model.stl';
   $obj = new STLCalc ( $path );
   $unit = 'cm';
   echo $obj->GetVolume ( $unit );
   ```

### Constructor
-----
Initialize the **STLCalc class** by passing the path to the **BINARY .stl file**.

### Public Functions
-----
Following public functions have been exposed that they can be called from outside the **STLCalc class**.

Public functions: `GetVolume`, `GetWeight`, `GetDensity`, `SetDensity`, `GetTrianglesCount`.

### Binary Read Functions
-----
Following binary functions have been defined. They are used to read and decode binary data and convert it to appropriate integer, float, etc formats.

Binary read functions: `ReadHeader`, `ReadTriangleCount`, `ReadTriangle`.

### Mathematic Functions
-----
Following mathematic functions have been defined. The `SignedVolumeOfTriangle` function is the core function of the class. It computes the tetrahedral volume for each triangle and returns its signed value for summing. The `Inch3` is a utility function for conversion of units from cubic cm to cubic inches. The `CalculateWeight` is function that calculates the weight of the 3D object using the density as a parameter.

Mathematic functions: `SignedVolumeOfTriangle`, `Inch3`, `CalculateWeight`.


### Example Output
-----
```text
STLCalc Example

BINARY STL Suspected. 

Basic Usage
Volume: 6.3402593834147 cubic cm
Weight: 6.5938697587513 gm
Density: 1.04 gm/cc
Triangles Count: 28276 triangles read

Units -> inch
Volume: 0.38690636614112 cubic inch
Weight: 6.5938697587513 gm
Density: 1.04 gm/cc
Triangles Count: 28276 triangles read

Change Density (default 1.04g/cc -> 2.44g/cc)
Volume: 6.3402593834147 cubic cm
Weight: 15.470232895532 gm
Density: 2.44 gm/cc
Triangles Count: 28276 triangles read
```

### References
-----
- [STL (file format)](https://en.wikipedia.org/wiki/STL_(file_format))
- [Thingiverse](https://www.thingiverse.com/)
