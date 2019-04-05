# Picassoboxes #
Stell dir vor, du hast einen boxartigen Bereich in 1, 2, 3, etc. Dimenensionen. Du willst boxartige Zellen unterschiedlicher Dimensionen darin zufällig erzeugen. Dein Ansatz? Du erzeugst Zufallspunkte und verwendest diese jeweils als den untersten Eckpunkt der Boxes. Problem? Die Boxes werden sich mit positiver Wahrscheinlichkeit überschneiden. Mit den **Picassoboxes** Klassen (`picassobox` und `picassoboxes`) kann man eben dieses Problem vermeiden. Die zweite Klasse bildet ein Netzwerk aus Boxen und berechnet den verfügbaren Platz, wo man Eckpunkte von einer hinzuzufügenden Box und dann um diesen Punkte die Box platzieren kann.

Diese Art von geometrischer Zufallserzeugung lässt sich in verschiedenen Fällen wie Biologie, Bodenmechanik, Diagrammerzeugung, usw. anwenden.

## R Class ##

[**Unter Arbeit!**]


## Python Class ##

### Beispielcode ###
```python
import picassoboxes;

PBnet = picassoboxes([1024,768]); # definiert einen 1024 x 768 Bereich
# Alle Dimensionen >= 1 sind möglich, nicht nur 2!
PBnet.addrandomcell([10,10], 4); # erzeugt 4 Boxes der Größe 10 x 10
for PB in PBnet.boxes: # Schleife über die 4 Boxes
	pt_1 = PB.point(0); # unterster Eckpunkt
	pt_m = PB.point(0.5); # Mittepunkt der Box
	pt_2 = PB.point(1); # oberster Eckpunkt
	# hier kommen Befehle, um bspw. die Box zu malen,
	# mit den Punkten zu berechnen, usw.

PBnet.addrandomcell([15,12]); # füge eine 15 x 12 Box hinzu
for PB in PBnet.part: # zuletzt berechnete Partition des Bereichs
	pt = PB.point(0.5);
	col = PB.colour;
	# Farbe = 0 <==> verfügbarer Punkt:
	# d. h. die unterste Ecke einer neuen 15x12 Box kann hier platziert werden,
	# ohne die bisher existierenden Boxes zu überschneiden.
	# Farbe = 1 <==> nicht ···
```


## PHP Class ##

### Beispielcode ###
```php
require_once "[PFAD]/picassoboxes.php";

$PBnet = new picassoboxes([1024,768]); // definiert einen 1024 x 768 Bereich
// Alle Dimensionen >= 1 sind möglich, nicht nur 2!
$PBnet->addrandomcell([10,10], 4); // erzeugt 4 Boxes der Größe 10 x 10

foreach($PBnet->boxes as $PB) { // Schleife über die 4 Boxes
	$pt_1 = $PB->point(0); // unterster Eckpunkt
	$pt_m = $PB->point(0.5); // Mittepunkt der Box
	$pt_2 = $PB->point(1); // unterster Eckpunkt
	// hier kommen Befehle, um bspw. die Box zu malen,
	// mit den Punkten zu berechnen, usw.
}

$PBnet->addrandomcell([15,12]); // füge eine 15 x 12 Box hinzu
foreach($PBnet->part as $PB) { // zuletzt berechnete Partition des Bereichs
	$pt = PB->point(0.5);
	$col = PB->colour;
	/*
	Farbe = 0 <==> verfügbarer Punkt:
	d. h. die unterste Ecke einer neuen 15x12 Box kann hier platziert werden,
	ohne die bisher existierenden Boxes zu überschneiden.
	Farbe = 1 <==> nicht ···
	*/
}
```