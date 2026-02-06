
const express = require('express');
const path = require('path');
const { stream } = require('./ollama.cjs');
const app = express();
const PORT = process.env.PORT || 4640;

app.use(express.json());
app.use(express.static(path.join(__dirname, '..')));

app.post('/api/ollama/chat', (req, res) => stream(req.body, res));

app.get('*', (_req, res) => res.sendFile(path.join(__dirname, '..', 'index.html')));

app.listen(PORT, () => console.log(`🛰 Crypto Generator Plugin http://localhost:${PORT}`));
