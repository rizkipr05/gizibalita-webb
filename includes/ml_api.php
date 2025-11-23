<?php

/**
 * Panggil layanan ML (Python) untuk memprediksi status gizi.
 *
 * @param int         $umur_bulan      Umur balita (bulan)
 * @param string      $jenis_kelamin   'L' atau 'P'
 * @param float       $berat_badan     kg  (saat ini belum dipakai model, tapi bisa kamu teruskan ke Python kalau mau)
 * @param float       $tinggi_badan    cm  (sama seperti berat_badan)
 * @param float|null  $lingkar_lengan  cm (boleh null)
 *
 * @return array|null
 */
function ml_predict_status_gizi(
    int $umur_bulan,
    string $jenis_kelamin,
    float $berat_badan,
    float $tinggi_badan,
    ?float $lingkar_lengan = null
): ?array {
    $url = "http://127.0.0.1:5000/predict";

    // ⚠️ Di sini kita SESUAIKAN dengan yang dicari Python:
    //   'Umur', 'JK', 'BB/U', 'TB/U', 'LILA', 'ZS BB/U', 'ZS TB/U', 'ZS BB/TB'
    // Kolom yang tidak kamu input (BB/U, TB/U, ZS) sementara diisi default.
    $payload = [
        "Umur"      => (string)$umur_bulan,                  // Python pakai regex angka, jadi cukup "24"
        "JK"        => $jenis_kelamin,                       // 'L' / 'P'
        "BB/U"      => "Normal",                             // default sementara
        "TB/U"      => "Normal",                             // default sementara
        "LILA"      => $lingkar_lengan !== null
                            ? (string)$lingkar_lengan
                            : "0",
        "ZS BB/U"   => "0",                                  // default
        "ZS TB/U"   => "0",                                  // default
        "ZS BB/TB"  => "0",                                  // default
        // kalau kamu nanti mau pakai berat_badan / tinggi_badan,
        // bisa tambahkan di sini dengan nama kolom persis seperti di CSV.
    ];

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $err = curl_error($ch);
        error_log("ML API cURL error: " . $err);

        return [
            'error' => 'Tidak dapat menghubungi layanan ML: ' . $err,
        ];
    }

    // (curl_close dibiarkan tidak dipanggil supaya Intelephense tidak protes "deprecated")

    $decoded = json_decode($response, true);

    if (!is_array($decoded)) {
        error_log("ML API invalid JSON: " . $response);

        return [
            'error' => 'Respon tidak valid dari layanan ML.',
        ];
    }

    // Kalau di Python kamu pakai format { success, class_label, class_id, error }
    if (isset($decoded['success']) && $decoded['success'] === false && isset($decoded['error'])) {
        return [
            'error' => $decoded['error'],
        ];
    }

    // Normalisasi nama field: class_label -> status_gizi
    if (isset($decoded['class_label']) && !isset($decoded['status_gizi'])) {
        $decoded['status_gizi'] = $decoded['class_label'];
    }

    return $decoded;
}
