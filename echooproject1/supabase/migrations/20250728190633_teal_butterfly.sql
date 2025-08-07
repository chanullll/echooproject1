-- Products table
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    seller_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    carbon_saved DECIMAL(8,2) DEFAULT 0.00,
    stock_quantity INTEGER DEFAULT 0,
    image_url VARCHAR(255),
    is_approved BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_seller ON products(seller_id);
CREATE INDEX idx_products_approved ON products(is_approved);
CREATE INDEX idx_products_price ON products(price);
CREATE INDEX idx_products_carbon_saved ON products(carbon_saved DESC);
CREATE INDEX idx_products_created_at ON products(created_at DESC);