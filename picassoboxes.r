#' Picassoboxes
#'
#' \code{picassoboxes(df, ...) [\%>\% split \%>\% summarise(...)]}
#' @param df Dataframe to be clustered. Method also possible with vectors.
#' @param by Specifies the column name for geometric data, according to which the clusters are to be built.
#' @param group Defaults to \code{c()}. Specificies columns, by which data is to be preliminarily divided into groups, within which the clusters are to be built.
#' @param dist Defaults to \code{Inf}. For a column \code{x} of positions, a cluster is a subset \code{y} which satisfies \bold{(i)} between every two points there is a path within \code{y} whose steps are 'small'; \bold{(ii)} y is maximal under condition (i). 'Small' here means the the distance between two points is strictly smaller than \code{d}.
#' @param strict Defaults to \code{TRUE}. If set to \code{FALSE} the 'smaller than' is replaced by 'smaller than or equal to'.
#' @param clustername Defaults to 'cluster'. Running \code{df \%>\% clusterby(...)} returns a data frame, which extends \code{df} by 1 column with this name. This column tags the clusters by a unique index.
#' @param dim New feature to be added. Allow points to be arbitrary data, allow input of a distance function/neighbourhoodscheme.
#' @keywords cluster clustering gene
#' @export picassoboxes
#' @export picassobox
#' @examples df %>% cluster(by='position', group=c('gene','actve'), dist=400, strict=FALSE, clustername='tag');
#' @examples p %>% cluster(dist=10); # p is a numeric vector




################################################################
#### NEBENKLASSEN ##############################################
pyArray <- setRefClass('pyArray',
	fields = list(
		length='numeric',
		values='list'
	),
	methods = list(
		initialize = function(...) {
			INPUTVARS <- list(...);
			if('from' %in% names(INPUTVARS)) {
				werte <- INPUTVARS[['from']];
			} else {
				werte <- INPUTVARS;
			}
			if(class(werte) == 'pyArray') werte <- werte$get();
			if(!(is.list(werte) || is.vector(werte))) werte <- list();
			.self$length <- 0;
			.self$values <- list();
			k <- 0;
			for(x in werte) {
				k <- k + 1;
				.self$values[[k]] <- x;
			}
			.self$length <- k;
		},
		seq = function() {
			if(.self$length > 0) return(c(1:.self$length));
			return(c());
		},
		get = function(ind=NULL) {
			if(is.null(ind)) return(.self$values);
			if(ind <= 0) ind <- .self$length + ind;
			return(.self$values[[ind]]);
		},
		asvector = function(ind=NULL) {
			X <- c();
			for(u in .self$get(ind)) X <- c(X, u);
			return(X);
		},
		set = function(ind, x) {
			if(ind <= 0) ind <- .self$length + ind;
			.self$values[[ind]] <- x;
		},
		select = function(ind=NULL) {
			if(is.null(ind)) return(.self$values);
			if(is.numeric(ind)) return(.self$values[ind]);
			return(NULL);
		},
		filter = function(cb) {
			return(.self$comprehension('filter'=cb));
		},
		comprehension = function(...) {
			INPUTVARS <- list(...);
			repl <- INPUTVARS[['replace']];
			cb <- INPUTVARS[['filter']];
			X <- pyArray();
			for(i in .self$seq()) {
				val <- .self$values[[i]];
				bool <- TRUE;
				if(class(cb) == 'function') if(!cb(val, i, .self)) next;
				if(class(repl) == 'function') val <- repl(val, i, .self);
				X$append(val);
			}
			return(X);
		},
		foreach = function(cb) {
			for(i in .self$seq()) {
				val <- .self$values[[i]];
				bool <- TRUE;
				cb(val, i, .self);
			}
		},
		shuffle = function() {
			ind <- sample(.self$length);
			X <- .self$copy();
			X$values <- X$values[ind];
			return(X);
		},
		append = function(x) {
			i <- .self$length;
			.self$length <- i + 1;
			.self$values[[i+1]] <- x;
		},
		concatone = function(x) {
			X <- .self$copy();
			X$append(x);
			return(X);
		},
		concat = function(x) {
			if(class(x) == 'pyArray') {
				return(.self$concat(x$get()));
			} else if(is.list(x) || is.vector(x)) {
				X <- .self$copy();
				for(xx in x) X$append(xx);
				return(X);
			}
		}
	)
);

################################################################
#### HAUPTKLASSEN ##############################################

picassobox <- setRefClass('picassobox',
	fields = list(
		dim='numeric',
		colour='numeric',
		mass='numeric',
		sides='pyArray'
	),
	methods = list(
		initialize = function(...) {
			INPUTVARS = list(...);
			if(length(INPUTVARS) >= 2) {
				sides_ <- INPUTVARS[[1]];
				colour_ <- INPUTVARS[[2]];
			} else {
				sides_ <- list(0);
				colour_ <- 0;
			}
			if(class(sides_) == 'pyArray') sides_ <- sides_$get();
			if(!(class(sides_) == 'list')) sides_ <- as.list(sides_);
			.self$dim <- length(sides_);
			.self$sides <- pyArray();
			.self$colour <- colour_;
			.self$mass <- 1;
			for(k in c(1:.self$dim)) {
				side_ <- sides_[[k]];
				if(length(side_) == 1) side_ <- c(0,side_);
				dx <- diff(side_);
				.self$sides$append(side_);
				.self$mass <- .self$mass * dx;
			}
		},
		randomunif = function() {
			u <- runif(.self$dim);
			return(.self$point(u));
		},
		point = function(coord) {
			if(length(coord) == 1) coord <- rep(coord, .self$dim);
			x <- c();
			for(k in .self$sides$seq()) {
				xx <- .self$sides$get(k);
				x[k] <- xx[1] + coord[k]*diff(xx);
			}
			return(x);
		},
		intersects = function(PB) { # prüft, ob self \cap PB nicht Lebesgue-null
			for(k in .self$sides$seq()) {
				side_ <- .self$sides$get(k);
				side__ <- PB$sides$get(k);
				if(side__[2] <= side_[1] || side_[2] <= side__[1]) return(FALSE);
			}
			return(TRUE);
		},
		subseteq = function(PB) { # prüft, ob self \subseteq PB
			for(k in .self$sides$seq()) {
				side_ <- .self$sides$get(k);
				side__ <- PB$sides$get(k);
				if(side__[1] > side_[1] || side__[2] < side_[2]) return(FALSE);
			}
			return(TRUE);
		},
		modify = function(...) {
			INPUTVARS <- list(...);
			VARNAMES <- names(INPUTVARS);
			X <- .self$copy();
			sides_ <- X$sides;
			if('preshift' %in% VARNAMES) for(k in X$sides$seq()) X$sides$values[[k]][1] <- X$sides$values[[k]][1] + INPUTVARS[['preshift']][k];
			if('postshift' %in% VARNAMES) for(k in X$sides$seq()) X$sides$values[[k]][2] <- X$sides$values[[k]][2] + INPUTVARS[['postshift']][k];
			if('colour' %in% VARNAMES) X$colour <- INPUTVARS[['colour']];
			return(X);
		},
		mince = function(PBfilt, col=1) {
			if(!.self$intersects(PBfilt)) return(pyArray(picassobox(.self$sides, 0)));

			sides_ <- pyArray();
			trace_ <- pyArray();
			for(k in .self$sides$seq()) {
				side_ <- .self$sides$get(k);
				side__ <- PBfilt$sides$get(k);
				a <- side_[1];
				b <- side_[2];
				aa <- side__[1];
				bb <- side__[2];

				if(aa < a) aa <- a;
				if(bb > b) bb <- b;
				if(bb < aa) bb <- aa;

				side__ <- pyArray();
				trace__ <- pyArray();

				if(a < aa) {
					side__$append(c(a,aa))
					trace__$append(0);
				}
				if(aa < bb) {
					side__$append(c(aa,bb));
					trace__$append(1);
				}
				if(bb < b) {
					side__$append(c(bb,b));
					trace__$append(0);
				}

				sides_$append(side__);
				trace_$append(trace__);
			};

			part_ <- .self$cartproduct(sides_);
			traces_ <- .self$cartproduct(trace_, TRUE);

			boxes <- pyArray();
			for(i in traces_$seq()) {
				trace_ <- traces_$get(i);
				sides_ <- part_$get(i);
				col_ <- col;
				if(0 %in% trace_) col_ <- 0;
				PB <- picassobox(sides_, col_);
				boxes$append(PB);
			}

			return(boxes);
		},
		cartproduct = function(arr, asvector=FALSE) {
			n <- arr$length;
			prod_ <- pyArray();
			if(n == 0) return(prod_);
			arr_ <- arr$get(1);
			if(asvector) {
				if(n == 1) {
					prod_ <- arr_$copy();
				} else {
					arr <- pyArray(from=arr$select(-1));
					prod__ <- .self$cartproduct(arr, TRUE);
					for(y in prod__$get()) for(x in arr_$get()) prod_$append(c(x,y));
				}
			} else {
				if(n == 1) {
					for(x in arr_$get()) prod_$append(pyArray(x));
				} else {
					arr <- pyArray(from=arr$select(-1));
					prod__ <- .self$cartproduct(arr, FALSE);
					for(y in prod__$get()) for(x in arr_$get()) prod_$append(pyArray(x)$concat(y));
				}
			}
			return(prod_);
		}
	)
);

probabilitytree <- setRefClass('probabilitytree',
	fields = list(
		nodes = 'pyArray',
		edges= 'pyArray',
		weight = 'pyArray',
		isleaf = 'pyArray',
		length = 'numeric'
	),
	methods = list(
		initialize = function(...) {
			INPUTVARS <- list(...);
			obj <- NULL;
			if(length(INPUTVARS) >= 1) obj <- INPUTVARS[[1]];

			.self$nodes <- pyArray(obj);
			.self$edges <- pyArray();
			.self$weight <- pyArray(1);
			.self$isleaf <- pyArray(TRUE);
			.self$length <- 1;
		},
		add = function(k, obj, p) {
			n <- .self$length + 1;
			.self$nodes$append(obj);
			.self$weight$append(p);
			.self$isleaf$set(k, FALSE);
			.self$isleaf$append(TRUE);
			.self$edges$append(c(k, n));
			.self$length <- n;
		},
		children = function(k) {
			kinder <- c();
			for(e in .self$edges$get()) if(e[1] == k) kinder <- c(kinder, e[2]);
			return(kinder);
		},
		getleaves = function(asindices=FALSE) {
			n <- .self$length;
			LEAVES <- c();
			for(k in c(1:n)) if(.self$isleaf$get(k)) LEAVES <- c(LEAVES, k);
			if(asindices) return(LEAVES);
			return(pyArray(from=.self$nodes$select(LEAVES)));
		},
		randomselection = function(k=1) {
			if(.self$isleaf$get(k)) {
				return(.self$nodes$get(k))
			} else {
				kinder <- .self$children(k);
				wt <- c();
				for(kk in kinder) wt <- c(wt, .self$weight$get(kk));

				F <- cumsum(c(0, wt));
				u <- runif(1);
				ind <- NULL;
				for(i in c(1:(length(F)-1))) {
					if(wt[i] == 0) next;
					if(u >= F[i+1]) next;
					ind <- i;
					break;
				}

				if(is.null(ind)) return(NULL);
				k <- kinder[ind];
				return(.self$randomselection(k));
			}
		}
	)
);

picassoboxes <- setRefClass('picassoboxes',
	fields = list(
		dim='numeric',
		boxes='pyArray',
		box='picassobox',
		bounds='pyArray',
		buffer='numeric',
		tree='probabilitytree'
	),
	methods = list(
		initialize = function(...) {
			INPUTVARS <- list(...);
			sides_ <- list(c(0,0));
			if(length(INPUTVARS) >= 1) sides_ <- INPUTVARS[[1]];
			sides__ <- list();
			for(i in seq_along(sides_)) {
				x <- sides_[[i]];
				if(length(x) == 1) x <- c(0,x);
				sides__[[i]] <- x;
			}
			sides_ <- sides__;

			.self$dim <- length(sides_);
			.self$box <- picassobox(sides_, 0);
			.self$boxes <- pyArray();
			.self$bounds <- pyArray();

			for(k in c(1:.self$dim)) {
				sides__ <- sides_;
				sides__[[k]][1] <- sides_[[k]][2];
				.self$bounds$append(picassobox(sides__, 1));
			}

			.self$buffer <- rep(0, .self$dim);
			.self$tree <- probabilitytree(.self$box);
		},
		filtercell = function(PB) {
			PBfilt <- PB$modify(preshift=-.self$buffer, colour=1);

			for(k in .self$tree$getleaves(TRUE)) {
				PB <- .self$tree$nodes$get(k);
				if(PB$mass == 0 || !(PB$colour == 0)) next;
				part <- PB$mince(PBfilt);
				m <- 0;
				for(PB_ in part$get()) if(PB_$colour == 0) m <- m + PB_$mass;
				if(m == 0) {
					PB$colour <- 1;
					.self$tree$nodes$set(k, PB);
				} else {
					for(PB_ in part$get()) {
						m_ <- PB_$mass;
						if(m_ > 0 && PB_$colour == 0) {
							.self$tree$add(k, PB_, m_/m);
						} else {
							.self$tree$add(k, PB_, 0);
						}
					}
				}
			}

			return(TRUE);
		},
		getboxes = function() {
			return(.self$boxes$comprehension(replace=function(PB, i, arr) {
				return(PB$sides);
			}));
		},
		getpartition = function(col_) {
			return(.self$tree$nodes(replace=function(PB, i, arr) {
				return(PB$sides);
			}, filter=function(PB, i, arr) {
				return(.self$tree$isleaf$get(i) && PB$colour == col_)
			}));
		},
		addrandomcell = function(bufferdim, n=1) {
			if(length(bufferdim) == 1) bufferdim <- rep(bufferdim, .self$dim);

			if(n == 0) {
				return(pyArray());
			} else if(n > 1) {
				cells <- pyArray();
				for(i in c(1:n)) {
					cell <- .self$addrandomcell(bufferdim, 1);
					if(is.null(cell)) break;
					cells$append(cell);
				}
				return(cells);
			}

			bool <- FALSE;
			for(k in seq_along(.self$buffer)) {
				if(.self$buffer[k] == bufferdim[k]) next;
				bool <- TRUE;
				break;
			}

			# Partition für zulässige Teile ggf. neu berechnen:
			if(bool) {
				.self$buffer <- bufferdim;
				.self$tree <- probabilitytree(.self$box);
				boxes_ <- .self$boxes$copy();
				boxes_ <- boxes_$concat(.self$bounds);
				for(PB in boxes_$get()) .self$filtercell(PB);
			}

			# Zufällige Selektion eines zulässigen Teils der Partition:
			PB <- .self$tree$randomselection();
			if(is.null(PB)) return(NULL);


			# Zufällige Selektion eines Punkts in Box:
			x <- PB$randomunif();
			sides_ <- pyArray();
			for(k in seq_along(.self$buffer)) {
				h <- .self$buffer[k];
				sides_$append(c(x[k],x[k]+h));
			}
			PB <- picassobox(sides_, 1);

			# Füge zum Netzwerk von Zellen:
			.self$boxes$append(PB);
			# Partition verfeinern:
			.self$filtercell(PB);

			return(PB);
		}
	)
);