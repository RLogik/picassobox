import numpy;


class picassobox:
	def __init__(self, sides, colour):
		self.dim = len(sides);
		self.sides = sides;
		self.colour = colour;
		self.mass = 1;
		for side in sides:
			dx = side[1]-side[0];
			self.mass = self.mass*dx;
		return;

	def randomunif(self):
		x = [];
		for xx in self.sides:
			u = numpy.random.uniform(0,1);
			x.append(xx[0] + u*(xx[1]-xx[0]));
		return x;

	def point(self, coord):
		if not isinstance(coord, list):
			coord = [coord for k in range(0, self.dim)];
		x = [];
		for k, xx in enumerate(self.sides):
			u = coord[k];
			x.append(xx[0] + u*(xx[1]-xx[0]));
		return x;

	def intersects(self, PB):
		bool = True;
		for k,side in enumerate(self.sides):
			side_ = PB.sides[k];
			if side_[1] <= side[0] or side[1] <= side_[0]:
				bool = False;
				break;
		return bool;

	def subseteq(self, PB):
		bool = True;
		for k, side in enumerate(self.sides):
			side_ = PB.sides[k];
			if side_[0] <= side[0] and side[0] <= side_[1] and side[1] <= side_[1]:
				continue;
			bool = False;
			break;
		return bool;

	def modify(self, params):
		X = picassobox(self.sides, self.colour);

		if 'preshift' in params:
			for k  in range(0, self.dim):
				X.sides[k][0] += params['preshift'][k];
		if 'postshift' in params:
			for k  in range(0, self.dim):
				X.sides[k][1] += params['postshift'][k];
		if 'colour' in params:
			X.colour = params['colour'];

		return X;

	def mince(self, PBfilt, col=1):
		if not self.intersects(PBfilt):
			return [picassobox(self.sides, 0)];

		sides = [];
		trace = [];
		for k,side in enumerate(self.sides):
			trace_ = [];
			sides_ = [];
			side_ = PBfilt.sides[k];
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

		part = self.cartproduct(sides);
		traces = self.cartproduct(trace);
		boxes = [];
		for i,trace in enumerate(traces):
			sides = part[i];
			col_ = 0;
			if numpy.prod(trace) == 1:
				col_ = col;
			PB = picassobox(sides, col_);
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
	def __init__(self, sides):
		sides = self.clonelist(sides);
		for i, x in enumerate(sides):
			if not isinstance(x, list):
				sides[i] = [0, x];

		self.dim = len(sides);
		self.box = picassobox(sides, 0);
		self.boxes = [];
		self.bounds = [];

		for k in range(0, self.dim):
			sides_ = self.clonelist(sides);
			sides_[k][0] = sides[k][1];
			self.bounds.append(picassobox(sides_, 1));

		self.buffer = [0 for i in range(0, self.dim)];
		return;

	def getboxes(self):
		return [PB.sides for PB in self.boxes];

	def getpartition(self):
		part <- [self.box];
		for PB in self.boxes + self.bounds:
			PB = PB.modify(preshift=-self.buffer, colour=1);
			part_ = [];
			for PB_ in part:
				if PB_.colour == 0:
					part__ = PB_.mince(PB);
				else:
					part__ = [PB_];
				for PB__ in part__:
					part_.append(PB__);
			part = part_;
		return part;

	def randomselection(self, k=0, PB=None):
		if k == 0:
			return self.randomselection(1, self.box);
		if k > len(self.filter):
			return PB;

		PB_e = None;
		part = PB.mince(self.filter[k]);
		part = [PB_ for PB_ in part if PB_.colour == 0 and PB_.mass > 0];
		if len(part) == 0:
			return None;

		wt = [];
		for PB_ in part:
			wt.append(PB_.mass);
		wt_ = wt;
		len_ind = len(part);
		ind = range(0,len(part));
		while len_ind > 0:
			F = [0];
			m_tot = 0
			for i,m in enumerate(wt_):
				m_tot += m;
				F.append(m_tot);
			u = numpy.random.uniform(0, 1);
			m = m_tot*u;
			j = 0;
			for i in range(0, len(ind)):
				if m >= F[i+1]:
					continue;
				j = i;
				break;
			PB_ = part[ind[j]];
			PB_e = self.randomselection(k+1, PB_);
			if not PB_e is None:
				break;
			wt_ = [x for i,x in enumerate(wt) if not i == j];
			ind = [i for i in ind if not i == j];
			len_ind -= 1;

		return PB_e;

	def addrandomcell(self, bufferdim, n=1):
		if isinstance(bufferdim, (int, long, float)):
			bufferdim = [bufferdim for i in range(0, self.dim)];
		if not isinstance(bufferdim, list):
			bufferdim = [0 for i in range(0, self.dim)];

		self.buffer = bufferdim;

		if n == 0:
			return [];
		elif n > 1:
			cells = [];
			for i in range(n):
				cell = self.addrandomcell(bufferdim, 1);
				if not instanceof(cell, 'picassobox'):
					break;
				cells.append(cell);
			return cells;

		# Partition für zulässige Teile neu berechnen:
		self.filter = [];
		for PB in self.bounds + self.boxes:
			self.filter.append(PB.modify({'preshift': -bufferdim, 'colour': 1}));

		# Zufällige Selektion eines zulässigen Teils der Partition:
		PB_part = self.randomselection();
		if not isinstance(PB_part, 'picassobox'):
			return(None);

		# Zufällige Selektion eines Punkts in Box:
		x = PB_part.randomunif();
		sides = [[x[k], x[k]+h] for k, h in enumerate(bufferdim)];
		PB = picassobox(sides, 1);

		# Füge zum Netzwerk von Zellen:
		self.boxes.append(PB);

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


class picassolines:
	def __init__(self, sides=None):
		if not sides is None:
			self.setframe(sides);
		self.edges = [];
		self.paths = [];
		self.points = [];
		self.radius = [];
		pass;

	def setframe(self, sides):
		sides = self.clonelist(sides);
		for i, x in enumerate(sides):
			if not isinstance(x, list):
				sides[i] = [0, x];
		self.dim = len(sides);
		self.box = picassobox(sides, 0);
		pass;

	def addpoints(self, x, r):
		self.points = x;
		if isinstance(r, list):
			r = [r for p in x];
		self.radius = r;
		pass;

	def addpointsfrompicassoboxes(self, PBs=[], r=1, relative=True):
		self.dim = PBs.dim;
		self.box = PBs.box;
		self.points = [PB.point(0.5) for PB in PBs.boxes];
		if relative:
			self.radius = [
				r*numpy.sqrt(
					sum([ ((x[1]-x[0])/2)**2 for x in PB.sides ])
				)
				for PB in PBs.boxes
			];
		else:
			self.radius = [r for PB in PBs.boxes];
		pass;
	
	def addpath(self, i, j, ptout=None, angleout=None, ptin=None, anglein=None, buffer=0, relative=True):
		self.edges.append([i, j]);
		radii = self.clonelist(self.radius);
		if relative:
			radii = [(1+buffer)*r for r in radii];
		else:
			radii = [r+buffer for r in radii];

		Pout = self.point[i];
		Rout = radii[i];
		Pin = self.point[j];
		Rin = radii[j];

		dP = [Pin[k]-Pout[k] for k in range(self.dim)];
		norm_dP = numpy.sqrt(sum([dx**2 for dx in dP]));
		u = [dx/norm_dP for dx in dP];
		sgn_dP = [numpy.sign(dx) for dx in dP];

		if ptout is None:
			ptout = [Rout*dx for dx in u];
		if ptin is None:
			ptin = [-Rin*dx for dx in u];
		if angleout is None:
			angleout = u;
		if anglein is None:
			anglein = [-dx for dx in u];

		path = [];

		## berechne Pfad...
		# unter Arbeit

		return(path);

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
