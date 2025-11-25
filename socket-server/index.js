const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');

const app = express();
app.use(cors());
app.use(express.json());

const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: "*", // Güvenlik için production'da frontend URL'ini verin
    methods: ["GET", "POST"]
  }
});

// Basit Doluluk Oranı Simülasyonu (Veritabanından da çekilebilir ama memory'de tutalım)
let currentOccupancy = 0;
const MAX_CAPACITY = 100; // Yemekhane kapasitesi

io.on('connection', (socket) => {
  console.log('Kullanıcı bağlandı:', socket.id);

  // Yeni bağlanana mevcut durumu gönder
  socket.emit('occupancy_update', { count: currentOccupancy, percentage: (currentOccupancy / MAX_CAPACITY) * 100 });

  socket.on('disconnect', () => {
    console.log('Kullanıcı ayrıldı:', socket.id);
  });
});

// Laravel'den gelen webhook (Biri yemek yediğinde buraya istek atacağız)
app.post('/api/entry', (req, res) => {
  currentOccupancy++;
  
  // 45 dakika sonra otomatik düş (Yemek yeme süresi ortalaması)
  setTimeout(() => {
    if(currentOccupancy > 0) currentOccupancy--;
    io.emit('occupancy_update', { count: currentOccupancy, percentage: (currentOccupancy / MAX_CAPACITY) * 100 });
  }, 45 * 60 * 1000); 

  io.emit('occupancy_update', { count: currentOccupancy, percentage: (currentOccupancy / MAX_CAPACITY) * 100 });
  res.json({ success: true, occupancy: currentOccupancy });
});

// Admin manuel reset
app.post('/api/reset', (req, res) => {
  currentOccupancy = 0;
  io.emit('occupancy_update', { count: 0, percentage: 0 });
  res.json({ success: true });
});

server.listen(3001, () => {
  console.log('Socket sunucusu 3001 portunda çalışıyor 🚀');
});