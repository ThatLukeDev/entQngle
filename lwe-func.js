var randMatrix = (row, col, max) => {
	let out = [];

	for (let i = 0; i < row; i++) {
		out[i] = new Uint32Array(col);
		self.crypto.getRandomValues(out[i]);
		for (let j = 0; j < col; j++) {
			out[i][j] %= max;
		}
		out[i] = Array.from(out[i]);
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

var modulusLWE = 524287;
var privSizeLWE = 16;
var pubSizeLWE = 128;
var errorLWE = 8191;
var samplesLWE = 16;

var genPrivateLWE = (size, mod) => {
	return randMatrix(size, 1, mod);
}

var genPublicLWE = (key, size, mod, error) => {
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

	return [key1, Array.from(keyWithErrors)];
};

var mixPublicLWE = (key, samples, mod) => {
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

var encodeBitLWE = (key, samples, mod, bit) => {
	let mixed = mixPublicLWE(key, samples, mod);
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

var decodeBitLWE = (key, msg, mod) => {
	let difference = mulMatrix(msg[0], key)[0][0] - msg[1];

	difference += Math.floor(mod / 4);
	difference %= mod;

	let val = 0;
	if (difference > Math.floor(mod / 2)) {
		val = 1;
	}

	return val;
};

var encodeByteLWE = (key, samples, mod, msg) => {
	let out = [];

	for (let i = 0; i < 8; i++) {
		let bit = (msg & (1 << i)) >> i;
		out[i] = encodeBitLWE(key, samples, mod, bit);
	}

	return out;
}

var decodeByteLWE = (key, msg, mod) => {
	let out = 0;

	for (let i = 0; i < 8; i++) {
		out |= decodeBitLWE(key, msg[i], mod) << i;
	}

	return out;
}

var autogenPrivateLWE = () => {
	return genPrivateLWE(privSizeLWE, modulusLWE);
}

var autogenPublicLWE = (key) => {
	return genPublicLWE(key, pubSizeLWE, modulusLWE, errorLWE);
}

var autoencodeByteLWE = (pubKey, msg) => {
	return encodeByteLWE(pubKey, samplesLWE, modulusLWE, msg);
}

var autodecodeByteLWE = (privKey, msg) => {
	return decodeByteLWE(privKey, msg, modulusLWE);
}

var autoencodeStrLWE = (pubKey, msg) => {
	return encodeByteLWE(pubKey, samplesLWE, modulusLWE, msg);
}

var autodecodeStrLWE = (privKey, msg) => {
	return decodeByteLWE(privKey, msg, modulusLWE);
}

var rollkey = (rollingKey) => {
	let hash = 0;
	for (let i = 0; i < rollingKey.length - 1; i++) {
		hash += parseInt(rollingKey[i]);
		rollingKey[i] = rollingKey[i + 1];
	}
	rollingKey[rollingKey.length - 1] = hash % 256;

	return rollingKey;
};
var lwe_cbc = (str) => {
	let tmpkey = localStorage.getItem("key").split(",").slice();
	let out = "";
	for (let i = 0; i < str.length; i++) {
		tmpkey = rollkey(tmpkey.slice());
		out += String.fromCharCode(str.charCodeAt(i) ^ tmpkey[tmpkey.length - 1]);
	}

	return out;
};
var lwe_encrypt = (str) => {
	return btoa(lwe_cbc(str));
};
var lwe_decrypt = (str) => {
	return lwe_cbc(atob(str));
};
