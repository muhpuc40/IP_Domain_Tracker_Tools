const http = require('http');
const net = require('net');
const cors = require('cors');
const express = require('express');
const app = express();

// Enable CORS
app.use(cors());

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

// Route to check the port status
app.get('/', async (req, res) => {
    const ip = req.query.ip;
    const port = parseInt(req.query.port, 10);

    if (!ip || isNaN(port)) {
        res.status(400).json({ error: 'Invalid IP or port' });
        console.log(`\x1b[31m[ERROR]\x1b[0m Invalid request received: IP - ${ip}, Port - ${port}`);
        return;
    }

    // Log the received request in styled format
    console.log(`\n\x1b[36m[REQUEST RECEIVED]\x1b[0m Checking IP: \x1b[33m${ip}\x1b[0m, Port: \x1b[33m${port}\x1b[0m`);

    const isOpen = await checkPort(ip, port);

    // Styled result based on the port status
    if (isOpen) {
        console.log(`\x1b[32m[RESULT]\x1b[0m Port ${port} on IP ${ip} is \x1b[32mOPEN\x1b[0m.`);
    } else {
        console.log(`\x1b[31m[RESULT]\x1b[0m Port ${port} on IP ${ip} is \x1b[31mCLOSED\x1b[0m.`);
    }

    res.json({ open: isOpen });
});

// Start the server with a styled console log
const PORT = 3000;
app.listen(PORT, () => {
    console.log(`\n\x1b[34m............................................\x1b[0m`);
    console.log(`\x1b[34mServer is running on http://localhost:${PORT}\x1b[0m`);
    console.log(`\x1b[34m............................................\x1b[0m`);
});
