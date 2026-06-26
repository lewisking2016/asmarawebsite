-- Asmara Restaurant Database Schema
-- Created: June 23, 2026

-- ============================================
-- TABLE: BRANCHES
-- ============================================
CREATE TABLE IF NOT EXISTS branches (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL UNIQUE,
  address TEXT NOT NULL,
  phone VARCHAR(20) NOT NULL,
  email VARCHAR(100) NOT NULL,
  opening_hours VARCHAR(100),
  capacity INT DEFAULT 50,
  latitude DECIMAL(10,8),
  longitude DECIMAL(10,8),
  subtitle VARCHAR(255) DEFAULT NULL,
  summary TEXT DEFAULT NULL,
  long_description TEXT DEFAULT NULL,
  seo_keywords TEXT DEFAULT NULL,
  hero_image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_name (name)
);

-- ============================================
-- TABLE: MENU_ITEMS
-- ============================================
CREATE TABLE IF NOT EXISTS menu_items (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  category VARCHAR(50) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  image_url VARCHAR(255),
  branch_id INT,
  is_available BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
  INDEX idx_category (category),
  INDEX idx_branch (branch_id),
  INDEX idx_available (is_available)
);

-- ============================================
-- TABLE: ADMIN_USERS
-- ============================================
CREATE TABLE IF NOT EXISTS admin_users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100) NOT NULL,
  role ENUM('admin', 'staff') DEFAULT 'staff',
  last_login TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_username (username)
);

-- ============================================
-- TABLE: BOOKINGS
-- ============================================
CREATE TABLE IF NOT EXISTS bookings (
  id INT PRIMARY KEY AUTO_INCREMENT,
  guest_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  booking_date DATE NOT NULL,
  booking_time TIME NOT NULL,
  guest_count INT NOT NULL,
  branch_id INT NOT NULL,
  special_requests TEXT,
  special_requests TEXT,
  event_id VARCHAR(64) DEFAULT NULL,
  status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
  confirmation_code VARCHAR(20) UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
  INDEX idx_date (booking_date),
  INDEX idx_status (status),
  INDEX idx_branch (branch_id),
  INDEX idx_email (email),
  INDEX idx_confirmation_code (confirmation_code)
);

-- ============================================
-- TABLE: CONTACT_INQUIRIES
-- ============================================
CREATE TABLE IF NOT EXISTS contact_inquiries (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  status ENUM('new', 'read', 'replied', 'closed') DEFAULT 'new',
  admin_response TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (status),
  INDEX idx_email (email),
  INDEX idx_created (created_at)
);

-- ============================================
-- TABLE: ACTIVITY_LOG
-- ============================================
CREATE TABLE IF NOT EXISTS activity_log (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT,
  action VARCHAR(50) NOT NULL,
  table_name VARCHAR(50) NOT NULL,
  record_id INT,
  changes JSON,
  ip_address VARCHAR(45),
  user_agent VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE SET NULL,
  INDEX idx_user (user_id),
  INDEX idx_action (action),
  INDEX idx_table (table_name),
  INDEX idx_created (created_at)
);

-- ============================================
-- INSERT INITIAL DATA
-- ============================================

-- Insert Branches
INSERT INTO branches (name, address, phone, email, opening_hours, capacity, subtitle, summary, long_description, seo_keywords, hero_image) VALUES
('Westlands', 'General Mathenge Drive, Westlands', '0721948020', 'info@asmara.co.ke', '10:00 AM - 11:00 PM', 50,
 'A casual and friendly dining atmosphere',
 'The modern indoor and outdoor setting perfectly complements the afro-contemporary Eritrean and Continental cuisine offered here.',
 'Located in the vibrant heart of Westlands, our flagship Asmara Restaurant brings the rich traditions of Eritrean and Ethiopian dining to Nairobi. We specialize in authentic African culinary experiences, featuring our famous, freshly-made injera paired with spicy Zigni, tender Kitfo, and rich vegetarian shiro.\n\nOur Westlands branch offers a seamless blend of traditional African culture and modern contemporary aesthetics. With an airy garden lounge and elegant indoor seating, it is the premier choice for business lunches, romantic dinners, and family gatherings in Nairobi.',
 'Eritrean food Westlands, Ethiopian restaurant Nairobi, Asmara Westlands, authentic African cuisine, best injera Nairobi, Zigni, Kitfo, outdoor dining Westlands',
 'images/optimized/Lavington-5.jpg'),

('Karen', 'Ngong'' Rd-No. 321, Karen', '0724124555', 'admin.karen@asmara.co.ke', '10:00 AM - 11:00 PM', 45,
 'Leafy, serene, and spacious',
 'In the leafy Karen suburb, this restaurant sits on a large and picturesque location with delicious quality food and excellent service.',
 'Escape the city rush at Asmara Restaurant in Karen. Nestled on Ngong Road, our Karen branch is a true sanctuary of Eritrean culture and gastronomy. Diners can enjoy authentic Horn of Africa hospitality surrounded by manicured gardens.\n\nWhether you are craving our signature platters, Tibs, or a relaxing weekend brunch with continental favorites, our serene environment elevates every meal. It is the perfect venue for events, parties, and anyone looking for the best African restaurant in Karen.',
 'Asmara Karen, Eritrean restaurant Karen, African food Ngong Road, garden restaurant Nairobi, best Ethiopian food Karen, family dining, cultural African restaurant',
 'images/optimized/Lavington-10.jpg'),

('Lavington', 'Othaya Road, Lavington', '0700458429', 'sales@asmara.co.ke', '10:00 AM - 11:00 PM', 40,
 'Cosy and modern',
 'Ideally situated in Lavington, this branch offers the ultimate dining experience of Eritrean and Continental dishes for breakfast, lunch, and dinner.',
 'Asmara Lavington on Othaya Road is the neighborhood''s top destination for premium Eritrean, Ethiopian, and Continental dining. We pride ourselves on preserving authentic African cooking methods—from our traditional clay-pot stews to our hand-poured injera.\n\nThe cozy, afro-contemporary interior is designed for comfort, making it an excellent spot for breakfast meetings, casual lunches, and intimate dinners. Experience the true taste of Asmara with our diverse menu that caters to both meat lovers and vegans alike.',
 'Asmara Lavington, Eritrean food Lavington, African restaurant Othaya Road, vegan Ethiopian food Nairobi, Asmara breakfast, best dining Lavington',
 'images/optimized/Lavington-20.jpg'),

('Pangani', 'Pangani, Juja Road', '0713610707', 'admin.pangani@asmara.co.ke', '10:00 AM - 11:00 PM', 35,
 'Authentically Eritrean',
 'A favourite of the Eritrean community and locals, described by The Nairobian as a little piece of Eritrea.',
 'Welcome to Asmara Pangani—the cultural heart of our brand. Situated on Juja Road, this branch is affectionately known as a "little piece of Eritrea." It is deeply rooted in tradition and remains a favorite among the local Eritrean and Ethiopian communities in Nairobi.\n\nIf you want the most authentic, unfiltered Horn of Africa dining experience, this is the place. From communal dining over massive injera platters to the rich aroma of traditional coffee ceremonies, Asmara Pangani celebrates the true essence of African heritage and culinary excellence.',
 'Asmara Pangani, authentic Eritrean restaurant Nairobi, Juja road restaurants, traditional Ethiopian food, Eritrean culture Nairobi, injera platters, African coffee ceremony',
 'images/optimized/Lavington-30.jpg');

-- Insert Admin User (username: admin, password: asmara123)
INSERT INTO admin_users (username, password, email, role) VALUES
('admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36gZvWQm', 'admin@asmara.co.ke', 'admin');

-- Insert Sample Menu Items
INSERT INTO menu_items (name, description, category, price, branch_id, is_available) VALUES
('Sambusas', 'Crispy pastries filled with spiced meat or vegetables', 'appetizers', 300, NULL, TRUE),
('Shiro', 'Creamy chickpea dip with traditional spices', 'appetizers', 450, NULL, TRUE),
('Fasting Bread', 'Fresh baked bread, light and fluffy', 'appetizers', 250, NULL, TRUE),
('Injera & Wat', 'Traditional Eritrean meal with meat sauce', 'mains', 800, NULL, TRUE),
('Tibs', 'Sauteed meat with vegetables and spices', 'mains', 950, NULL, TRUE),
('Kitfo', 'Ethiopian minced raw meat with spiced butter', 'mains', 1200, NULL, TRUE),
('Fasting Greens', 'Seasonal vegetables in traditional preparation', 'mains', 600, NULL, TRUE),
('Honey Wine', 'Traditional fermented honey beverage', 'drinks', 400, NULL, TRUE),
('Tamarind Juice', 'Fresh tamarind juice, tangy and refreshing', 'drinks', 350, NULL, TRUE),
('Basboosa', 'Sweet coconut semolina cake', 'desserts', 400, NULL, TRUE),
('Tiramisu', 'Italian-inspired layered dessert', 'desserts', 550, NULL, TRUE);

-- ============================================
-- TABLE: NEWSLETTER_SUBSCRIBERS
-- ============================================
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
  id INT PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(100) NOT NULL UNIQUE,
  subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_active BOOLEAN DEFAULT TRUE,
  INDEX idx_email (email)
);

