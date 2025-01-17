<?php

require_once __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use Workerman\Connection\TcpConnection;

// Membuat server WebSocket pada port 9000
$ws_worker = new Worker("websocket://0.0.0.0:9000");

// Menentukan jumlah proses
$ws_worker->count = 4;

// Daftar klien yang terhubung
$clients = [];

// Path file untuk menyimpan daftar klien yang terhubung
$clients_file = 'clients.txt';

// Event saat koneksi baru dibuka
$ws_worker->onConnect = function(TcpConnection $connection) use (&$clients, $clients_file) {
    echo "New connection: {$connection->id}\n";
    $clients[$connection->id] = $connection;

    // Simpan ID klien ke dalam file jika file belum ada
    if (!file_exists($clients_file)) {
        file_put_contents($clients_file, '');
    }

    // Menambahkan ID klien ke file (append)
    // Membaca isi file dan menambahkan ID klien baru
    $existing_clients = file($clients_file, FILE_IGNORE_NEW_LINES);  // Membaca ID klien yang sudah ada
    $existing_clients[] = $connection->id;  // Menambahkan ID klien baru
    file_put_contents($clients_file, implode(PHP_EOL, $existing_clients) . PHP_EOL); // Menyimpan kembali daftar klien ke file
};

// Event saat menerima pesan dari klien
$ws_worker->onMessage = function(TcpConnection $connection, $data) use (&$clients) {
    echo "Received message: $data\n";

    // Logika untuk mengirim pesan ke klien
    $response = ($data === "trigger") ? "ON" : "OFF";

    // Kirim pesan ke semua klien
    foreach ($clients as $client) {
        $client->send($response);
    }
    echo "Response sent: $response\n";
};

// Event saat koneksi ditutup
$ws_worker->onClose = function(TcpConnection $connection) use (&$clients, $clients_file) {
    echo "Connection closed: {$connection->id}\n";
    unset($clients[$connection->id]);

    // Hapus ID klien dari file
    $existing_clients = file($clients_file, FILE_IGNORE_NEW_LINES);  // Membaca isi file
    $existing_clients = array_filter($existing_clients, function($client_id) use ($connection) {
        return $client_id != $connection->id;
    });
    file_put_contents($clients_file, implode(PHP_EOL, $existing_clients) . PHP_EOL); // Menyimpan kembali daftar klien ke file
};

// Menjalankan server
Worker::runAll();
