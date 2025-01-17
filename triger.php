<?php

require_once __DIR__ . '/vendor/autoload.php';

use WebSocket\Client;

// Membaca ID klien dari file
$clients = file('clients.txt', FILE_IGNORE_NEW_LINES);

// Jika ada klien yang terhubung dan menerima perintah trigger
if (isset($argv[1]) && $argv[1] === 'trigger' && !empty($clients)) {
    echo "Trigger activated: Sending 'ON' to all clients\n";

    // Loop untuk mengirim pesan 'ON' ke semua klien yang ada di dalam file
    foreach ($clients as $client_id) {
        echo "Sending 'ON' to client ID: $client_id\n";

        try {
            // Inisialisasi WebSocket Client untuk mengirim pesan ke server
            $client = new Client("ws://192.168.10.199:9000"); // Ganti dengan alamat server WebSocket Anda

            // Kirim pesan 'ON' ke server yang akan diteruskan ke Arduino
            $client->send("trigger");

            // Tunggu dan terima pesan dari server
            $response = $client->receive();
            echo "Response from server: $response\n";
        } catch (Exception $e) {
            echo "Error sending message to WebSocket: " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "No clients connected or invalid argument\n";
}
