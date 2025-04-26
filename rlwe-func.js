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
		out += vec[i] + ("&nbsp;".repeat(15 - mat[i][j].toString().length));
	}

	return out;
};
