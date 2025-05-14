var polyAdd = (a, b) => {
	let len = Math.max(a.length, b.length);
	let out = new Array(len).fill(0);

	for (let i = 0; i < a.length; i++) {
		out[i] += a[i];
	}
	for (let i = 0; i < b.length; i++) {
		out[i] += b[i];
	}

	return out;
};

var polyPower = (idx, pow) => {
	return new Array(pow).fill(0).concat(idx);
};

var modPow = (x, y, mod) => {
	if (y == 0) {
		return 1;
	}
	let working = 1;

	let base = x % mod;
	let exp = y;
	while (exp > 0) {
		if (exp % 2 == 1) {
			working = (working * base) % mod;
		}
		exp >>= 1;
		base = (base ** 2) % mod;
	}

	return working;
};

var modPowLookup = new Map();

var modPowCached = (x, y, mod) => {
	if (modPowLookup.has(`${x} ${y} ${mod}`)) {
		return modPowLookup.get(`${x} ${y} ${mod}`);
	}

	let out = modPow(x, y, mod);
	modPowLookup.set(`${x} ${y} ${mod}`, out);

	return out;
};

var primitivenunity = (n, mod) => {
	for (let root = 0; root < mod; root++) {
		if (modPow(root, n, mod) == 1) {
			let taken = false;
			for (let k = 1; k < n; k++) {
				if (modPow(root, k, mod) == 1) {
					taken = true;
				}
			}
			if (!taken) {
				return root;
			}
		}
	}
	return null;
};

var primitive2nunity = (n, mod) => {
	let nthunity = primitivenunity(n, mod);
	for (let root = 0; root < mod; root++) {
		if (modPow(root, 2, mod) == nthunity && modPow(root, n, mod) == mod - 1) {
			return root;
		}
	}
};

var polyDisplay = (poly) => {
	let skip = false;
	for (let i = 0; i < poly.length; i++) {
		if (i > 4 && i < poly.length - 4) {
			if (!skip) {
				document.body.innerHTML += "... &nbsp; &nbsp; ";
				skip = true;
			}
			continue;
		}
		else {
			document.body.innerHTML += `${poly[i]} x^${i} &nbsp; &nbsp; `;
		}
	}
};

var modulusRLWE = 25601;
var keypowRLWE = 9;
var sampleBoundRLWE = 8 / Math.sqrt(2 * Math.pi);

var keysizeRLWE = 2 ** keypowRLWE;
var ringRLWE = polyAdd(polyPower([1], keysizeRLWE), [1]);
var ring2NunityRLWE = primitive2nunity(keysizeRLWE, modulusRLWE);
