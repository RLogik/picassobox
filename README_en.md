# Picassoboxes #
Imagine you are working with a box-like region of 1, 2, 3, etc. dimensions.
Now imagine you need to randomly generate cells (or smaller boxes).
The typical approach? Generate points randomly and use these as the corner
points of the boxes. The problem: the generated boxes will overlap with a non-0 probability. With **Picassoboxes** classes (`picassobox` und `picassoboxes`) this is avoidable. The latter class constructs a network of objects (each a `picassobox`) and during generation keeps track of the available space, where corner points may be safely placed for new boxes whilst avoiding overlaps.

This kind of geometric random generation may be applicable in different cases like biology, soil mechanics, generation of diagrammes, *etc.*

## R Class ##

[**documentation to be written**]


## Python Class ##

### Example Code ###
```python
import picassoboxes;

PBnet = picassoboxes([1024,768]); # define a 1024 x 768 region
# all dimensions >= 1 are possible, not just 2!
PBnet.addrandomcell([10,10], 4); # erzeugt 4 Boxes der Größe 10 x 10
for PB in PBnet.boxes: # loop over the 4 boxes
	pt_1 = PB.point(0); # lower corner
	pt_m = PB.point(0.5); # mid point
	pt_2 = PB.point(1); # upper corner
	# here come your commands to e. g. draw the box
	# compute stuff with the points, etc.

PBnet.addrandomcell([15,12]); # add a 15 x 12 box
for PB in PBnet.part: # use the latest computed partition of the region
	pt = PB.point(0.5);
	col = PB.colour;
	# Colour = 0 <==> available point:
	# i. e. the lower corner of a 15 x 12 box may be placed here,
	# without risk of overlapping existing boxes.
	# Colour = 1 <==> not ···
```


## PHP Class ##

### Example Code ###
```php
require_once "[PFAD]/picassoboxes.php";

$PBnet = new picassoboxes([1024,768]); // define a 1024 x 768 region
// all dimensions >= 1 are possible, not just 2!
$PBnet->addrandomcell([10,10], 4); // generates 4 boxes of size 10 x 10

foreach($PBnet->boxes as $PB) { // loop over the 4 boxes
	$pt_1 = $PB->point(0); // lower corner
	$pt_m = $PB->point(0.5); // mid point
	$pt_2 = $PB->point(1); // upper corner
	// here come your commands to e. g. draw the box
	// compute stuff with the points, etc.
}

$PBnet->addrandomcell([15,12]); // add a 15 x 12 box
foreach($PBnet->part as $PB) { // use the latest computed partition of the region
	$pt = PB->point(0.5);
	$col = PB->colour;
	/*
	Colour = 0 <==> available point:
	i. e. the lower corner of a 15 x 12 box may be placed here,
	without risk of overlapping existing boxes.
	Colour = 1 <==> not ···
	*/
}
```