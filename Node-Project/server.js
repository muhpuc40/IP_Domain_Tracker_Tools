// server.js
const http = require('http');
const net = require('net');
const url = require('url');

// Function to check if a port is open
function checkPort(ip, port) {
    return new Promise((resolve) => {
        const socket = new net.Socket();
        const timeout = 3000; // timeout in milliseconds

        socket.setTimeout(timeout);
        socket.on('connect', () => {
            socket.destroy();
            resolve(true); // Port is open
        });
        socket.on('timeout', () => {
            socket.destroy();
            resolve(false); // Port is closed (timeout)
        });
        socket.on('error', () => {
            resolve(false); // Port is closed (error)
        });
        socket.connect(port, ip);
    });
}

// Create an HTTP server
const server = http.createServer(async (req, res) => {
    const query = url.parse(req.url, true).query;
    const ip = query.ip;
    const port = parseInt(query.port, 10);

    if (!ip || isNaN(port)) {
        res.writeHead(400, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ error: 'Invalid IP or port' }));
        return;
    }

    const isOpen = await checkPort(ip, port);
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ open: isOpen }));
});

// Start the server
const PORT = 3000;
server.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});
