#include <ESP8266WiFi.h>
#include <WebSocketsClient.h>

const char* ssid = "NFC0X";  // Ganti dengan SSID WiFi Anda
const char* password = "k4m1nd0!@#";  // Ganti dengan password WiFi Anda

WebSocketsClient webSocket;
int ledPin = D8;  // Pin untuk LED

void webSocketEvent(WStype_t type, uint8_t * payload, size_t length) {
    switch(type) {
        case WStype_DISCONNECTED:
            Serial.println("WebSocket Disconnected");
            break;
        case WStype_CONNECTED:
            Serial.println("WebSocket Connected");
            webSocket.sendTXT("Hello from Arduino");  // Kirim pesan ke server saat terkoneksi
            break;
        case WStype_TEXT:
            Serial.printf("Message: %s\n", payload);
            if (strcmp((char*)payload, "ON") == 0) {
                digitalWrite(ledPin, HIGH);  // Nyalakan LED jika menerima "ON"
            } else if (strcmp((char*)payload, "OFF") == 0) {
                digitalWrite(ledPin, LOW);  // Matikan LED jika menerima "OFF"
            } else {
                // Tampilkan ID client atau informasi lain yang diterima
                Serial.printf("Received: %s\n", payload);  // Menampilkan pesan lain dari server
            }
            break;
    }
}

void setup() {
    Serial.begin(115200);  // Mulai komunikasi serial untuk debugging
    pinMode(ledPin, OUTPUT);  // Set pin LED sebagai output

    // Menghubungkan ke WiFi
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("WiFi connected");

    // Menghubungkan ke WebSocket server (ganti dengan IP server Anda)
    webSocket.begin("192.168.10.199", 9000, "/");

    // Menetapkan event handler untuk WebSocket
    webSocket.onEvent(webSocketEvent);
}

void loop() {
    webSocket.loop();  // Loop untuk menerima dan mengirim pesan melalui WebSocket
}
