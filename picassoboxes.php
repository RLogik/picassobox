<?php
class picassobox {
	public function __construct($box, $colour) {
		$this->dim = count($box);
		$this->box = $box;
		$this->colour = $colour;
		$this->sides = [];
		$this->mass = 1;
		foreach($box as $side) {
			$dx = $side[1]-$side[0];
			$this->sides[] = $dx;
			$this->mass = $this->mass*$dx;
		}
		return;
	}

	public function randomunif() {
		$x = [];
		foreach($this->box as $xx) {
			$u = mt_rand(0,2147483647-1)/2147483647;
			$x[] = $xx[0] + $u*($xx[1]-$xx[0]);
		}
		return $x;
	}

	public function point($coord) {
		if(!is_array($coord)) $coord = array_fill(0, $this->dim, $coord);
		$x = [];
		foreach($this->box as $k => $xx) {
			$u = $coord[$k];
			$x[] = $xx[0] + $u*($xx[1]-$xx[0]);
		}
		return $x;
	}

	public function intersects($PB) {
		$bool = TRUE;
		foreach($this->box as $k => $side) {
			$side_ = $PB->box[$k];
			if($side_[1] <= $side[0] or $side[1] <= $side_[0]) {
				$bool = FALSE;
				break;
			}
		}	
		return $bool;
	}

	public function subseteq($PB) {
		$bool = TRUE;
		foreach($this->box as $k => $side) {
			$side_ = $PB->box[$k];
			if($side_[0] <= $side[0] and $side[0] <= $side_[1] and $side[1] <= $side_[1]) continue;
			$bool = FALSE;
			break;
		}
		return $bool;
	}

	public function mince($PBfilt, $col=1) {
		if(!$this->intersects($PBfilt)) return [new picassobox($this->box, 0)];

		$sides = [];
		$trace = [];
		foreach($this->box as $k => $side) {
			$trace_ = [];
			$sides_ = [];
			$side_ = $PBfilt->box[$k];
			$a = $side[0];
			$b = $side[1];
			$aa = $side_[0];
			$bb = $side_[1];

			if($aa < $a) $aa = $a;
			if($bb > $b) $bb = $b;
			if($bb < $aa) $bb = $aa;

			if($a < $aa) {
				$sides_[] = [$a,$aa];
				$trace_[] = 0;
			}
			if($aa < $bb) {
				$sides_[] = [$aa,$bb];
				$trace_[] = 1;
			}
			if($bb < $b) {
				$sides_[] = [$bb,$b];
				$trace_[] = 0;
			}

			$sides[] = $sides_;
			$trace[] = $trace_;
		}

		$traces = picassobox::cartproduct($trace);
		$part = picassobox::cartproduct($sides);
		$boxes = [];
		foreach($traces as $i => $trace) {
			$box = $part[$i];
			$__col = 0;
			if(array_product($trace) === 1) $__col = $col;
			$PB = new picassobox($box,$__col);
			$boxes[] = $PB;
		}
		return $boxes;
	}

	private static function cartproduct($liste) {
		$n = count($liste);
		if($n === 0) return [];
		$prod = [];
		if($n === 1) {
			foreach($liste[0] as $x) $prod[] = [$x];
		} else {
			$prod_ = picassobox::cartproduct(array_slice($liste, 1));
			foreach($prod_ as $y) foreach($liste[0] as $x) $prod[] = array_merge([$x], $y);
		}
		return $prod;
	}
}


class picassoboxes {
	public function __construct($box) {
		$box = picassoboxes::clonelist($box);
		$grenze_lo = [];
		$grenze_hi = [];
		foreach($box as $i => $x) {
			if(!is_array($x)) $box[$i] = [0, $x];
			$grenze_lo[] = $x[0];
			$grenze_hi[] = $x[1];
		}

		$this->dim = count($box);
		$this->box = new picassobox($box, 0);
		$this->mass = $this->box->mass;
		$this->part = [$this->box];
		$this->boxes = [];
		$this->bounds = [];

		foreach(range(0, $this->dim - 1) as $k) {
			$box_ = picassoboxes::clonelist($box);
			$box_[$k][0] = $box[$k][1];
			$this->bounds[] = new picassobox($box_, 1);
		}

		$this->buffer = array_fill(0, $this->dim, 0);
		return;
	}

	public function filtercell($PB) {
		$box = picassoboxes::clonelist($PB->box);
		foreach($this->buffer as $k => $h) $box[$k][0] -= $h;
		$PBfilt = new picassobox($box, 1);

		$PB_filter = array_filter($this->part, function($PB) {return $PB->colour === 0;});
		$this->part = array_filter($this->part, function($PB) {return !($PB->colour === 0);});
		$boxes = [];
		foreach($PB_filter as $PB) foreach($PB->mince($PBfilt) as $PB_) $boxes[] = $PB_;
		shuffle($boxes);
		$this->part = array_merge($this->part, $boxes);
		return;
	}

	public function getboxes() {
		$boxes = [];
		foreach($this->boxes as $PB) $boxes[] = $PB->box;
		return $boxes;
	}

	public function getpartition($colour) {
		$boxes = [];
		foreach($this->part as $PB) if($PB->colour === $colour) $boxes[] = $PB->box;
		return $boxes;
	}

	public function addrandomcell($bufferdim, $n=1) {
		if(!is_array($bufferdim)) $bufferdim = array_fill(0, $this->dim, $bufferdim);

		if($n === 0) {
			return [];
		} else if($n > 1) {
			$cells = [];
			for($i=0; $i<$n; $i++) {
				$cell = $this->addrandomcell($bufferdim, 1);
				if($cell === NULL) break;
				$cells[] = $cell;
			}
			return $cells;
		}

		$bool = FALSE;
		foreach($this->buffer as $k => $h) {
			if($h === $bufferdim[$k]) continue;
			$bool = TRUE;
			break;
		}	

		# Partition für zulässige Teile ggf. neu berechnen:
		if($bool) {
			$this->buffer = $bufferdim;
			$this->part = [$this->box];
			foreach($this->boxes as $PB) $this->filtercell($PB);
			foreach($this->bounds as $PB) $this->filtercell($PB);
		}

		# Zufällige Selektion eines zulässigen Teils der Partition:
		$mass_free = 0;
		$F = [0];
		$indices = [];
		foreach($this->part as $ind => $PB) {
			if($PB->colour === 1) continue;
			$indices[] = $ind;
			$mass_free += $PB->mass;
			$F[] = $mass_free;
		}

		$u = mt_rand(0,2147483647-1)/2147483647;
		$mu = $mass_free*$u;
		$ind = NULL;
		foreach($F as $i => $m) {
			if($i >= count($indices)) break;
			if($mu < $m) continue;
			$ind = $indices[$i];
			break;
		}
		if($ind === NULL) return NULL;
		$PB = $this->part[$ind];

		# Zufällige Selektion eines Punkts in Box:
		$x = $PB->randomunif();
		$box = [];
		foreach($this->buffer as $k => $h) $box[] = [$x[$k],$x[$k]+$h];
		$PB = new picassobox($box, 1);

		# Füge zum Netzwerk von Zellen:
		$this->boxes[] = $PB;
		# Partition verfeinern:
		$this->filtercell($PB);

		return $PB;
	}

	private static function clonelist($x, $deep=TRUE) {
		$xx = $x;
		if(is_array($x)) {
			$xx = [];
			foreach($x as $y) {
				$yy = $y;
				if($deep) $yy = picassoboxes::clonelist($y, TRUE);
				$xx[] = $yy;
			}
		} else if(is_object($x)) {
			$xx = (object) [];
			foreach($x as $key) {
				$y = $x->{$key};
				$yy = $y;
				if($deep) $yy = picassoboxes::clonelist($y, TRUE);
				$xx->{$key} = $yy;
			}
		}
		return $xx;
	}
}
?>