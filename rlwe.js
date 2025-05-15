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

var polyRand = (n, max) => {
	let out = [];

	let vals = new Int32Array(n);
	self.crypto.getRandomValues(vals);

	for (let i = 0; i < n; i++) {
		out[i] = vals[i] % (max + 1);
	}

	return out;
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

var bitReverse = (x, k) => {
	let mask = (1 << k) - 1;

	let v = x & mask;
	let out = 0;
	for (let i = 0; i < k; i++) {
		out <<= 1;
		out |= v & 1;
		v >>= 1;
	}

	return out;
};

var inverseModulus = (val, mod) => {
	for (let i = 1; i < mod; i++) {
		if ((i * val) % mod == 1) {
			return i;
		}
	}
	return null;
};

var padRLWE = (val) => {
	let out = val;

	for (let i = 0; i < keysizeRLWE; i++) {
		if (out[i] == undefined) {
			out[i] = 0;
		}
	}

	return out;
}

var nttRLWE = (val) => {
	let out = padRLWE(val);

	let rootunity = ring2NunityRLWE;
	let k = keypowRLWE;
	let mod = modulusRLWE;

	let t = keysizeRLWE;
	for (let m = 1; m < keysizeRLWE; m *= 2) {
		t /= 2;
		for (let i = 0; i < m; i++) {
			let j1 = 2 * i * t;
			let j2 = j1 + t;
			let s = modPowCached(rootunity, bitReverse(m + i, k), mod);

			for (let j = j1; j < j2; j++) {
				let u = out[j];
				let v = out[j + t] * s;
				out[j] = (u + v) % mod;
				out[j + t] = (u - v + mod) % mod;
			}
		}
	}

	let orderedOut = [];
	for (let i = 0; i < keysizeRLWE; i++) {
		orderedOut[bitReverse(i, k)] = out[i];
	}

	return orderedOut;
};
