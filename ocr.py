from flask import Flask, request, jsonify
from flask_cors import CORS

app = Flask(__name__)
CORS(app)  # Mengizinkan akses dari Laravel

@app.route('/process-ocr', methods=['POST'])
def process_ocr():
    if 'file' not in request.files:
        return jsonify({'error': 'No file uploaded'}), 400

    file = request.files['file']
    print(f"Received file: {file.filename}")

    # Dummy response
    response_data = {
        "type": "Surat Tugas",
        "nomor_surat": "790/UN1/SV/TU/2025",
        "tanggal": "2025-07-25",
        "pengirim": "Universitas Gadjah Mada",
        "penerima": "HRD Angkasa Pura II",
        "alamat": "Jl. Kaliurang KM 10, Yogyakarta",
        "isi_surat": "Dengan hormat, kami mengajukan permohonan magang bagi mahasiswa kami yang"
    }

    return jsonify(response_data), 200

if __name__ == '__main__':
    app.run(debug=True, port=5000)
