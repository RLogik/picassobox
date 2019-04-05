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

	public function modify($params) {
		$X = new picassobox($this->sides, $this->colour);

		if(isset($params['preshift'])) for($k=0; $k<$this->dim; $k++) $X->sides[k][0] += $params['preshift'][k];
		if(isset($params['postshift'])) for($k=0; $k<$this->dim; $k++) $X->sides[k][1] += $params['postshift'][k];
		if(isset($params['colour'])) $X->colour = $params['colour'];

		return $X;
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
	private $filter = [];

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

	public function getpartition() {
		$part = [$this->box];
		$boxes_ = [];
		foreach($this->boxes as $PB) $boxes_[] = $PB;
		foreach($this->bounds as $PB) $boxes_[] = $PB;
		foreach($boxes_ as $PB) {
			$shift = [];
			foreach($this->bufferdim as $k => $val) $shift[] = -$val;
			$PB = $PB->modify(['preshift'=>$shift, 'colour'=>1]);
			$part_ = pyArray();
			foreach($part as $k => $PB_) {
				if($PB_->colour == 0) {
					$part__ = $PB_->mince($PB);
				} else {
					$part__ = [$PB_];
				}
				foreach($part__ as $PB__) $part_[] = $PB__;
			}
			$part = $part_;
		}
		return($part);
	}

	public function randomselection($depth=0, $PB=NULL) {
		if($depth == 0) return($this->randomselection(1, $this->box));
		if($depth > count($this->filter)) return($PB);

		$PB_e = NULL;
		$part = $PB->mince($this->filter[$depth]);
		$part = array_filter($part, function($PB_) {
			return($PB_->colour == 0 && $PB_->mass > 0);
		});
		if(count($part) == 0) return(NULL);

		$wt = [];
		foreach($part as $PB_) $wt[] = $PB_->mass;
		$wt_ = $wt;
		$len_ind = count($part);
		$ind = range(0, $len_ind-1);
		while($len_ind > 0) {
			$F = [0];
			$sum = 0;
			foreach($wt_ as $m) {
				$sum += $m;
				$F[] = $sum;
			}
			$u = mt_rand(0,2147483647-1)/2147483647;
			$m = $sum*$u;
			$j = 0;
			for($i=0; $i<$len_ind; $i++) {
				if($m >= F[$i+1]) continue;
				$j = $i;
				break;
			}
			$PB_ = $part[$ind[$j]];
			$PB_e = $this->randomselection($depth+1, $PB_);
			if($PB_e instanceof picassobox) break;
			$ind_ = [];
			for($i=0; $i<$len_ind; $i++) {
				if($i === $j) continue;
				$ind_[] = $ind[$i];
				$wt_[] = $wt_[$i];
			}
			$len_ind--;
		}
		return($PB_e);
	}

	public function addrandomcell($bufferdim, $n=1) {
		if(is_numeric($bufferdim)) $bufferdim = array_fill($bufferdim, $this->dim, $bufferdim);
		if(!is_array($bufferdim)) $bufferdim = array_fill(0, $this->dim, 0);

		$this->buffer = $bufferdim;

		if(n == 0) {
			return([]);
		} else if($n > 1) {
			$cells = [];
			for($i=0; $i<$n; $i++) {
				$cell = $this->addrandomcell($bufferdim, 1);
				if(!($cell instanceof picassobox)) break;
				$cells[] = $cell;
			}
			return(cells);
		}

		# Partition für zulässige Teile neu berechnen:
		$this->filter = [];
		$shift = [];
		foreach($bufferdim as $k => $val) $shift[] = -$val;
		foreach($this->bounds as $PB) $this->filter[] = $PB->modify(['preshift'=>$shift, 'colour'=>1]);
		foreach($this->boxes as $PB) $this->filter[] = $PB->modify(['preshift'=>$shift, 'colour'=>1]);

		# Zufällige Selektion eines zulässigen Teils der Partition:
		$PB_part = $this->randomselection();
		if(!($PB_part instanceof picassobox)) return(NULL);

		# Zufällige Selektion eines Punkts in Box:
		$x = $PB_part->randomunif();
		$sides = [];
		foreach($bufferdim as $k => $h) $sides[] = [$x[$k], $x[$k]+$h];
		$PB = new picassobox($sides, 1);

		# Füge zum Netzwerk von Zellen:
		$this->boxes[] = $PB;

		return($PB);
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