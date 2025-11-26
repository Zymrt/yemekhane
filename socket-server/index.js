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
    origin: "*", // Laravel ve Nuxt'ın çalıştığı adresler için güvenlik ayarı
    methods: ["GET", "POST"]
  }
});

// Basit Doluluk Oranı Durumu (Memory'de tutuluyor)
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

// --- LARAVEL'DEN GELEN WEBHOOK'LAR ---

// ROTA 1: QR Kod okundu ve yemek yendi (Doluluk artar)
app.post('/api/entry', (req, res) => {
  currentOccupancy++;
  
  // 45 dakika sonra otomatik düşürme (simülasyon)
  setTimeout(() => {
    if(currentOccupancy > 0) currentOccupancy--;
    io.emit('occupancy_update', { count: currentOccupancy, percentage: (currentOccupancy / MAX_CAPACITY) * 100 });
  }, 45 * 60 * 1000); 

  // Tüm bağlı istemcilere yeni durumu yay
  io.emit('occupancy_update', { count: currentOccupancy, percentage: (currentOccupancy / MAX_CAPACITY) * 100 });
  res.json({ success: true, occupancy: currentOccupancy });
});

// ROTA 2: Admin panelinden yeni duyuru yayınlandı
app.post('/api/announcement-posted', (req, res) => {
  // Tüm bağlı istemcilere yeni duyuru sinyali gönder
  io.emit('new_announcement', { 
      message: 'Yeni bir duyuru yayınlandı!', 
      title: req.body.title 
  });
  
  res.json({ success: true });
});

// ROTA 3: Admin manuel reset
app.post('/api/reset', (req, res) => {
  currentOccupancy = 0;
  io.emit('occupancy_update', { count: 0, percentage: 0 });
  res.json({ success: true });
});

// --- SUNUCUYU BAŞLAT ---
server.listen(3001, () => {
  console.log('Socket sunucusu 3001 portunda çalışıyor 🚀');
});