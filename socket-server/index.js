require('dotenv').config();

const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');

const PORT = process.env.PORT || 3001;
const MAX_CAPACITY = parseInt(process.env.MAX_CAPACITY || '100', 10);

const app = express();
app.use(cors());
app.use(express.json());

const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"]
  }
});

// Basit Doluluk Oranı Durumu (Memory'de tutuluyor)
let currentOccupancy = 0;

io.on('connection', (socket) => {
  console.log('Kullanıcı bağlandı:', socket.id);

  // Yeni bağlanana mevcut doluluk durumunu gönder
  socket.emit('occupancy_update', {
    count: currentOccupancy,
    percentage: (currentOccupancy / MAX_CAPACITY) * 100
  });

  socket.on('disconnect', () => {
    console.log('Kullanıcı ayrıldı:', socket.id);
  });
});

// ─────────────────────────────────────────────
// LARAVEL'DEN GELEN WEBHOOK'LAR
// ─────────────────────────────────────────────

// ROTA 1: QR Kod okundu → Doluluk artar
app.post('/api/entry', (req, res) => {
  currentOccupancy++;

  // 45 dakika sonra otomatik düşürme (simülasyon)
  setTimeout(() => {
    if (currentOccupancy > 0) currentOccupancy--;
    io.emit('occupancy_update', {
      count: currentOccupancy,
      percentage: (currentOccupancy / MAX_CAPACITY) * 100
    });
  }, 45 * 60 * 1000);

  io.emit('occupancy_update', {
    count: currentOccupancy,
    percentage: (currentOccupancy / MAX_CAPACITY) * 100
  });
  res.json({ success: true, occupancy: currentOccupancy });
});

// ROTA 2: Admin panelinden yeni duyuru yayınlandı
app.post('/api/announcement-posted', (req, res) => {
  io.emit('new_announcement', {
    message: 'Yeni bir duyuru yayınlandı!',
    title: req.body.title
  });
  res.json({ success: true });
});

// ROTA 3: Admin duyuru sildi → Frontend listeden kaldırsın
app.post('/api/announcement-deleted', (req, res) => {
  io.emit('announcement_deleted', { id: req.body.id });
  res.json({ success: true });
});

// ROTA 4: Admin menü ekledi/güncelledi → Kullanıcı sayfası yenilensin
app.post('/api/menu-updated', (req, res) => {
  io.emit('menu_updated', { message: 'Menü güncellendi.' });
  res.json({ success: true });
});

// ROTA 5: Kullanıcı bakiye yükledi → Anlık bakiye güncellemesi
app.post('/api/balance-updated', (req, res) => {
  const { user_id, new_balance } = req.body;
  if (!user_id || new_balance === undefined) {
    return res.status(400).json({ error: 'user_id ve new_balance gerekli.' });
  }
  io.emit('balance_updated', { user_id, new_balance });
  res.json({ success: true });
});

// ROTA 6: Admin manuel doluluk sıfırlama
app.post('/api/reset', (req, res) => {
  currentOccupancy = 0;
  io.emit('occupancy_update', { count: 0, percentage: 0 });
  res.json({ success: true });
});

// ─────────────────────────────────────────────
// SUNUCUYU BAŞLAT
// ─────────────────────────────────────────────
server.listen(PORT, () => {
  console.log(`Socket sunucusu ${PORT} portunda çalışıyor 🚀`);
  console.log(`Yemekhane kapasitesi: ${MAX_CAPACITY} kişi`);
});