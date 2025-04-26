var randMatrix = (row, col, max) => {
	let out = [];

	for (let i = 0; i < row; i++) {
		out[i] = new Uint32Array(col);
		self.crypto.getRandomValues(out[i]);
		for (let j = 0; j < col; j++) {
			out[i][j] %= max;
		}
	}

	return out;
};

var mulMatrix = (a, b) => {
	let out = [];

	for (let i = 0; i < a.length; i++) {
		out[i] = [];
		for (let j = 0; j < b[0].length; j++) {
			out[i][j] = 0;
			for (let k = 0; k < b.length; k++) {
				out[i][j] += a[i][k] * b[k][j];
			}
		}
	}

	return out;
};

var formatMatrix = (mat) => {
	let out = "";

	for (let i = 0; i < mat.length; i++) {
		for (let j = 0; j < mat[i].length; j++) {
			out += mat[i][j] + ("&nbsp;".repeat(15 - mat[i][j].toString().length));
		}
		out += "<br><br><br>";
	}

	return out;
};

var formatVector = (vec) => {
	let out = "";

	for (let i = 0; i < vec.length; i++) {
		out += vec[i] + ("&nbsp;".repeat(15 - vec[i].toString().length));
	}

	return out;
};

var randomInstruction = () => {
	let sum = 0;
	for (let i = 0; i < Math.floor(Math.random() * 16); i++) {
		sum += i;
	}
};

var modulusRLWE = 524287;
var privSizeRLWE = 16;
var pubSizeRLWE = 128;
var errorRLWE = 8191;
var samplesRLWE = 16;

var genPrivateRLWE = (size, mod) => {
	return randMatrix(size, 1, mod);
}

var genPublicRLWE = (key, size, mod, error) => {
	let key1 = randMatrix(size, key.length, mod);
	let key2 = mulMatrix(key1, key);
	let keyWithErrors = new Int32Array(size);
	self.crypto.getRandomValues(keyWithErrors);

	for (let i = 0; i < size; i++) {
		keyWithErrors[i] %= error;
		keyWithErrors[i] += key2[i][0]
		keyWithErrors[i] %= mod;
		keyWithErrors[i] += mod;
		keyWithErrors[i] %= mod;
	}

	return [key1, keyWithErrors];
};

var autogenPrivateRLWE = () => {
	return genPrivateRLWE(privSizeRLWE, modulusRLWE);
}

var autogenPublicRLWE = (key) => {
	return genPublicRLWE(key, pubSizeRLWE, modulusRLWE, errorRLWE);
}
