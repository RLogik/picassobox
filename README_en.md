# Picassoboxes
Imagine you are working with a box-like region of 1, 2, 3, etc. dimensions.
Now imagine you need to randomly generate cells (or smaller boxes).
The typical approach? Generate points randomly and use these as the corner
points of the boxes. The problem: the generated boxes will overlap with a non-0 probability. With **Picassoboxes** classes (`picassobox` und `picassoboxes`) this is avoidable. The latter class constructs a network of objects (each a `picassobox`) and during generation keeps track of the available space, where corner points may be safely placed for new boxes whilst avoiding overlaps.

This kind of geometric random generation may be applicable in different cases like biology, soil mechanics, generation of diagrammes, *etc.*

## Python Class

[**Unter arbeit!**]

### Example Code
```python
import picassoboxes;

PBnet = picassoboxes([1024,768]); # definiert einen 1024 x 768 Bereich
# Alle Dimensionen >= 1 sind möglich, nicht nur 2!
PBnet.addrandomcell([10,10], 4); # erzeugt 4 Boxes der Größe 10 x 10
for PB in PBnet.boxes: # Schleife über die 4 Boxes
	pt_1 = PB.point(0); # unterster Eckpunkt
	pt_m = PB.point(0.5); # unterster Eckpunkt
	pt_2 = PB.point(1); # Mittepunkt der Box
	# hier kommen Befehle, um bspw. die Box zu malen,
	# mit den Punkten zu berechnen, usw.

PBnet.addrandomcell([15,12]); # füge eine 15 x 12 Box hinzu
for PB in PBnet.part: # zuletzt berechnete Partition des Bereichs
	pt = PB.point(0.5);
	col = PB.colour;
	# Farbe = 0 <==> verfügbarer Punkt:
	# unterster Ecke einer Box kann hier Platziert werden
	# und eine weiter 15x12 Box kann um diese Punkt erzeugt werden,
	# ohne die bisher existierenden Boxes zu überschneiden.
	# Farbe = 1 <==> nicht ···
```


## PHP Class

[**Unter arbeit!**]

### Example Code
```php
require_once "[PFAD]/picassoboxes.php";

$PBnet = new picassoboxes([1024,768]); // definiert einen 1024 x 768 Bereich
// Alle Dimensionen >= 1 sind möglich, nicht nur 2!
$PBnet->addrandomcell([10,10], 4); // erzeugt 4 Boxes der Größe 10 x 10

foreach($PBnet->boxes as $PB) { // Schleife über die 4 Boxes
	$pt_1 = $PB->point(0); // unterster Eckpunkt
	$pt_m = $PB->point(0.5); // unterster Eckpunkt
	$pt_2 = $PB->point(1); // Mittepunkt der Box
	// hier kommen Befehle, um bspw. die Box zu malen,
	// mit den Punkten zu berechnen, usw.
}

$PBnet->addrandomcell([15,12]); // füge eine 15 x 12 Box hinzu
foreach($PBnet->part as $PB) { // zuletzt berechnete Partition des Bereichs
	$pt = PB->point(0.5);
	$col = PB->colour;
	/*
	Farbe = 0 <==> verfügbarer Punkt:
	unterster Ecke einer Box kann hier Platziert werden
	und eine weiter 15x12 Box kann um diese Punkt erzeugt werden,
	ohne die bisher existierenden Boxes zu überschneiden.
	Farbe = 1 <==> nicht ···
	*/
}
```