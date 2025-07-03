var QR4 = {};

QR4.field = {};
QR4.field.poly = 0b100011101;
QR4.field.roots = [
	1, 2, 4, 8, 16, 32, 64, 128, 29, 58, 116, 232, 205, 135, 19, 38, 
	76, 152, 45, 90, 180, 117, 234, 201, 143, 3, 6, 12, 24, 48, 96, 192, 
	157, 39, 78, 156, 37, 74, 148, 53, 106, 212, 181, 119, 238, 193, 159, 35, 
	70, 140, 5, 10, 20, 40, 80, 160, 93, 186, 105, 210, 185, 111, 222, 161, 
	95, 190, 97, 194, 153, 47, 94, 188, 101, 202, 137, 15, 30, 60, 120, 240, 
	253, 231, 211, 187, 107, 214, 177, 127, 254, 225, 223, 163, 91, 182, 113, 226, 
	217, 175, 67, 134, 17, 34, 68, 136, 13, 26, 52, 104, 208, 189, 103, 206, 
	129, 31, 62, 124, 248, 237, 199, 147, 59, 118, 236, 197, 151, 51, 102, 204, 
	133, 23, 46, 92, 184, 109, 218, 169, 79, 158, 33, 66, 132, 21, 42, 84, 
	168, 77, 154, 41, 82, 164, 85, 170, 73, 146, 57, 114, 228, 213, 183, 115, 
	230, 209, 191, 99, 198, 145, 63, 126, 252, 229, 215, 179, 123, 246, 241, 255, 
	227, 219, 171, 75, 150, 49, 98, 196, 149, 55, 110, 220, 165, 87, 174, 65, 
	130, 25, 50, 100, 200, 141, 7, 14, 28, 56, 112, 224, 221, 167, 83, 166, 
	81, 162, 89, 178, 121, 242, 249, 239, 195, 155, 43, 86, 172, 69, 138, 9, 
	18, 36, 72, 144, 61, 122, 244, 245, 247, 243, 251, 235, 203, 139, 11, 22, 
	44, 88, 176, 125, 250, 233, 207, 131, 27, 54, 108, 216, 173, 71, 142
];
QR4.field.invroots = QR4.field.roots.slice();
QR4.field.roots.forEach((v, i) => QR4.field.invroots[v] = i);
QR4.blocksize = 32;
QR4.blocks = 2;
QR4.eccsize = 18;
QR4.errc = { c: QR4.blocksize + QR4.eccsize, k: QR4.blocksize, r: 9 };
QR4.genpoly = {};
QR4.genpoly.roots = [
	0, 215, 234, 158, 94, 184, 97, 118, 170, 79, 187, 152, 148, 252, 179, 5, 98, 96, 153
];
QR4.genpoly.poly = QR4.genpoly.roots.slice();
QR4.genpoly.poly.forEach((v, i, a) => a[i] = QR4.field.roots[v]);
QR4.modules = 33;

QR4.multiply = (a, b) => {
	if (a == 0 || b == 0) {
		return 0;
	}
	return QR4.field.roots[(QR4.field.invroots[a] + QR4.field.invroots[b]) % QR4.field.roots.length];
}

QR4.divide = (a, b) => {
	if (a == 0 || b == 0) {
		return 0;
	}
	return QR4.field.roots[(QR4.field.invroots[a] - QR4.field.invroots[b] + QR4.field.roots.length) % QR4.field.roots.length];
}

QR4.reedSolomon = (polyIn) => {
	let poly = [];
	for (let i = 0; i < QR4.blocksize; i++) {
		poly[i] = polyIn[i];
	}
	for (let i = 0; i < QR4.eccsize; i++) {
		poly[i + QR4.blocksize] = 0;
	}

	for (let i = 0; i < QR4.blocksize; i++) {
		let multiplier = QR4.divide(poly[i], QR4.genpoly.poly[0]);
		for (let j = 0; j < QR4.eccsize + 1; j++) {
			let difference = QR4.multiply(QR4.genpoly.poly[j], multiplier);
			poly[i + j] ^= difference;
		}
	}
	for (let i = 0; i < QR4.eccsize; i++) {
		poly[i] = poly[i + QR4.blocksize];
	}

	return poly;
}

QR4.encodeStr = (str) => {
	let out = "0100"; // byte encoding

	out += str.length.toString(2).padStart(8, '0'); // length

	for (let i = 0; i < str.length; i++) {
		out += str.charCodeAt(i).toString(2).padStart(8, '0'); // data
	}

	out += "0000"; // terminator

	for (let i = 0; i < QR4.blocksize * QR4.blocks * 8 - out.length; i++) {
		if (i % 2 == 0) {
			out += "11101100"; // pad 0xec
		}
		else {
			out += "00010001"; // pad 0x11
		}
	}

	let blocks = [[], []];

	for (let i = 0; i < QR4.eccsize; i++) {
		blocks[0][i + QR4.blocksize] = 0;
		blocks[1][i + QR4.blocksize] = 0;
	}

	for (let i = 0; i < 32; i++) {
		blocks[0][i] = parseInt(out.substring(i * 8, i * 8 + 8), 2);
		blocks[1][i] = parseInt(out.substring(QR4.blocksize * 8 + i * 8, QR4.blocksize * 8 + i * 8 + 8), 2);
	}

	for (let k = 0; k < QR4.blocks; k++) {
		let ecc = QR4.reedSolomon(blocks[k]);
		for (let i = 0; i < QR4.eccsize; i++) {
			blocks[k][i + QR4.blocksize] = ecc[i];
		}
	}

	out = "";

	for (let i = 0; i < 100; i++) {
		out += blocks[i % 2][Math.floor(i / 2)].toString(2).padStart(8, '0');
	}

	return out;
};

QR4.encode = (str) => {
	let data = [];

	for (let x = 0; x < QR4.modules; x++) {
		data[x] = [];

		for (let y = 0; y < QR4.modules; y++) {
			data[x][y] = 0;
		}
	}

	let finder = "0,0;1,0;2,0;3,0;4,0;5,0;6,0;0,1;6,1;0,2;2,2;3,2;4,2;6,2;0,3;2,3;3,3;4,3;6,3;0,4;2,4;3,4;4,4;6,4;0,5;6,5;0,6;1,6;2,6;3,6;4,6;5,6;6,6".split(";");
	for (let i = 0; i < finder.length; i++) {
		data[finder[i].split(",")[0]][finder[i].split(",")[1]] = 1;
		data[QR4.modules - finder[i].split(",")[0] - 1][finder[i].split(",")[1]] = 1;
		data[finder[i].split(",")[0]][QR4.modules - finder[i].split(",")[1] - 1] = 1;
	}
	let finder2 = "5,5;5,6;5,7;5,8;5,9;6,5;6,9;7,5;7,7;7,9;8,5;8,9;9,5;9,6;9,7;9,8;9,9".split(";");
	for (let i = 0; i < finder2.length; i++) {
		data[QR4.modules - finder2[i].split(",")[0]][QR4.modules - finder2[i].split(",")[1]] = 1;
	}
	let timer = "6,8;6,10;6,12;6,14;6,16;6,18;6,20;6,22;6,24".split(";");
	for (let i = 0; i < timer.length; i++) {
		data[timer[i].split(",")[0]][timer[i].split(",")[1]] = 1;
		data[timer[i].split(",")[1]][timer[i].split(",")[0]] = 1;
	}

	let bin = QR4.encodeStr(str);

	for (let i = 0; i < bin.length; i++) {
		// Yeah this is disgraceful but it has to follow a set pattern
		if (i < (QR4.modules - 9) * 2) {
			let q = i;
			data[QR4.modules - 1 - Math.floor(q % 2)][QR4.modules - 1 - Math.floor(q / 2)] = Number(bin[i]);
		}
		else if (i < (QR4.modules - 9) * 4) {
			let q = i - (QR4.modules - 9) * 2;
			data[QR4.modules - 3 - Math.floor(q % 2)][9 + Math.floor(q / 2)] = Number(bin[i]);
		}
		else if (i < (QR4.modules - 9) * 4 + 8) {
			let q = i - (QR4.modules - 9) * 4;
			data[QR4.modules - 5 - Math.floor(q % 2)][QR4.modules - 1 - Math.floor(q / 2)] = Number(bin[i]);
		}
		else if (i < (QR4.modules - 9) * 6 - 10) {
			let q = i - (QR4.modules - 9) * 4 + 10;
			data[QR4.modules - 5 - Math.floor(q % 2)][QR4.modules - 1 - Math.floor(q / 2)] = Number(bin[i]);
		}
		else if (i < (QR4.modules - 9) * 8 - 28) {
			let q = i - (QR4.modules - 9) * 6 + 10;
			data[QR4.modules - 7 - Math.floor(q % 2)][9 + Math.floor(q / 2)] = Number(bin[i]);
		}
		else if (i < (QR4.modules - 9) * 8 - 20) {
			let q = i - (QR4.modules - 9) * 6 + 20;
			data[QR4.modules - 7 - Math.floor(q % 2)][9 + Math.floor(q / 2)] = Number(bin[i]);
		}
		else if (i < (QR4.modules - 9) * 8 - 12) {
			let q = i - (QR4.modules - 9) * 8 + 20;
			data[QR4.modules - 9 - Math.floor(q % 2)][QR4.modules - 1 - Math.floor(q / 2)] = Number(bin[i]);
		}
		else if (i < (QR4.modules - 9) * 8 - 7) {
			let q = i - (QR4.modules - 9) * 8 + 16;
			data[QR4.modules - 10][QR4.modules - 1 - q] = Number(bin[i]);
		}
		// Literally the entire middle of the QR code
		else if (i < (QR4.modules - 9) * 28 + 7) {
			let q = i - (QR4.modules - 9) * 8 + 25;
			let x = QR4.modules - 9 - Math.floor(q % 2) - Math.floor(q / (QR4.modules - 1) / 2) * 2;
			let y = Math.floor(q / 2) % (QR4.modules - 1);
			if (Math.floor(q / ((QR4.modules - 1) * 2)) % 2 == 0) {
				if (y > QR4.modules - 8) {
					y += 1;
				}
				data[x][QR4.modules - 1 - y] = Number(bin[i]);
			}
			else {
				if (y > 5) {
					y += 1;
				}
				data[x][y] = Number(bin[i]);
			}
		}
		else {
			let q = i - (QR4.modules - 9) * 28 - 7;
			let x = 8 - Math.floor(q % 2) - Math.floor(q / (QR4.modules - 17) / 2) * 2;
			let y = Math.floor(q / 2) % (QR4.modules - 17);
			if (q > (QR4.modules - 17) * 2) {
				x -= 1;
			}
			if (Math.floor(q / ((QR4.modules - 17) * 2)) % 2 == 0) {
				data[x][QR4.modules - 9 - y] = Number(bin[i]);
			}
			else {
				data[x][y + 9] = Number(bin[i]);
			}
		}
	}
	// TODO timing patterns

	return data
}

QR4.code = (str, scale) => {
	let canvas = document.createElement("canvas");
	canvas.width = QR4.modules * scale;
	canvas.height = QR4.modules * scale;

	let ctx = canvas.getContext("2d");

	let data = QR4.encode(str);

	for (let x = 0; x < QR4.modules; x++) {
		for (let y = 0; y < QR4.modules; y++) {
			if (data[x][y] != 0) {
				ctx.fillRect(x * scale, y * scale, scale, scale);
			}
		}
	}

	return canvas;
};

let teststr = "otpauth://totp/entQngle:test?digits=8&secret=MFRGGZDFMZTWQ2I";

let output = QR4.encodeStr(teststr);

document.body.innerHTML = output;

let testVector = "01000011100101101100011001110110111101111001011101000111010001110000011000110011000101111101001101010111100000100100011001100111100000110011011010100010010101101111001000110111111101110010011001000110010101111111011101000011010001111101010000000010110101001111011001100101010101100010010011100111011101000100010101110101000101101010010011100110010001000111011001100100110001101101010101010011101001011010011101000101010001100111010101010111000100110011011100100100010000111001000011110110111011000100011000010001001111000001000001011110100011011111000000101101000001110001010001101001111000000010101011111101111001110010000111101011111010010011101000110111110100001001111001000110101100011000001110100000101011111110001010000000001111011110111001100000011000000101110001001100010100011001111011110101";

if (output == testVector) {
	document.body.innerHTML += "<br><br>Matches test vector";
}
else {
	document.body.innerHTML += `<br>${testVector}<br>`;
	alert("No match");
	for (let i = 0; i < Math.min(output.length, testVector.length); i++) {
		if (output[i] == testVector[i]) {
			document.body.innerHTML += "&nbsp;";
		}
		else {
			document.body.innerHTML += "*";
		}
	}
}

document.body.appendChild(QR4.code(teststr, 10));
