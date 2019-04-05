# Picassoboxes #
Imagine you are working with a box-like region of 1, 2, 3, etc. dimensions.
Now imagine you need to randomly generate cells (or smaller boxes).
The typical approach? Generate points randomly and use these as the corner
points of the boxes. The problem: the generated boxes will overlap with a non-0 probability. With **Picassoboxes** classes (`picassobox` und `picassoboxes`) this is avoidable. The latter class constructs a network of objects (each a `picassobox`) and during generation keeps track of the available space, where corner points may be safely placed for new boxes whilst avoiding overlaps.

This kind of geometric random generation may be applicable in different cases like biology, soil mechanics, generation of diagrammes, *etc.*

## R Class ##
An auxiliary class `pyArray` was written, in order to allow for more robust methods for vectors. See the script for more details. Some aspects of the `picassobox` und `picassoboxes` classes are of this type.

### Beispielcode ###
```r
library('picassoboxes');

PBs <- picassoboxes(c(1024,768)); # definiert einen 1024 x 768 Bereich
# Alle Dimensionen >= 1 sind möglich, nicht nur 2!
PBs$addrandomcell(c(10,10), n=4); # erzeugt 4 Boxes der Größe 10 x 10
for(PB in PBs$boxes$get()) { # Schleife über die 4 Boxes
	pt_1 <- PB$point(0); # unterster Eckpunkt
	pt_m <- PB$point(0.5); # Mittepunkt der Box
	pt_2 <- PB$point(1); # oberster Eckpunkt
	# hier kommen Befehle, um bspw. die Box zu malen,
	# mit den Punkten zu berechnen, usw.
}

PBs$addrandomcell([15,12]); # add a 15 x 12 box
for(PB in PBs$getpartition()$get()) { # use the latest computed partition of the region
	pt <- PB$point(0.5);
	col <- PB$colour;
	# Colour = 0 <==> available point:
	# i. e. the lower corner of a 15 x 12 box may be placed here,
	# without risk of overlapping existing boxes.
	# Colour = 1 <==> not ···
}
```


## Python Class ##

### Example Code ###
```python
import picassoboxes;

PBs = picassoboxes([1024,768]); # define a 1024 x 768 region
# all dimensions >= 1 are possible, not just 2!
PBs.addrandomcell([10,10], 4); # erzeugt 4 Boxes der Größe 10 x 10
for PB in PBs.boxes: # loop over the 4 boxes
	pt_1 = PB.point(0); # lower corner
	pt_m = PB.point(0.5); # mid point
	pt_2 = PB.point(1); # upper corner
	# here come your commands to e. g. draw the box
	# compute stuff with the points, etc.

PBs.addrandomcell([15,12]); # add a 15 x 12 box
for PB in PBs.getpartition(): # use the latest computed partition of the region
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

$PBs = new picassoboxes([1024,768]); // define a 1024 x 768 region
// all dimensions >= 1 are possible, not just 2!
$PBs->addrandomcell([10,10], 4); // generates 4 boxes of size 10 x 10

foreach($PBs->boxes as $PB) { // loop over the 4 boxes
	$pt_1 = $PB->point(0); // lower corner
	$pt_m = $PB->point(0.5); // mid point
	$pt_2 = $PB->point(1); // upper corner
	// here come your commands to e. g. draw the box
	// compute stuff with the points, etc.
}

$PBs->addrandomcell([15,12]); // add a 15 x 12 box
foreach($PBs->getpartition() as $PB) { // use the latest computed partition of the region
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