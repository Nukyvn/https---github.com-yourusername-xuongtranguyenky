const express = require('express');
const mysql = require('mysql2/promise');
const multer = require('multer');
const cors = require('cors');
const jwt = require('jsonwebtoken');
const bcrypt = require('bcryptjs');
const sharp = require('sharp');
const path = require('path');
const fs = require('fs');
require('dotenv').config();

const app = express();
app.use(cors());
app.use(express.json());
app.use('/uploads', express.static('public/uploads'));

// MySQL Connection
const pool = mysql.createPool({
  host: process.env.DB_HOST,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  database: 'xuongtranguyenky'
});

// Multer for Image Uploads
const storage = multer.diskStorage({
  destination: (req, file, cb) => cb(null, 'public/uploads'),
  filename: (req, file, cb) => cb(null, Date.now() + path.extname(file.originalname))
});
const upload = multer({ storage });

// Compress Image
const compressImage = async (file) => {
  const outputPath = `public/uploads/${Date.now()}.jpg`;
  await sharp(file.path)
    .resize({ width: 1200 })
    .jpeg({ quality: 80 })
    .toFile(outputPath);
  fs.unlinkSync(file.path);
  return outputPath.replace('public', '');
};

// Format VNĐ
const formatVND = (number) => number.toLocaleString('vi-VN', { style: 'currency', currency: 'VND', minimumFractionDigits: 0 });

// Auth Middleware
const authMiddleware = async (req, res, next) => {
  const token = req.headers.authorization?.split(' ')[1];
  if (!token) return res.status(401).json({ message: 'Chưa đăng nhập' });
  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    req.user = decoded;
    next();
  } catch (error) {
    res.status(401).json({ message: 'Token không hợp lệ' });
  }
};

// Login
app.post('/api/login', async (req, res) => {
  const { username, password } = req.body;
  const [users] = await pool.query('SELECT * FROM users WHERE username = ?', [username]);
  if (users.length === 0 || !await bcrypt.compare(password, users[0].password)) {
    return res.status(401).json({ message: 'Sai thông tin đăng nhập' });
  }
  const token = jwt.sign({ id: users[0].id }, process.env.JWT_SECRET, { expiresIn: '1h' });
  res.json({ token });
});

// Products
app.get('/api/products', async (req, res) => {
  const [rows] = await pool.query('SELECT * FROM products');
  res.json(rows.map(row => ({ ...row, price: formatVND(row.price) })));
});

app.post('/api/products', authMiddleware, upload.single('image'), async (req, res) => {
  const { name, description, price } = req.body;
  const image = req.file ? await compressImage(req.file) : null;
  await pool.query('INSERT INTO products (name, description, image, price) VALUES (?, ?, ?, ?)', [name, description, image, parseInt(price)]);
  res.json({ message: 'Thêm sản phẩm thành công' });
});

app.put('/api/products/:id', authMiddleware, upload.single('image'), async (req, res) => {
  const { id } = req.params;
  const { name, description, price } = req.body;
  const image = req.file ? await compressImage(req.file) : req.body.image;
  await pool.query('UPDATE products SET name = ?, description = ?, image = ?, price = ? WHERE id = ?', [name, description, image, parseInt(price), id]);
  res.json({ message: 'Cập nhật sản phẩm thành công' });
});

app.delete('/api/products/:id', authMiddleware, async (req, res) => {
  const { id } = req.params;
  await pool.query('DELETE FROM products WHERE id = ?', [id]);
  res.json({ message: 'Xóa sản phẩm thành công' });
});

// Similar CRUD for gallery, articles, videos, testimonials, contact_info (simplified)
app.get('/api/gallery', async (req, res) => {
  const [rows] = await pool.query('SELECT * FROM gallery');
  res.json(rows);
});

app.post('/api/gallery', authMiddleware, upload.single('image'), async (req, res) => {
  const { title, description } = req.body;
  const image = req.file ? await compressImage(req.file) : null;
  await pool.query('INSERT INTO gallery (title, image, description) VALUES (?, ?, ?)', [title, image, description]);
  res.json({ message: 'Thêm hình ảnh thành công' });
});

app.get('/api/articles', async (req, res) => {
  const [rows] = await pool.query('SELECT * FROM articles');
  res.json(rows);
});

app.post('/api/articles', authMiddleware, upload.single('image'), async (req, res) => {
  const { title, type, content } = req.body;
  const image = req.file ? await compressImage(req.file) : null;
  await pool.query('INSERT INTO articles (type, title, content, image) VALUES (?, ?, ?, ?)', [type, title, content, image]);
  res.json({ message: 'Thêm bài viết/tin tức thành công' });
});

app.get('/api/videos', async (req, res) => {
  const [rows] = await pool.query('SELECT * FROM videos');
  res.json(rows);
});

app.post('/api/videos', authMiddleware, upload.single('thumbnail'), async (req, res) => {
  const { title, url, description } = req.body;
  const thumbnail = req.file ? await compressImage(req.file) : null;
  await pool.query('INSERT INTO videos (title, url, thumbnail, description) VALUES (?, ?, ?, ?)', [title, url, thumbnail, description]);
  res.json({ message: 'Thêm video thành công' });
});

app.get('/api/testimonials', async (req, res) => {
  const [rows] = await pool.query('SELECT * FROM testimonials');
  res.json(rows);
});

app.post('/api/testimonials', authMiddleware, async (req, res) => {
  const { name, role, content } = req.body;
  await pool.query('INSERT INTO testimonials (name, role, content) VALUES (?, ?, ?)', [name, role, content]);
  res.json({ message: 'Thêm đánh giá thành công' });
});

app.get('/api/contact_info', async (req, res) => {
  const [rows] = await pool.query('SELECT * FROM contact_info');
  res.json(rows);
});

app.post('/api/contact_info', authMiddleware, async (req, res) => {
  const { type, value } = req.body;
  await pool.query('INSERT INTO contact_info (type, value) VALUES (?, ?)', [type, value]);
  res.json({ message: 'Thêm thông tin liên hệ thành công' });
});

// Image Upload for TinyMCE
app.post('/api/upload', authMiddleware, upload.single('image'), async (req, res) => {
  const url = req.file ? await compressImage(req.file) : null;
  res.json({ url });
});

// Generate HTML
app.get('/api/generate-html', async (req, res) => {
  const [products] = await pool.query('SELECT * FROM products');
  const [testimonials] = await pool.query('SELECT * FROM testimonials');
  const [gallery] = await pool.query('SELECT * FROM gallery');
  const [articles] = await pool.query('SELECT * FROM articles');
  const [videos] = await pool.query('SELECT * FROM videos');
  const [contactInfo] = await pool.query('SELECT * FROM contact_info');
  const html = `
    <!DOCTYPE html>
    <html lang="vi">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta name="description" content="NGUYÊN KÝ - Trà chất lượng cao từ Việt Nam">
      <meta name="keywords" content="trà, trà xanh, trà đen, trà Việt Nam, Nguyên Ký">
      <title>NGUYÊN KÝ - Trà Chất Lượng Cao</title>
      <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    </head>
    <body class="font-sans">
      <nav class="bg-green-600 text-white p-4">
        <ul class="flex space-x-4">
          <li><a href="/">Trang chủ</a></li>
          <li><a href="/gioi-thieu">Giới thiệu</a></li>
          <li><a href="/san-pham">Sản phẩm</a></li>
          <li><a href="/thu-vien">Thư viện</a></li>
          <li><a href="/bai-viet">Bài viết</a></li>
          <li><a href="/tin-tuc">Tin tức</a></li>
          <li><a href="/video">Video</a></li>
          <li><a href="/dich-vu">Dịch vụ</a></li>
          <li><a href="/lien-he">Liên hệ</a></li>
        </ul>
      </nav>
      <main class="container mx-auto p-4">
        <section id="products">
          <h1 class="text-2xl font-bold mb-4">Sản phẩm</h1>
          ${products.map(p => `
            <div class="border p-4 mb-4">
              <h2>${p.name}</h2>
              <p>${p.description}</p>
              <p>${formatVND(p.price)}</p>
              <img src="${p.image}" alt="${p.name}" class="w-32 lazy-load">
            </div>
          `).join('')}
        </section>
        <section id="gallery">
          <h1 class="text-2xl font-bold mb-4">Thư viện hình ảnh</h1>
          ${gallery.map(g => `
            <div class="border p-4 mb-4">
              <h2>${g.title}</h2>
              <img src="${g.image}" alt="${g.title}" class="w-32 lazy-load">
              <p>${g.description}</p>
            </div>
          `).join('')}
        </section>
        <section id="articles">
          <h1 class="text-2xl font-bold mb-4">Bài viết/Tin tức</h1>
          ${articles.map(a => `
            <div class="border p-4 mb-4">
              <h2>${a.title} (${a.type === 'article' ? 'Bài viết' : 'Tin tức'})</h2>
              <img src="${a.image}" alt="${a.title}" class="w-32 lazy-load">
              <p>${a.content}</p>
            </div>
          `).join('')}
        </section>
        <section id="videos">
          <h1 class="text-2xl font-bold mb-4">Video</h1>
          ${videos.map(v => `
            <div class="border p-4 mb-4">
              <h2>${v.title}</h2>
              <a href="${v.url}" target="_blank"><img src="${v.thumbnail}" alt="${v.title}" class="w-32 lazy-load"></a>
              <p>${v.description}</p>
            </div>
          `).join('')}
        </section>
        <section id="testimonials">
          <h1 class="text-2xl font-bold mb-4">Đánh giá</h1>
          ${testimonials.map(t => `
            <div class="border p-4 mb-4">
              <h2>${t.name}</h2>
              <p>${t.content}</p>
            </div>
          `).join('')}
        </section>
        <section id="contact">
          <h1 class="text-2xl font-bold mb-4">Liên hệ</h1>
          ${contactInfo.map(c => `
            <div class="border p-4 mb-4">
              <h2>${c.type}</h2>
              <p>${c.value}</p>
            </div>
          `).join('')}
        </section>
      </main>
      <footer class="bg-gray-800 text-white p-4 text-center">
        <p>© 2025 NGUYÊN KÝ. All rights reserved.</p>
      </footer>
      <script>
        document.querySelectorAll('.lazy-load').forEach(img => {
          img.setAttribute('loading', 'lazy');
        });
      </script>
    </body>
    </html>
  `;
  fs.writeFileSync('public/index.html', html);
  res.json({ message: 'Tạo HTML thành công' });
});

app.listen(3000, () => console.log('Server chạy trên cổng 3000'));
