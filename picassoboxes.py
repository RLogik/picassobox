import numpy;


class picassobox:
	def __init__(self, box, colour):
		self.dim = len(box);
		self.box = box;
		self.colour = colour;
		self.sides = [];
		self.mass = 1;
		for side in box:
			dx = side[1]-side[0];
			self.sides.append(dx);
			self.mass = self.mass*dx;
		return;

	def randomunif(self):
		x = [];
		for xx in self.box:
			u = numpy.random.uniform(0,1);
			x.append(xx[0] + u*(xx[1]-xx[0]));
		return x;

	def point(self, coord):
		if not isinstance(coord, list):
			coord = [coord for k in range(0, self.dim)];
		x = [];
		for k, xx in enumerate(self.box):
			u = coord[k];
			x.append(xx[0] + u*(xx[1]-xx[0]));
		return x;

	def intersects(self, PB):
		bool = True;
		for k,side in enumerate(self.box):
			side_ = PB.box[k];
			if side_[1] <= side[0] or side[1] <= side_[0]:
				bool = False;
				break;
		return bool;

	def subseteq(self, PB):
		bool = True;
		for k, side in enumerate(self.box):
			side_ = PB.box[k];
			if side_[0] <= side[0] and side[0] <= side_[1] and side[1] <= side_[1]:
				continue;
			bool = False;
			break;
		return bool;

	def mince(self, PBfilt, col=1):
		if not self.intersects(PBfilt):
			return [picassobox(self.box, 0)];

		sides = [];
		trace = [];
		for k,side in enumerate(self.box):
			trace_ = [];
			sides_ = [];
			side_ = PBfilt.box[k];
			a = side[0];
			b = side[1];
			aa = side_[0];
			bb = side_[1];

			if aa < a:
				aa = a;
			if bb > b:
				bb = b;
			if bb < aa:
				bb = aa;

			if a < aa:			
				sides_.append([a,aa]);
				trace_.append(0);
			if aa < bb:
				sides_.append([aa,bb]);
				trace_.append(1);
			if bb < b:
				sides_.append([bb,b]);
				trace_.append(0);

			sides.append(sides_);
			trace.append(trace_);
			pass;

		traces = self.cartproduct(trace);
		part = self.cartproduct(sides);
		boxes = [];
		for i,trace in enumerate(traces):
			box = part[i];
			col_ = 0;
			if numpy.prod(trace) == 1:
				col_ = col;
			PB = picassobox(box,col_);
			boxes.append(PB);
		return boxes;

	def cartproduct(self, liste):
		n = len(liste);
		if n == 0:
			return [];
		prod = [];
		if n == 1:
			for x in liste[0]:
				prod.append([x]);
		else:
			prod_ = self.cartproduct(liste[1:]);
			for y in prod_:
				for x in liste[0]:
					prod.append([x]+y);
		return prod;

	pass;


class picassoboxes:
	def __init__(self, box):
		box = self.clonelist(box);
		for i, x in enumerate(box):
			if !isinstance(x, list):
				box[i] = [0, x];

		self.dim = len(box);
		self.box = picassobox(box, 0);
		self.mass = self.box.mass;
		self.part = [self.box];
		self.boxes = [];
		self.bounds = [];

		for k in range(0, self.dim):
			box_ = self.clonelist(box);
			box_[k][0] = box[k][1];
			self.bounds.append(picassobox(box_, 1));

		self.buffer = [0 for i in range(0, self.dim)];
		return;

	def filtercell(self, PB, buffer=False):
		box = self.clonelist(PB.box);
		if buffer:
			for k,h in enumerate(self.buffer):
				box[k][0] -= h;
		PBfilt = picassobox(box, 1);

		PB_filter = [PB for PB in self.part if PB.colour == 0];
		self.part = [PB for PB in self.part if not(PB.colour == 0)];
		boxes = [];
		for PB in PB_filter:
			boxes += PB.mince(PBfilt);
		self.part += list(numpy.random.permutation(boxes));

		return;

	def getboxes(self):
		return [PB.box for PB in self.boxes];

	def getpartition(self, colour):
		return [PB.box for PB in self.part if PB.colour == colour];

	def addrandomcell(self, bufferdim, n=1):
		if not isinstance(bufferdim, list):
			bufferdim = [bufferdim for i in range(0, self.dim)];

		if n > 1:
			cells = [];
			for i in range(0, n):
				cells.append(self.addrandomcell(bufferdim, 1));
			return cells;

		bool = False;
		for k,h in enumerate(self.buffer):
			if h == bufferdim[k]:
				continue;
			bool = True;
			break;

		# Partition für zulässige Teile ggf. neu berechnen:
		if bool:
			self.buffer = bufferdim;
			self.part = [self.box];
			for PB in self.boxes + self.bounds:
				self.filtercell(PB, True);

		# Zufällige Selektion eines zulässigen Teils der Partition:
		mass_free = 0;
		F = [0];
		indices = [];
		for ind, PB in enumerate(self.part):
			if PB.colour == 1:
				continue;
			indices.append(ind);
			mass_free += PB.mass;
			F.append(mass_free);

		u = numpy.random.uniform(0,1);
		mu = mass_free*u;
		ind = None;
		for i,m in enumerate(F):
			if i >= len(indices):
				break;
			if mu < m:
				continue;
			ind = indices[i];
			break;
		if ind is None:
			return None;
		PB = self.part[ind];

		# Zufällige Selektion eines Punkts in Box:
		x = PB.randomunif();
		box = [];
		for k,h in enumerate(self.buffer):
			box.append([x[k],x[k]+h]);
		PB = picassobox(box, 1);

		# Füge zum Netzwerk von Zellen:
		self.boxes.append(PB);
		# Partition verfeinern:
		self.filtercell(PB, True);

		return PB;

	def clonelist(self, x, deep=True):
		xx = x;
		if isinstance(x,list) or isinstance(x,tuple):
			xx = [];
			for y in x:
				yy = y;
				if deep:
					yy = self.clonelist(y, True);
				xx.append(yy);
			if isinstance(x,tuple):
				xx = tuple(xx);
		if isinstance(x,dict):
			xx = {};
			for key in x:
				y = x[key];
				yy = y;
				if deep:
					yy = self.clonelist(y, True);
				xx[key] = yy;
		return xx;

	pass;
