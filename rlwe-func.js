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
		let spaces = 15 - vec[i].toString().length;
		out += vec[i] + ("&nbsp;".repeat(spaces > 0 ? spaces : 0));
	}

	return out;
};

var randInstruction = () => {
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
		keyWithErrors[i] += key2[i][0] % mod;
		keyWithErrors[i] %= mod;
		keyWithErrors[i] += mod;
		keyWithErrors[i] %= mod;
	}

	randInstruction();

	return [key1, keyWithErrors];
};

var mixPublicRLWE = (key, samples, mod) => {
	let out1 = [[]];
	let out2 = 0;

	for (let j = 0; j < key[0][0].length; j++) {
		out1[0][j] = 0;
	}
	for (let t = 0; t < samples; t++) {
		let rnd = new Uint32Array(1);
		self.crypto.getRandomValues(rnd);
		rnd[0] %= key[0].length;
		let i = rnd[0];
		for (let j = 0; j < key[0][0].length; j++) {
			out1[0][j] += key[0][i][j];
			out1[0][j] %= mod;
		}
		out2 += key[1][i];
		out2 %= mod;
	}

	randInstruction();

	return [ out1, out2 ];
};

var encodeBitRLWE = (key, samples, mod, bit) => {
	let mixed = mixPublicRLWE(key, samples, mod);
	let notmixed = 0;

	if (bit == 1) {
		mixed[1] += Math.floor(mod / 2);
		mixed[1] %= mod;
	}
	else {
		// timing based attacks
		notmixed += Math.floor(mod / 2);
		notmixed %= mod;
	}

	randInstruction();

	return mixed;
};

var decodeBitRLWE = (key, msg, mod) => {
	let difference = mulMatrix(msg[0], key)[0][0] - msg[1];

	difference += Math.floor(mod / 4);
	difference %= mod;

	let val = 0;
	if (difference > Math.floor(mod / 2)) {
		val = 1;
	}

	return val;
};

var encodeByteRLWE = (key, samples, mod, msg) => {
	let out = [];

	for (let i = 0; i < 8; i++) {
		let bit = (msg & (1 << i)) >> i;
		out[i] = encodeBitRLWE(key, samples, mod, bit);
	}

	return out;
}

var decodeByteRLWE = (key, msg, mod) => {
	let out = 0;

	for (let i = 0; i < 8; i++) {
		out |= decodeBitRLWE(key, msg[i], mod) << i;
	}

	return out;
}

var autogenPrivateRLWE = () => {
	return genPrivateRLWE(privSizeRLWE, modulusRLWE);
}

var autogenPublicRLWE = (key) => {
	return genPublicRLWE(key, pubSizeRLWE, modulusRLWE, errorRLWE);
}

var autoencodeByteRLWE = (pubKey, msg) => {
	return encodeByteRLWE(pubKey, samplesRLWE, modulusRLWE, msg);
}

var autodecodeByteRLWE = (privKey, msg) => {
	return decodeByteRLWE(privKey, msg, modulusRLWE);
}

var autoencodeStrRLWE = (pubKey, msg) => {
	return encodeByteRLWE(pubKey, samplesRLWE, modulusRLWE, msg);
}

var autodecodeStrRLWE = (privKey, msg) => {
	return decodeByteRLWE(privKey, msg, modulusRLWE);
}

var rollkey = (rollingKey) => {
	let hash = 0;
	for (let i = 9; i < rollingKey.length - 1; i++) {
		hash += rollingKey[i];
		rollingKey[i] = rollingKey[i + 1];
	}
	rollingKey[rollingKey.length - 1] = hash % 256;

	return rollingKey;
};
var rlwe_cbc = (str) => {
	let tmpkey = key;
	let out = "";
	for (let i = 0; i < str.length; i++) {
		tmpkey = rollkey(tmpkey);
		out += str[i] ^ tmpkey[tmpkey.length - 1];
	}

	return out;
};
var rlwe_encrypt = (str) => {
	return btoa(rlwe_cbc(str));
};
var rlwe_decrypt = (str) => {
	return rlwe_cbc(atob(str));
};
