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
	44, 88, 176, 125, 250, 233, 207, 131, 27, 54, 108, 216, 173, 71, 142, 1
];
QR4.field.invroots = QR4.field.roots.slice();
QR4.field.roots.forEach((v, i, a) => a[v] = i);
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

QR4.reedSolomon = (polyIn) => {
	let poly = [];
	for (let i = 0; i < QR4.eccsize; i++) {
		poly[i] = 0;
	}
	for (let i = 0; i < QR4.blocksize; i++) {
		poly[i + QR4.eccsize] = polyIn[i];
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

let teststr = "otpauth://totp/entQngle:test?digits=8&secret=MFRGGZDFMZTWQ2I";

let output = QR4.encodeStr(teststr);

document.body.innerHTML = output;
console.log(output);

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
